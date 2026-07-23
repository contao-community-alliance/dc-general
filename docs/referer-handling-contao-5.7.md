# Umsetzungskonzept: Referer-Handling im dc-general (Contao 5.7)

> Status: **abgeschlossen.** Umbau in dc-general umgesetzt, verifiziert (Psalm + phpcs
> PSR12, 319 phpunit-Tests, Playwright-E2E) und über alle betroffenen MetaModels-Pakete
> nachgezogen (Anhang D).
>
> - [x] 1 – Backend-Test des 5.7-Ist-Verhaltens (Anhang A)
> - [x] 2 – `ViewHelpers::getBackUrl()` eingeführt, `redirectHome/redirectCleanHome` darauf umgestellt
> - [x] 3 – Call-Sites umgestellt: EditMask, AbstractPropertyOverrideEditAllHandler, BackButtonListener, SelectHandler, ShowHandler + Show-Template
> - [x] 4 – `StoreRefererListener` + Service entfernt; `_dcg_referer_update` aus metamodels/core routing.yml entfernt
> - [x] 5 – Static Analysis + Playwright-Klicktest grün (Anhang B)
> - [x] 6 – `saveNback`-Button entfernt (Core-Analogie, Anhang C)
> - [x] 7 – Paketübergreifende Nachziehung: core add-all, attribute_levenshtein, filter_loupe, notelist (Anhang D)
>
> **Grundsatzentscheidungen:**
> - `GetReferrerEvent` **ersatzlos** aus dem DCG-Navigationspfad genommen (Event bleibt in
>   events-contao-bindings bestehen, wird von DCG nur nicht mehr genutzt).
> - `getBackUrl()` finale Signatur: `getBackUrl(EnvironmentInterface $environment, array $cleanNames = []): string`
>   (kein `$targetProvider` — die ursprünglich dafür vorgesehene saveNback-Parent-Logik
>   entfiel, weil `saveNback` ganz entfernt wurde).

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
public static function getBackUrl(EnvironmentInterface $environment, array $cleanNames = []): string
```

Kapselt **beide** Zweige aus der früheren `determineNewStyleRedirect`/
`determineLegacyRedirect`-Logik (die dabei durch `buildNewStyleUrl`/`buildLegacyUrl`
ersetzt wurden):

- **New-Style** (eigene MM-Route, `_route !== 'contao_backend'`):
  `router->generate(routeName, params)` mit bereinigten Parametern.
- **Legacy** (`contao_backend`): `contao?do=…&table=…[&pid=…]`.

Parameter-Bereinigung fürs Listen-Ziel: `act`, `id` **und** `rt` entfernen, `cleanNames`
entfernen, `pid` behalten (= Kind-Liste).

> Hinweis: In der ursprünglichen Planung war ein dritter Parameter `$targetProvider`
> für die saveNback-Parent-Ebene vorgesehen. Da `saveNback` letztlich ganz entfernt wurde
> (Anhang C), entfiel dieser Parameter — die finale Signatur hat nur `$cleanNames`.

`redirectHome()/redirectCleanHome()` werden dünne Wrapper:

```php
self::dispatchRedirect($environment, new RedirectEvent(self::getBackUrl($environment, $cleanNames)));
```

## 4. Die 5 Call-Sites — konkrete Umstellung

| # | Ort | Heute | Neu |
|---|-----|-------|-----|
| 1 | `EditMask::doPersist` `saveNclose` | `GetReferrerEvent` → `RedirectEvent` | `RedirectEvent(getBackUrl($env))` |
| 2 | `EditMask::doPersist` `saveNback` | `GetReferrerEvent(false, $parentProvider)` | **entfernt** — `saveNback`-Button ganz gestrichen (Anhang C) |
| 3 | `AbstractPropertyOverrideEditAllHandler:90` | `GetReferrerEvent(false, $definition->getName())` → Redirect | `RedirectEvent(getBackUrl($env))` |
| 4 | `BackButtonListener::getReferrerUrl` (`@api`, Listen-Back-Button) | `GetReferrerEvent(true, parent/self)` | `$event->setHref(getBackUrl($env))` |
| 5 | `SelectHandler::getReferrerUrl` (private, Button-Href) | `GetReferrerEvent(...)` | `getBackUrl($env, ['select'])` |

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
- **Entschieden:** `GetReferrerEvent` wird **ersatzlos** aus dem DCG-Navigationspfad
  genommen (kein Override-Hook). Das Event lieferte nur die jetzt kaputten session-/
  analyzer-basierten URLs; ein Hook mit falschem Default wäre mehr Bürde als Nutzen.
- `BackButtonListener` bleibt `@api`-Klasse mit gleicher Signatur, nur interne
  URL-Quelle ändert sich.

## 7. Vormals offene Punkte — Auflösung

1. ~~5.7-Verhalten ungetestet~~ → **verifiziert** (Anhang A): `System::getReferer()`/
   `DcaUrlAnalyzer` liefern für MM-Datenansichten leere/falsche URLs.
2. ~~`id`-Bereinigung~~ → **umgesetzt**: `buildNewStyleUrl` strippt `act`, `id`, `rt`;
   Nichtregression für Delete/Paste/Select im Playwright-Klicktest bestätigt (Anhang B).
3. ~~saveNback-Ebenenlogik (Parent-Provider)~~ → **entfällt**: `saveNback` wurde ganz
   entfernt (Anhang C).
4. ~~`popup`-/`picker`-Modus + Ampersand-Encoding~~ → in der Praxis unkritisch: die
   erzeugten Listen-URLs sind einfache Ein-Parameter-Routen (kein `&`). Der Select-Modus
   wird über `cleanNames` (`['select']`) sauber abgedeckt (Anhang B).

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

**Erledigt (Nachtrag):** Der ehemals offene Punkt „Kind-/Parent-Listen-Route für
saveNback" entfällt — `saveNback` wurde entfernt (siehe Anhang C).

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

## Anhang C: Entfernung des „Speichern und zurück" (saveNback)

Contao Core hat den Button **„Speichern und zurück" (`saveNback`) in 5.7.0 entfernt**
(bisektiert über die getaggten Releases: in `DC_Table` bis 5.4 inline, ab 5.5 im neuen
`ButtonsBuilder`, dort in 5.6 noch enthalten, in 5.7.0 nicht mehr). Mit der neuen
Navigation ist „Schließen" ausreichend; das separate „Zurück" war außer im
verschachtelten Fall redundant.

dc-general folgt dem und entfernt `saveNback` ebenfalls:

- **Single-Edit (`EditMask`):** Button-Definition + `doPersist`-Zweig entfernt
  (`saveNclose` deckt das Verhalten ab). Verwaister `BasicDefinitionInterface`-Import
  entfernt.
- **Multi-Edit (`AbstractPropertyOverrideEditAllHandler`):** `_saveNback`-Button
  entfernt. `handleSubmit()` triggert jetzt auf `_save` statt `_saveNback` → der
  verbleibende „Speichern"-Button wendet an **und** kehrt zur Liste zurück (kein
  Funktionsverlust).
- **`SelectHandler`:** `_saveNback`-Referenzen in `getSelectAction()` und
  `regardSelectMode()` entfernt.
- Verwaiste Übersetzungs-Units `saveNback` aus `dc-general.en.xlf`/`.de.xlf` entfernt.

Verifikation: Psalm „No errors", phpcs PSR12 clean, 319 phpunit-Tests grün.

## Anhang D: Paketübergreifende Nachziehung (alle MetaModels-Pakete)

Dieselbe Ursache (`System::getReferer()` / session-Referer / `saveNback`) betraf noch
weitere Pakete. Ein Audit aller MetaModels-Pakete ergab genau die unten gelisteten
Treffer; alle übrigen (~45: `attribute_*` außer levenshtein, `filter_*` außer loupe,
`contao-frontend-editing`, `cowegis-layer`, `dropzone_file_upload`, `translator-bridge`,
… sowie `dc-general-contao-frontend`) sind **nicht betroffen**.

**Muster für Nicht-DCG-Kontexte (Symfony-Controller):** Dort steht kein dc-general-
`Environment` zur Verfügung, `ViewHelpers::getBackUrl()` greift also nicht. Die Back-URL
wird stattdessen deterministisch aus **Route + Parent-Bezug** gebaut:
`router->generate('metamodels.configuration', ['tableName' => <settingsTabelle>]) . '?pid=<parentProvider>::<parentId>'`.

| Paket | Fundstelle | Fix |
|-------|-----------|-----|
| **metamodels/core** | `AbstractAddAllController::getReferer()` (`System::getReferer()`) → Back-Link/`saveNclose`-Redirect landeten auf `/contao` | Deterministische URL aus Settings-Tabelle + Parent-`pid`; `getParentProviderName()` (dcasetting→`tl_metamodel_dca`, rendersetting→`tl_metamodel_rendersettings`); verwaiste System-Adapter-DI entfernt. Live: beide add-all-Varianten HTTP 200, korrekte Back-Links. |
| **metamodels/core** | `_dcg_referer_update` an 4 Routen (`routing.yml`) | entfernt (nur vom gelöschten `StoreRefererListener` gelesen). |
| **metamodels/core** | ~33 verwaiste `saveNback`-`trans-unit` (per-Tabelle-xlf, de/en/fr) | entfernt. |
| **attribute_levenshtein** | `RegenerateSearchIndexListener` (`GetReferrerEvent`) — Reindex-Back-Button | auf `ViewHelpers::getBackUrl($event->getEnvironment())` (hat DCG-Environment); ungenutzte `EventDispatcher`-DI entfernt. |
| **filter_loupe** | `ReindexController` (`System::getReferer()`) → Reindex-Redirect auf `/contao` | Back-URL zur Filtersetting-Liste `?pid=tl_metamodel_filter::<fid>` (`fid` per DB-Lookup); `Connection` + `router` injiziert. Ziel-URL HTTP 200 verifiziert, Container-DI kompiliert. |
| **notelist** | 2 verwaiste `saveNback`-`trans-unit` (de/en) | entfernt. |

### Commit-/Branch-Übersicht

| Repo | Branch | Remote |
|------|--------|--------|
| contao-community-alliance/dc-general | `hotfix/fix_delete_store_referer` | GitHub |
| metamodels/core | `hotfix/fix_store_referer` | GitHub (`origin-github`) |
| metamodels/attribute_levenshtein | `hotfix/fix_store_referer` | GitLab |
| metamodels/filter_loupe | `feature/2.5.0` | GitLab |
| metamodels/notelist | `feature/2.5.0` | git.cyberspectrum.de |

Alle Commits ohne `Co-Authored-By`, Autor *Ingolf Steinhardt*; je Repo nur die
gewollten Dateien.
