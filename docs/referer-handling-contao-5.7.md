# Umsetzungskonzept: Referer-Handling im dc-general (Contao 5.7)

> Status: **alle Schritte 1–5 erledigt.** Static Analysis (Psalm + phpcs PSR12) und
> End-to-End-Klicktest (Playwright) grün.
>
> - [x] 1 – Backend-Test des 5.7-Ist-Verhaltens (Anhang A)
> - [x] 2 – `ViewHelpers::getBackUrl()` eingeführt, `redirectHome/redirectCleanHome` darauf umgestellt
> - [x] 3 – Call-Sites umgestellt: EditMask (saveNclose/saveNback), AbstractPropertyOverrideEditAllHandler, BackButtonListener, SelectHandler, ShowHandler + Show-Template
> - [x] 4 – `StoreRefererListener` + Service entfernt; `_dcg_referer_update` aus metamodels/core routing.yml entfernt (verifiziert: Service weg, Route-Defaults bereinigt)
> - [x] 5 – Psalm (`--no-cache`) + phpcs PSR12 sauber; Playwright-Klicktest grün (Anhang B)
>
> Entscheidung: `GetReferrerEvent` **ersatzlos** aus dem DCG-Navigationspfad genommen
> (Event bleibt in events-contao-bindings bestehen, wird von DCG nur nicht mehr genutzt).

## 1. Ausgangslage / Ursache

Contao 5.7 hat `System::getReferer()` intern umgestellt: Es liest **nicht mehr die
Session** (`session['referer'][refererId]`), sondern baut den Pfad über den neuen
Service `contao.data_container.dca_url_analyzer` (`getTrail()`) aus DCA-Metadaten,
echten DB-Records, `ptable` und Standard-Sorting-Modi auf.

**Konsequenz:**

- `StoreRefererListener` schreibt eine Session, die **niemand mehr liest** →
  funktionsloses Altlast-Objekt. Entfernen ist funktional risikolos.
- `DcaUrlAnalyzer` ist auf `DC_Table`-Konventionen gebaut → für dc-generals eigene
  Data-Provider und dynamische MetaModels-Tabellen (`mm_*`) **nicht verlässlich**.
  Deshalb erzeugt DCG seine Back-URLs künftig selbst.

## 2. Fundament existiert bereits

`ViewHelpers::redirectHome()/redirectCleanHome()` → `determineNewStyleRedirect()`
baut die "zurück zur Liste"-URL bereits **deterministisch aus dem aktuellen Request**
(`_route` + `_route_params` + Query, ohne `act`) und hat einen Legacy-Fallback
(`contao?do=…&table=…[&pid=…]`). Das ist im Kern der gewünschte "DCG-eigene Trail" —
nur an einen `never`-Redirect gekoppelt und ohne URL-String-Rückgabe für Buttons/Links.

## 3. Kernidee: URL-Builder zentralisieren

Neue, wiederverwendbare Methode in `ViewHelpers`, die die URL **zurückgibt** statt zu
redirecten:

```php
public static function getBackUrl(
    EnvironmentInterface $environment,
    array $cleanNames = [],
    ?string $targetProvider = null   // für saveNback = Parent-Provider
): string
```

Kapselt **beide** Zweige aus der heutigen `determineNewStyleRedirect`/
`determineLegacyRedirect`-Logik:

- **New-Style** (eigene MM-Route, `_route !== 'contao_backend'`):
  `router->generate(routeName, params)` mit bereinigten Parametern.
- **Legacy** (`contao_backend`): `contao?do=…&table=…[&pid=…]`.

Parameter-Bereinigung fürs Listen-Ziel: `act` **und** `id` entfernen, `cleanNames`
entfernen, `pid` behalten (= Kind-Liste). Für `saveNback`/`$targetProvider` eine Ebene
hochgehen (Ziel-`table` = Parent-Provider, `pid` entsprechend reduzieren).

`redirectHome()/redirectCleanHome()` werden dünne Wrapper:

```php
self::dispatchRedirect($environment, new RedirectEvent(self::getBackUrl($environment, $cleanNames)));
```

## 4. Die 5 Call-Sites — konkrete Umstellung

| # | Ort | Heute | Neu |
|---|-----|-------|-----|
| 1 | `EditMask::doPersist` `saveNclose` | `GetReferrerEvent` → `RedirectEvent` | `RedirectEvent(getBackUrl($env))` |
| 2 | `EditMask::doPersist` `saveNback` | `GetReferrerEvent(false, $parentProvider)` | `RedirectEvent(getBackUrl($env, [], $parentProvider))` |
| 3 | `AbstractPropertyOverrideEditAllHandler:90` | `GetReferrerEvent(false, $definition->getName())` → Redirect | `RedirectEvent(getBackUrl($env))` |
| 4 | `BackButtonListener::getReferrerUrl` (`@api`, Listen-Back-Button) | `GetReferrerEvent(true, parent/self)` | `$event->setHref(getBackUrl($env))` |
| 5 | `SelectHandler::getReferrerUrl` (private, Button-Href) | `GetReferrerEvent(...)` | `getBackUrl($env)` |

Zusätzlich **Template**: `dcbe_general_show.html5:25` nutzt `$this->getReferer(true)`
(Contao-`BackendTemplate`-Methode → `System::getReferer()`). → In `ShowHandler` neue
Template-Variable `backHref = ViewHelpers::getBackUrl($environment)` setzen und im
Template `$this->backHref` verwenden.

## 5. Entfernen / Aufräumen

- `src/EventListener/StoreRefererListener.php` **löschen**.
- Service-Registrierung in `src/Resources/config/event_listeners.yml`
  (Block `StoreRefererListener`) **entfernen**.
- In **metamodels/core** `.../Resources/config/routing.yml`: die wirkungslosen
  `_dcg_referer_update: true`-Defaults entfernen (4 Vorkommen).
  *(Anderes Repo/Paket — separater Commit/PR.)*

## 6. Bewusst nicht anfassen (BC)

- `GetReferrerEvent` + `SystemSubscriber::handleGetReferer` liegen in
  **events-contao-bindings** und funktionieren weiter (jetzt via DcaUrlAnalyzer).
  Bleiben öffentliche API — dc-general nutzt sie nur intern nicht mehr für die eigene
  Navigation.
- **Offen:** `GetReferrerEvent` als optionalen Override-Hook in `getBackUrl`
  voranstellen — oder ersatzlos aus dem DCG-Navigationspfad nehmen? *(noch zu entscheiden)*
- `BackButtonListener` bleibt `@api`-Klasse mit gleicher Signatur, nur interne
  URL-Quelle ändert sich.

## 7. Offene Punkte / zu testen

1. **5.7-Verhalten ist ungetestet** → Backend-Durchlauf im
   `metamodels-devstack-5x-backend-1`-Container: Verhält sich `System::getReferer()`
   unter DCG falsch/leer? Referenz-URLs zum Abgleich sammeln. **(Schritt 1, läuft)**
2. **`id`-Bereinigung**: heutiges `determineNewStyleRedirect` entfernt nur `act`,
   behält `id`; Legacy-Zweig droppt `id`. `getBackUrl` muss `id` konsistent entfernen
   — Nichtregression für bestehende `redirectHome`-Nutzer (Delete/Paste/Select) prüfen.
3. **saveNback-Ebenenlogik**: Parent-Provider → Ziel-`table`/`pid`, auch bei
   mehrstufigen Parent/Child-Beziehungen.
4. **`popup`-/`picker`-Modus** und **Ampersand-Encoding** im URL-Builder abbilden.

## 8. Reihenfolge

1. Backend-Test des Ist-5.7-Verhaltens (7.1) → dokumentieren.
2. `ViewHelpers::getBackUrl()` einführen + `redirectHome/redirectCleanHome` darauf
   umstellen (additiv, testbar).
3. Call-Sites 1–5 + Show-Template umstellen.
4. `StoreRefererListener` + Service + Routing-Defaults entfernen.
5. Psalm (`--no-cache`, backend-Container) + Backend-Klicktest: Edit→Speichern-und-
   zurück, Listen-Back-Button, Show-Back, Select-Modus, EditAll.

## Anhang A: Backend-Test-Ergebnisse (5.7-Ist-Verhalten)

**Umgebung:** Contao Managed Edition 5.7.9 (dev), Container
`metamodels-devstack-5x-backend-1`. MM-Datenansicht ist bereits eine New-Style-Route:
`/contao/metamodel/mm_employees` (kein klassisches `contao?do=…`).

**Probe (auth-frei, Kernel gebootet, `DcaUrlAnalyzer` direkt) für `mm_employees`:**

| Aufruf | Ergebnis | Bewertung |
|--------|----------|-----------|
| `getEditUrl('mm_employees', 1)` | `NULL` | Analyzer findet **kein** Backend-Modul für die Tabelle |
| `getViewUrl('mm_employees', 1)` | `NULL` | dito |
| `getTrail(edit-context)` | 1 Item → `{"label":"","url":"/contao?do=metamodels"}` | **falsches Ziel**: zeigt auf das MM-*Konfig*-Modul, nicht auf die `mm_employees`-Liste |

**Ursache (verifiziert):**

- Kein `$GLOBALS['BE_MOD']`-Eintrag führt `mm_employees` in seiner `tables`-Liste.
- Einziges `metamodel*`-Modul: `metamodels/metamodels` mit `tables=[tl_metamodel_notelist]`
  (= Modell-Konfiguration). Die **per-Modell-Datenansichten** (`mm_*`) sind New-Style-
  Routen und **keine** klassischen `BE_MOD`-Module mit `tables`-Array.
- `DcaUrlAnalyzer` ist genau auf diese `BE_MOD['…']['tables']`-Konvention gebaut →
  kann MM-Datenansichten nicht auflösen und fällt auf `do=metamodels` (Konfig) zurück.

**Fazit:** `System::getReferer()` / `DcaUrlAnalyzer` liefern für MetaModels-Datenansichten
**keine korrekten Back-URLs** (leer bzw. auf das falsche, übergeordnete Konfig-Modul).
Damit ist der DCG-eigene Trail bestätigt notwendig.

**Korrektes Ziel** (Referenz für `getBackUrl`): Aus einer Edit-/Show-Ansicht von
`mm_employees` muss die Back-URL auf die Liste `/contao/metamodel/mm_employees` zeigen
(bzw. bei Kind-Listen auf die entsprechende Parent-Route).

**Route-Struktur (verifiziert per Router-Match):**

Edit-URL (Adresszeile, real):
```
/contao/metamodel/mm_employees?act=edit&id=mm_employees::1&rt=<token>
```

`router->match('/contao/metamodel/mm_employees')`:
```
_route              = metamodels.metamodel
_controller         = MetaModels\CoreBundle\Controller\Backend\MetaModelController
_scope              = backend
_dcg_referer_update = 1
_token_check        = 1
tableName           = mm_employees        (Modellname als ROUTE-Param!)
```

Unterschiede zu Contao-Core (wichtig für den Builder):

- Eigene Route `metamodels.metamodel` statt `contao_backend`; Modellname steckt im
  **Route-Param `tableName`**, nicht in `?table=`.
- ID ist dc-generals **serialisierte ModelId** `id=mm_employees::1`, nicht `?id=<int>`.
- Aktion in der Query: `act=edit` (+ `rt`-Token).

**Back-URL-Bildung konkret:**
`router->generate('metamodels.metamodel', ['tableName' => 'mm_employees'])`
= `/contao/metamodel/mm_employees`
→ Route + `_route_params` (`tableName`) behalten; Query **`act`, `id`, `rt`** (+ `cleanNames`) entfernen.

**Korrektur zu Punkt 7.2:** Core-`determineNewStyleRedirect` entfernt heute nur `act`
und ließe `id`/`rt` stehen. `getBackUrl` muss `id` **und** `rt` mit strippen.

**Erweiterung zu Abschnitt 5 (Cleanup):** `_dcg_referer_update` hängt **auch an der
`metamodels.metamodel`-Route** (nicht nur an den `add_all`-Routen). Cleanup-Scope in
metamodels/core entsprechend größer.

**Noch offen:** Kind-/Parent-Listen-Route (saveNback, mehrstufig) — Routen-/`pid`-Struktur
bei verschachtelten MM noch am Live-Backend zu bestätigen. `mm_employees` ist flach:
`saveNback` = `saveNclose` (beide → Liste).

## Anhang B: End-to-End-Verifikation (Playwright, eingeloggtes Backend)

Getestet gegen `http://localhost:8025`, Modell `mm_employees` (flach). Ergebnis nach
dem Umbau:

| Ansicht / Aktion | Back-/Redirect-Ziel | OK |
|------------------|---------------------|----|
| EDIT „Zurück" (`header_back dcg`) | `/contao/metamodel/mm_employees` | ✅ |
| SHOW „Zurück" (`header_back dcg`) | `/contao/metamodel/mm_employees` | ✅ |
| „Speichern und schließen" (saveNclose) | Redirect → `/contao/metamodel/mm_employees` | ✅ |
| „Speichern und zurück" (saveNback) | Redirect → `/contao/metamodel/mm_employees` | ✅ |
| Select-Modus „Beenden" | `/contao/metamodel/mm_employees` | ✅ |

Alle Ziele sauber, ohne stale `id`/`rt`.

**Fund + Fix während des Tests:** Der Select-Modus-„Beenden"-Button behielt zunächst
`?select=models`. `SelectHandler::getReferrerUrl()` gibt jetzt `getBackUrl($env, ['select'])`
mit — analog zum früheren `redirectCleanHome(['select'])`.

Static Analysis: Psalm (`--no-cache`) „No errors", phpcs PSR12 ohne Beanstandung auf
allen geänderten Dateien.
