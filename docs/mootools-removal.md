# Ausbau von MooTools im dc-general

> Status: **Stufe 1 abgeschlossen, Stufe 2 offen.** Alle in Contao 5.7 als *deprecated*
> markierten Backend-JS-Aufrufe sind aus dem dc-general verschwunden, ebenso das
> Inline-MooTools in den Templates. Die beiden großen JS-Dateien stehen noch aus.
>
> - [x] 1 – Bestandsaufnahme (Abschnitt 2)
> - [x] 2 – `Backend.getScrollOffset()` (23 Stellen) → `contao--scroll-offset#store`
> - [x] 3 – `Backend.toggleCheckboxes()` (4 Stellen) → `contao--check-all`
> - [x] 4 – `Backend.vScrollTo()` (3 Stellen) → `contao--scroll-offset`-Targets
> - [x] 5 – `Backend.makeMultiSrcSortable()` (3 Stellen) → `contao--sortable` + `contao--input-map`
> - [x] 6 – Inline-MooTools in den Picker-Templates → `fetch` (`dcGeneralAjax.js`)
> - [x] 7 – tote Build-Artefakte `generalDriver.js` / `.js.map` entfernt
> - [ ] 8 – `generalDriver_src.js` auf Vanilla umbauen (Abschnitt 5)
> - [ ] 9 – `vanillaGeneral.js` auf Vanilla umbauen (Abschnitt 5)
>
> **jQuery:** im dc-general nicht vorhanden — es gab und gibt keine Fundstelle.

## 1. Ausgangslage

Contao 5.7 baut sein Backend-JS schrittweise von MooTools auf **Stimulus-Controller**
um. Der alte Code bleibt vorerst erhalten, wird aber mit `console.warn(... is
deprecated ...)` markiert und fällt in **Contao 6**.

Wichtig für die Planung: Contao 5.7 ist im Backend **selbst noch nicht MooTools-frei**
(`mootao.js`, `core.js`, `SimpleModal`, `Picker.Date` werden weiterhin ausgeliefert).
„MooTools raus" kann für den dc-general deshalb nur heißen:

> **dc-generals eigener Code wird vanilla** (DOM, Events, Ajax). Die dokumentierten
> Contao-Einstiegspunkte, die noch MooTools-basiert sind, bleiben — sonst bricht die
> Integration.

## 2. Bestandsaufnahme (Stand vor Stufe 1)

| Ort | Art | Menge |
| --- | --- | --- |
| `Backend.getScrollOffset()` | deprecated Contao-API | 23 in 14 Dateien |
| `Backend.toggleCheckboxes()` | deprecated Contao-API | 4 |
| `Backend.vScrollTo()` | deprecated Contao-API | 3 |
| `Backend.makeMultiSrcSortable()` | deprecated Contao-API | 3 |
| Inline-MooTools in Templates | `$()`, `.addEvent()`, `Request.Contao`, `Browser.exec` | 5 Templates |
| `$GLOBALS['TL_MOOTOOLS']`-Block | erzeugtes MooTools-JS | `SelectPropertyAllHandler` |
| `generalDriver_src.js` | eigene JS-Datei | 55 Stellen |
| `vanillaGeneral.js` | eigene JS-Datei (trotz Namens) | 8 Stellen |
| `generalDriver.js` + `.js.map` | minifizierte Artefakte, **nirgends registriert** | tot |

## 3. Umgesetzt in Stufe 1

### 3.1 Scroll-Offset

`onclick="Backend.getScrollOffset();"` bzw. `onfocus="…"` →
`data-action="contao--scroll-offset#store"` bzw. `data-action="focus->contao--scroll-offset#store"`.

Der Controller hängt in Contao 5.7 auf `<html>` (`be_main.html.twig`), die Attribute
funktionieren also überall im Backend — auch im Picker-Popup, das über `be_main` mit
`renderMainOnly` gerendert wird.

Betroffen: `TreeView`, `TreePicker`, `ButtonRenderer`, `WidgetBuilder`, `SelectHandler`,
`AbstractListShowAllHandler`, `ParentedListViewShowAllHandler`, `BackCommand`,
`CreateModelCommand` sowie die Templates `dcbe_general_show`, `dcbe_general_field`,
`dcbe_general_treeview_entry`, `widget_treepicker_entry`, `widget_treepicker_popup`.

Die Falt-Links der Baumansicht behalten ihr `onclick` (`BackendGeneral.loadSubTree`) und
bekommen das `data-action` **zusätzlich** — beides feuert.

### 3.2 Alle auswählen

`onclick="Backend.toggleCheckboxes(this)"` → `contao--check-all`:

* Container (`.tl_listing_container` bzw. `#tl_listing`) → `data-controller="contao--check-all"`
* Trigger → `data-action="contao--check-all#toggleAll"`
* Zeilen-Checkboxen → `data-contao--check-all-target="input"` und `contao--check-all#toggleInput`

`toggleInput` bringt zusätzlich die Shift-Bereichsauswahl mit, die es vorher nicht gab.

Contaos Controller verdrahtet `#tl_listing` in `afterLoad` auch selbst. Die Attribute
werden hier trotzdem **explizit** gesetzt: die Auto-Verdrahtung ist BC-Glue, greift bei
`dcbe_general_common_list` mangels `#tl_listing` ohnehin nicht, und doppelt gesetzte
Attribute sind idempotent.

### 3.3 Scrollen zum Fehler / Autofokus

Die beiden `window.addEvent('domready', …)`-Blöcke in `dcbe_general_edit` sind entfallen:

* Fehler-Scrolling → `data-contao--scroll-offset-target="widgetError"` am `error_wrapper`
  und am Feld-Wrapper in `dcbe_general_field` (analog `DataContainer.php:742`).
* Autofokus des ersten Textfeldes → **ersatzlos**, das erledigen Contaos `TextField`/
  `TextArea` selbst über `data-contao--scroll-offset-target="autoFocus"`.

### 3.4 Sortierbare Auswahllisten

Siehe eigener Commit: `fileTree`-Widget und Baum-Picker nutzen `contao--sortable` +
`contao--input-map`; der Entfernen-Button wird serverseitig gerendert. Das Legacy-
`orderField` wird von `sortableOrderField.js` (vanilla, delegiert am `document`)
nachgeführt.

### 3.5 Ajax der Picker-Templates

`Request.Contao` → `fetch`, gekapselt in **`src/Resources/public/js/dcGeneralAjax.js`**:

* `DcGeneral.post(url, data)` — POST als `x-www-form-urlencoded` mit
  `X-Requested-With: XMLHttpRequest`, normalisiert die Antwort auf `{content, javascript}`
  (Contao antwortet je nach Route JSON *oder* nacktes HTML) und folgt `X-Ajax-Location`.
* `DcGeneral.setHtml(element, html)` — ersetzt Markup und **führt die enthaltenen
  `<script>`-Blöcke aus**. Per `innerHTML` eingefügte Scripts sind inert; MooTools hat sie
  ebenfalls verschluckt, weshalb nach einer Picker-Auswahl bisher die Widget-Skripte tot
  waren. Stimulus-Controller im neuen Markup verbinden sich selbst.

Betroffen: `widget_filetree`, `widget_common_picker`, `dc_general_wizard_common_picker`.

Nebenbei bereinigt: die Modal-Titel gehen jetzt durch `json_encode()` statt durch
`specialchars()` + manuelles Quote-Escaping (wie in Contao Core).

### 3.6 Erzeugtes MooTools-JS

`SelectPropertyAllHandler::handlePropertyFileTree()` erzeugte einen `$(…).addEvent(…)`-
Block mit `Backend.toggleCheckboxes`. Jetzt vanilla (`addEventListener` +
`querySelectorAll('input[type="checkbox"][id^="…"]')`). Die reine Debug-Ausgabe über
`GeneralLogger.info()` ist dabei entfallen.

`$GLOBALS['TL_MOOTOOLS']` bleibt als **Injektionspunkt** bestehen: der Name ist
historisch, das Array ist in Contao 5.7 schlicht die Ablage für Inline-JS am Body-Ende
und nicht deprecated.

## 4. Was bewusst bleibt

Diese Contao-APIs haben in 5.7 **keinen** vanilla- oder Stimulus-Ersatz. Sie sind kein
Versäumnis, sondern die Grenze der Umstellung:

| API | genutzt in | Anmerkung |
| --- | --- | --- |
| `Backend.openModalSelector()` | `widget_filetree`, `widget_common_picker`, `dc_general_wizard_common_picker` | nicht deprecated; Contaos eigener `contao--modal-selector`-Controller ruft sie intern selbst auf |
| `AjaxRequest.displayBox()` / `hideBox()` | `vanillaGeneral.js` | Ladeanzeige, kein Ersatz |
| `new Picker.Date()` | `ContaoWidgetManager::buildDatePicker()` | Datepicker; Element-Lookup wurde bereits auf `document.getElementById()` umgestellt, der Konstruktor bleibt |
| `SimpleModal` | indirekt über `openModalSelector` | — |

Sobald Contao hier nachzieht, gehört das erneut geprüft.

## 5. Offen — Stufe 2

### 5.1 `generalDriver_src.js` (55 Stellen)

Die globale API `BackendGeneral`, aus PHP über `onclick`-Attribute aufgerufen. Umbau
funktionsweise:

| Funktion | Stellen | Bemerkung |
| --- | --- | --- |
| `loadSubTree` | 25 | `Request.Contao` → `fetch`; `new Element(…).inject()` → `createElement`/`after()`; `.store('tip:title', …)` hat kein direktes Gegenstück mehr (Contao nutzt `contao--tooltips`) |
| `toggleVisibility` | 24 | **heikelster Teil**: verschachtelte DOM-Traversierung für Baum-, Listen- und Parent-Ansicht plus Icon-Namens-Arithmetik. Nur mit Klicktest in allen drei Ansichten umzubauen |
| `confirmDelete` | 12 | reiner DOM-Aufbau, unkritisch |
| `displayMessage` / `hideMessage` | 13 | reiner DOM-Aufbau, `window.getScroll()` → `window.scrollY` |
| `setLegendState` | 6 | `Request.Contao` → `fetch` |
| `autoSubmit` | 3 | fast schon vanilla |
| `confirmSelectOverrideEditAll` | 1 | `$$(collection).each` → `Array.from(...).some(...)` |

Zusätzlich zu klären: `window.fireEvent('ajax_change')` bzw. `'structure'`. Contao-Core-
Listener hängen noch am MooTools-Window-Event, der neue `contao--check-all`-Controller
dagegen an `document.addEventListener('ajax_change', …)`. Übergangsweise müssen **beide**
gefeuert werden.

### 5.2 `vanillaGeneral.js` (8 Stellen)

Trotz des Namens nicht vanilla. Alle Treffer liegen in **einer** Stelle — dem
Modal-Callback um Zeile 336–354 (`Request.Contao`, `AjaxRequest.displayBox/hideBox`,
`Browser.exec`, `$()`, `.getParent().set('html')`, `window.fireEvent`). Der Rest der
Datei (Tabellen-Drag&Drop nach isocra, `GeneralLogger`, `GeneralEnvironment`) ist bereits
vanilla.

Der Umbau ist klein: `DcGeneral.post()` / `DcGeneral.setHtml()` aus `dcGeneralAjax.js`
decken den Fall bereits ab, `displayBox`/`hideBox` bleiben (Abschnitt 4).

## 6. Prüfstand

Statisch verifiziert: Psalm (0 Fehler auf den geänderten Dateien), phpcs PSR12,
`php -l` auf allen Templates, `node --check` auf den JS-Dateien. Zusätzlich wurden alle
emittierten Stimulus-Bezeichner gegen `vendor/contao/core-bundle/assets/controllers/`
abgeglichen (Controller-Name, Methodenname, Target-Name).

**Nicht automatisiert abgedeckt** — beim nächsten Backend-Durchgang klicken:

* Listenansicht: „Alle auswählen", Shift-Bereichsauswahl, Sortier-Drag&Drop
* Baumansicht: Auf-/Zuklappen, „Alle auswählen", Sichtbarkeits-Toggle
* Eingabemaske: Speichern mit Feldfehler (springt die Seite zum Fehler?), Autofokus,
  Datepicker, Farbwähler
* Datei-Picker: Auswahl ändern, danach sortieren und einzeln entfernen — insbesondere
  **nach** dem Ajax-Reload (das ging vorher nicht)
* Baum-Picker im Popup: „Alle auswählen", Übernehmen
* „Alle bearbeiten"/„Alle überschreiben": Auswahl eines `fileTree`-Feldes zieht das
  zugehörige Order-Feld mit
* Konsole auf `is deprecated`-Warnungen prüfen
