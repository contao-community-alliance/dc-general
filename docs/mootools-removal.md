# Ausbau von MooTools im dc-general

> Status: **Stufe 1 abgeschlossen, Stufe 2 begonnen.** Alle in Contao 5.7 als *deprecated*
> markierten Backend-JS-Aufrufe sind aus dem dc-general verschwunden, ebenso das
> Inline-MooTools in den Templates. Die Ajax-Schicht ist vereinheitlicht; der
> MooTools-DOM-Code der beiden großen JS-Dateien steht noch aus.
>
> - [x] 1 – Bestandsaufnahme (Abschnitt 2)
> - [x] 2 – `Backend.getScrollOffset()` (23 Stellen) → `contao--scroll-offset#store`
> - [x] 3 – `Backend.toggleCheckboxes()` (4 Stellen) → `contao--check-all`
> - [x] 4 – `Backend.vScrollTo()` (3 Stellen) → `contao--scroll-offset`-Targets
> - [x] 5 – `Backend.makeMultiSrcSortable()` (3 Stellen) → `contao--sortable` + `contao--input-map`
> - [x] 6 – Inline-MooTools in den Picker-Templates → `fetch` (`generalAjax.js`)
> - [x] 7 – tote Build-Artefakte `generalDriver.js` / `.js.map` entfernt
> - [x] 8 – eine Ajax-Schicht, einheitliche Dateinamen (Abschnitt 4)
> - [x] 9 – tote `setLegendState`-Kette entfernt (Abschnitt 4.4)
> - [ ] 10 – `generalDriver.js` auf Vanilla umbauen (Abschnitt 6)
> - [ ] 11 – `generalBase.js` auf Vanilla umbauen (Abschnitt 6)
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

Die Dateinamen dieser Tabelle sind der historische Stand; seit Abschnitt 4 heißen sie
`generalDriver.js`, `generalBase.js` und `generalAjax.js`.

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

`Request.Contao` → `fetch`, gekapselt in **`src/Resources/public/js/generalAjax.js`**
(damals noch `dcGeneralAjax.js`, siehe Abschnitt 4.2):

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

## 4. Umgesetzt in Stufe 2: eine Ajax-Schicht, einheitliche Dateinamen

### 4.1 Drei Ajax-Wege wurden einer

Das Bundle sprach auf drei Arten mit dem Server: das MooTools-`Request.Contao`, der
selbstgebaute `GeneralAjaxCaller` und die mit Stufe 1 eingeführten `DcGeneral`-Helfer.
Alles, was nicht am MooTools-DOM hängt, läuft jetzt über die letzteren.

`GeneralAjaxCaller` ist entfallen (76 Zeilen). Sein `sendPost()` hatte **keinen einzigen
Aufrufer** und war obendrein defekt — es übergab die Nutzdaten an `setRequestHeader()`,
statt sie als Body zu senden. Genutzt wurde nur `sendGet()`; dafür gibt es jetzt
`DcGeneral.get()`. Die Accessoren `getAjax()`/`setAjax()` von `GeneralEnvironment` sind
damit ebenfalls weg, `getLogger()`/`getDom()` bleiben.

In `generalDriver.js` nutzen die Fire-and-Forget-Posts jetzt `DcGeneral.post()`.
`Request.Contao` sinkt damit von **7 auf 3** Stellen (zwei in `loadSubTree` und
`toggleVisibility`, eine im Modal-Callback von `generalBase.js`). Zwei der umgestellten
Posts sind mit Abschnitt 4.4 gleich ganz entfallen.

`generalAjax.js` wird in `config.php` als **erstes** registriert, da die anderen Skripte
darauf aufbauen.

### 4.2 Dateinamen

| vorher | nachher |
| --- | --- |
| `dcGeneralAjax.js` | `generalAjax.js` |
| `vanillaGeneral.js` | `generalBase.js` (die Datei war nie vanilla) |
| `generalDriver_src.js` | `generalDriver.js` |

Das `_src`-Suffix ergab nur mit dem prepros-Build Sinn, und der war längst auseinander-
gelaufen: das gebaute `generalDriver.js` war veraltet und wurde entfernt, seitdem wurde
die Quelldatei unter Quell-Namen ausgeliefert. Wie bei den Stylesheets verzichten wir auf
Minifizierung — die Dateien sind klein. Die verwaiste `generalDriver.css.map` und das
letzte SCSS-Partial sind mit entfallen.

### 4.3 Nebenbefund: Drag-&-Drop-Sortieren war defekt

Beim Prüfen des umgestellten Pfades antwortete der Server mit HTTP 500
(`Invalid language file name "tl_metamodel_dcasetting?"`). `GeneralTableDnD.onDrop()`
baute die URL aus `window.location.search` **plus** `'?'` — das Suchfragment bringt das
Zeichen aber schon mit, das zweite landete im Wert des letzten Parameters. Der Fehler ist
älter als die Umstellung; das synchrone XHR erzeugte dieselbe URL, nur wurde die Antwort
nie ausgewertet. Behoben, die Sortierung übersteht jetzt den Reload.

### 4.4 `setLegendState` komplett entfallen

Die Funktion sollte den Auf-/Zuklapp-Zustand der Palette-Legenden über die Session
merken. Die Kette war an **jeder** Stelle unterbrochen:

* `BackendGeneral.setLegendState` hatte keinen Aufrufer — kein Template und kein PHP
  erzeugte je ein `onclick` darauf.
* Der Server-Handler `Ajax3X::setLegendState()` war nur über `action=setLegendState`
  erreichbar. Das sendet niemand; Contao 5.7 klappt Fieldsets über seinen eigenen
  `toggle-fieldset`-Controller mit `action=toggleFieldset` auf und zu und legt das
  Ergebnis unter `fieldset_states` ab — einen Schlüssel, den der dc-general nicht liest.
* Damit wurde der Session-Schlüssel `LEGENDS` nie geschrieben, und
  `EditMask::getLegendStates()` lieferte immer ein leeres Array. `isLegendVisible()` fiel
  folglich **immer** auf `$legend->isInitialVisible()` zurück.

Entfernt wurden daher JS-Funktion, Dispatch-Eintrag, die abstrakte Deklaration in
`Ajax`, die Implementierung in `Ajax3X`, der Leser `getLegendStates()` samt
`isLegendVisible()` und der Session-Schlüssel aus `config.yml`. **An der Darstellung
ändert sich nichts** — die Legenden richteten sich schon vorher ausschließlich nach der
Palette-Definition.

## 5. Was bewusst bleibt

Diese Contao-APIs haben in 5.7 **keinen** vanilla- oder Stimulus-Ersatz. Sie sind kein
Versäumnis, sondern die Grenze der Umstellung:

| API | genutzt in | Anmerkung |
| --- | --- | --- |
| `Backend.openModalSelector()` | `widget_filetree`, `widget_common_picker`, `dc_general_wizard_common_picker` | nicht deprecated; Contaos eigener `contao--modal-selector`-Controller ruft sie intern selbst auf |
| `AjaxRequest.displayBox()` / `hideBox()` | `generalBase.js` | Ladeanzeige, kein Ersatz |
| `new Picker.Date()` | `ContaoWidgetManager::buildDatePicker()` | Datepicker; Element-Lookup wurde bereits auf `document.getElementById()` umgestellt, der Konstruktor bleibt |
| `SimpleModal` | indirekt über `openModalSelector` | — |

Sobald Contao hier nachzieht, gehört das erneut geprüft.

## 6. Offen — Rest von Stufe 2

Was hier steht, ist der **MooTools-DOM-Code**. Die Ajax-Schicht ist mit Abschnitt 4
erledigt; die verbliebenen drei `Request.Contao` hängen an `AjaxRequest.displayBox()` und
an `onSuccess`-Callbacks voller MooTools-DOM-Aufrufe, lassen sich also erst zusammen mit
dem umgebenden Code umbauen.

### 6.1 `generalDriver.js`

Die globale API `BackendGeneral`, aus PHP über `onclick`-Attribute aufgerufen. Umbau
funktionsweise:

| Funktion | Stellen | Bemerkung |
| --- | --- | --- |
| `loadSubTree` | 25 | die beiden Zustands-Posts laufen bereits über `DcGeneral.post()`; offen bleiben der ladende Zweig (`Request.Contao` mit `onSuccess`), `new Element(…).inject()` → `createElement`/`after()` und `.store('tip:title', …)`, das kein direktes Gegenstück mehr hat (Contao nutzt `contao--tooltips`) |
| `toggleVisibility` | 24 | **heikelster Teil**: verschachtelte DOM-Traversierung für Baum-, Listen- und Parent-Ansicht plus Icon-Namens-Arithmetik. Nur mit Klicktest in allen drei Ansichten umzubauen |
| `confirmDelete` | 12 | reiner DOM-Aufbau, unkritisch |
| `displayMessage` / `hideMessage` | 13 | reiner DOM-Aufbau, `window.getScroll()` → `window.scrollY` |
| `autoSubmit` | 3 | fast schon vanilla |
| `confirmSelectOverrideEditAll` | 1 | `$$(collection).each` → `Array.from(...).some(...)` |

Zusätzlich zu klären: `window.fireEvent('ajax_change')` bzw. `'structure'`. Contao-Core-
Listener hängen noch am MooTools-Window-Event, der neue `contao--check-all`-Controller
dagegen an `document.addEventListener('ajax_change', …)`. Übergangsweise müssen **beide**
gefeuert werden.

### 6.2 `generalBase.js`

Alle verbliebenen Treffer liegen in **einer** Stelle — dem Modal-Callback von
`GeneralTreePicker` (`Request.Contao`, `AjaxRequest.displayBox/hideBox`, `Browser.exec`,
`$()`, `.getParent().set('html')`, `window.fireEvent`). Der Rest der Datei
(Tabellen-Drag&Drop nach isocra, `GeneralLogger`, `GeneralEnvironment`) ist vanilla.

Der Umbau ist klein: `DcGeneral.post()` / `DcGeneral.setHtml()` decken den Fall ab,
`displayBox`/`hideBox` bleiben (Abschnitt 5).

## 7. Prüfstand

Statisch verifiziert: Psalm (0 Fehler auf den geänderten Dateien), phpcs PSR12,
`php -l` auf allen Templates, `node --check` auf den JS-Dateien. Zusätzlich wurden alle
emittierten Stimulus-Bezeichner gegen `vendor/contao/core-bundle/assets/controllers/`
abgeglichen (Controller-Name, Methodenname, Target-Name).

**Im Backend durchgespielt** (Playwright gegen den Devstack, angemeldete Session):

* Listenansicht: „Alle auswählen" hakt alle an und wieder ab, `contao--check-all` am
  Container, keine `scroll-offset`-Altlast im Markup
* Eingabemaske: `generalAjax.js` eingebunden, `DcGeneral.post/get/setHtml` verfügbar,
  `GeneralAjaxCaller` und `GeneralEnvironment.getAjax()` verschwunden
* Datei-Widget: `contao--sortable` und `contao--input-map` verdrahtet, Drag&Drop ändert
  die Reihenfolge, der Hidden-Wert entspricht danach der DOM-Reihenfolge, der Button am
  Vorschaubild entfernt Eintrag **und** Wert
* Datepicker öffnet
* Sortier-Drag&Drop in der Listenansicht: Reihenfolge übersteht den Reload (siehe 4.3)
* Konsole: keine `is deprecated`-Warnung zu einer der ersetzten APIs. Es bleiben drei
  Contao-eigene (`Theme.stopClickPropagation()`, `Theme.setupSplitButtonToggle()`) — die
  Bezeichner kommen ausschließlich im `core-bundle` vor.

**Noch offen** — beim nächsten Durchgang klicken:

* Baumansicht: Auf-/Zuklappen, Sichtbarkeits-Toggle (beides noch `Request.Contao`)
* Eingabemaske: Speichern mit Feldfehler (springt die Seite zum Fehler?), Autofokus,
  Farbwähler
* Baum-Picker im Popup: „Alle auswählen", Übernehmen
* „Alle bearbeiten"/„Alle überschreiben": Auswahl eines `fileTree`-Feldes zieht das
  zugehörige Order-Feld mit

> Achtung beim Testen: klappt man ein Palette-Fieldset zu, merkt sich Contao das
> serverseitig. Ein verstecktes Widget hat dann keine Bounding-Box, und ein Folgelauf
> scheitert scheinbar grundlos an Drag&Drop.
