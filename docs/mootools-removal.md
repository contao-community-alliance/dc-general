# Ausbau von MooTools im dc-general

> Status: **Stufe 1 und 2 abgeschlossen.** Der eigene Code des dc-general ist vanilla —
> deprecated Backend-APIs, Inline-MooTools in den Templates und der MooTools-DOM-Code der
> beiden großen JS-Dateien sind verschwunden, und kein Markup fordert Contao mehr zu einem
> deprecated Helfer auf. Es bleiben allein die Contao-Einstiegspunkte, für die es keinen
> Ersatz gibt — sie stehen vollständig in Abschnitt 5.
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
> - [x] 10 – zwei in Contao 5 entfernte `Backend`-APIs ersetzt (Abschnitt 4.5)
> - [x] 11 – `generalDriver.js` auf Vanilla umbauen (Abschnitt 6)
> - [x] 12 – `generalBase.js` auf Vanilla umbauen (Abschnitt 6)
> - [x] 13 – Marker-Klassen für deprecated Helfer aus dem Markup (Abschnitt 6.1)
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

### 4.5 Zwei Contao-APIs, die es nicht mehr gibt

Beim Audit fielen zwei Aufrufe in `WidgetBuilder` auf, die **nicht** nur veraltet waren,
sondern beim Klick geworfen haben — die Funktionen fehlen im core-bundle vollständig:

* `Backend.toggleWrap()` (Zeilenumbruch-Schalter an Textareas ohne RTE) wurde ersatzlos
  gestrichen; übrig ist allein die CSS-Klasse `.toggleWrap` im Theme, und Contao rendert
  selbst keinen solchen Button mehr. Da der dc-general ihn weiterhin anbietet, liegt das
  Verhalten jetzt in `BackendGeneral.toggleWrap()` — vanilla, also ohne neue Altlast.
* `Backend.openWindow()` des Hilfe-Assistenten ist ebenfalls weg. Contao öffnet seine
  eigene Hilfe über `Backend.openModalIframe()` (siehe `DataContainer::generateHelp()`).
  Genau das tat der `helptext`-Zweig wenige Zeilen darüber längst; der `helpwizard`-Zweig
  folgt ihm nun und baut die URL über die Route `contao_backend_help`, statt `/contao/help`
  fest zu verdrahten.

### 4.6 Dark-Mode-Icon des Sichtbarkeits-Schalters

`toggleVisibility` tauschte nur das helle Icon; das dunkle behielt bis zum Neuladen den
alten Zustand. Der Dateiname der Dark-Variante wurde aus dem **aktiven** Farbschema
abgeleitet:

```js
const postfixDark = colorScheme === 'dark' ? '--dark' : '';
```

Beide Varianten stehen aber immer im Markup, das Schema entscheidet nur, welche CSS
anzeigt. Im Light-Modus war der Postfix leer, also wurde `invisible.svg` in
`invisible--dark.svg` gesucht — kein Treffer, das Bild blieb stehen. Das Suffix ist jetzt
fest `--dark`. Nebenbei entfiel ein `console.log()`, das bei jedem Klick feuerte.

## 5. Was bewusst bleibt

Diese Contao-APIs haben in 5.7 **keinen** vanilla- oder Stimulus-Ersatz. Sie sind kein
Versäumnis, sondern die Grenze der Umstellung:

| API | genutzt in | Anmerkung |
| --- | --- | --- |
| `Backend.openModalSelector()` | `widget_filetree`, `widget_common_picker`, `dc_general_wizard_common_picker` | nicht deprecated; Contaos eigener `contao--modal-selector`-Controller ruft sie intern selbst auf |
| `AjaxRequest.displayBox()` / `hideBox()` | `generalDriver.js` (`loadSubTree`), `generalBase.js` (Modal-Callback) | Ladeanzeige, kein Ersatz — **nur an diesen beiden Stellen**, siehe 6.2 |
| `window.fireEvent('ajax_change')` | `generalDriver.js`, `generalBase.js` | dokumentierter Contao-Hook; in `core.js` hängen MooTools-Listener daran, die ein `dispatchEvent` nie erreicht |
| `new Picker.Date()` | `ContaoWidgetManager::buildDatePicker()` | Datepicker; Element-Lookup wurde bereits auf `document.getElementById()` umgestellt, der Konstruktor bleibt |
| `SimpleModal` | indirekt über `openModalSelector` | — |

Sobald Contao hier nachzieht, gehört das erneut geprüft.

## 6. Umgesetzt in Stufe 2: der MooTools-DOM-Code

`generalDriver.js` (91 Aufrufe) und `generalBase.js` (8) sind vanilla. Was an die Stelle
der MooTools-Bequemlichkeiten getreten ist:

| MooTools | vanilla |
| --- | --- |
| `$(id)` | `document.getElementById(id)` |
| `new Element('li', {...}).inject(x, 'bottom')` | `document.createElement()` + `appendChild()`/`after()` |
| `.getParent('li')` | `.closest('li')` |
| `.getPrevious('td')` / `.getNext('div')` | `siblingMatching()` — siehe unten |
| `.getFirst('div.list_icon')` | `firstChildMatching()` — `:scope > selector` |
| `.getElement()` / `.getElements()` | `querySelector()` / `querySelectorAll()` |
| `.hasClass()` / `.addClass()` / `.removeClass()` | `classList` |
| `.setStyle()` / `.getStyle()` | `.style.x` / `getComputedStyle()` |
| `$$(collection).each()` | `Array.prototype.some.call()` |
| `.toInt()` | `parseInt(x, 10)` |
| `window.getSize().y` / `window.getScroll()` | `window.innerHeight` / `window.scrollY` |
| `Browser.exec()` | `DcGeneral.runScript()` (neu in `generalAjax.js`) |
| `Request.Contao` | `DcGeneral.post()` / `DcGeneral.get()` |
| `window.fireEvent('structure')` | `window.dispatchEvent(new CustomEvent('structure'))` |

Zwei Helfer ersetzen, was vanilla fehlt: `siblingMatching()` läuft die Geschwister in eine
Richtung ab und liefert das erste passende — `previousElementSibling` überspringt im
Gegensatz zu MooTools nicht das, was nicht passt. `firstChildMatching()` bildet
`getFirst(selector)` über `:scope >` ab.

**`.store('tip:title', …)` ist ersatzlos entfallen.** Das war die Ablage der alten
MooTools-Tooltips; Contao 5.7 liest den Tooltip aus dem `title`-Attribut
(`contao--tooltips`), also wird jetzt `el.title` gesetzt.

**`window.fireEvent('ajax_change')` bleibt.** Contao feuert diesen Hook in seinem eigenen
`toggle-nodes`-Controller ebenfalls über MooTools (mit dem Kommentar „HOOK (see #6752)"),
und in `core.js` hängen MooTools-Listener daran. Ihn vanilla zu ersetzen würde die
Integration brechen.

Zwei Altlasten sind dabei aufgefallen und mitkorrigiert:

* `hideMessage()` rief `remove()` **außerhalb** der Null-Prüfungen auf und warf damit, wenn
  keine Box offen war. Jetzt steht es innerhalb.
* In `loadSubTree()` taten beide Zweige der `mode`-Abfrage dasselbe; sie sind
  zusammengefasst, die Variable entfällt.

### 6.1 Marker-Klassen für deprecated Helfer

Drei Warnungen kamen nicht aus unserem JavaScript, sondern aus **Markup**, das Contao als
Auftrag versteht, einen veralteten Helfer anzuwerfen:

| Marker | wo | ersetzt durch |
| --- | --- | --- |
| `click2edit` am `<tr>`/`<li>` | `dcbe_general_common_list`, `dcbe_general_treeview_entry` | `data-controller="contao--deeplink"` + Ziele `primary`/`secondary` |
| `id="sbtog"` am Umschalter | `dc_general_submit_button` | `contao--toggle-sender` / `contao--toggle-receiver` |
| `picker_selector` am `<ul>` | `widget_treepicker_popup` | ersatzlos entfallen |

Alle drei Marker waren reine Hinweise für BC-Shims. Contaos `deeplink-controller` sucht
`.click2edit`, **entfernt die Klasse** und hängt `contao--deeplink` samt Zielen aus `a.edit`
und `a.children` an — das Markup erzeugen wir jetzt direkt, `ButtonRenderer` markiert die
Ziele. Der Split-Button folgt `backend/data_container/buttons.html.twig` aus dem Core;
`sbtog` nutzt Contao selbst gar nicht mehr, wir waren der einzige Verwender.

**`picker_selector` ist ersatzlos entfallen.** Die Klasse hat in Contao keine andere
Verwendung — kein CSS, kein weiteres JS — und bewirkt allein, dass `stopClickPropagation()`
Klicks auf Links und Checkboxen am Hochblubbern hindert. Im Popup gibt es aber nichts, wohin
sie blubbern könnten: der `contao--check-all`-Controller hängt nur `keydown`/`keyup` ans
Dokument, `toggleInput`/`toggleAll` sitzen per `data-action` an den Eingaben selbst, und im
Container steht überhaupt kein `<a>`. Ein Ersatz-Listener war also nicht nötig.

### 6.2 Was der Umbau selbst kaputt gemacht hat

Drei Regressionen sind erst im Betrieb aufgefallen, nicht im Prüfstand. Sie stehen hier,
weil jede von ihnen eine Lehre über die Übersetzung MooTools → vanilla enthält:

* **Der Sichtbarkeits-Schalter blockierte die Seite.** Beim Umbau ist
  `AjaxRequest.displayBox()` in `toggleVisibility()` gewandert — die Ladeanzeige gab es
  dort nie, nur in `loadSubTree()`. Jeder Klick legte damit ein Overlay „Daten werden
  geladen…" über die Liste. Wieder entfernt; die Ladeanzeige bleibt allein am Aufklappen
  des Baums, wo das Warten sichtbar sein soll.
* **Der Schalter holte die ganze Folgeseite.** `Request.Contao` lief mit
  `followRedirects: false`; `fetch()` folgt Weiterleitungen dagegen von sich aus. Die
  Antwort auf das Umschalten ist eine Weiterleitung, also lud der Browser bei jedem Klick
  im Hintergrund die komplette Listenansicht nach — daher war das Umschalten spürbar
  langsamer als im Core. Der Aufruf nutzt jetzt `{redirect: 'manual'}`.
* **Kleine Vorschaubilder blieben leer.** Der Entfernen-Button des `contao--input-map`
  liegt absolut positioniert über der Vorschau und verdeckte sie bei Icon-Größen
  vollständig — das Bild war geladen und dekodiert, nur unsichtbar. `generalDriver.css`
  setzt den Listeneintrag jetzt auf `flex` und den Button auf `position: static`, damit er
  neben statt über dem Bild sitzt.

> **Lehre für beide ersten Punkte:** eine MooTools-Option ohne vanilla-Entsprechung
> verschwindet beim Portieren lautlos. `followRedirects`, `evalScripts`, `urlEncoded` haben
> in `fetch()` andere Vorgaben als in `Request.Contao` — beim Umschreiben gehört jede
> Option einzeln übersetzt, nicht nur die URL und der Callback.

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
* Konsole: **keine** `is deprecated`-Warnung mehr — geprüft in Listenansicht, Baumansicht,
  Eingabemaske und im Baum-Picker-Popup.
* Baum-Picker-Popup nach dem Entfernen von `picker_selector`: Checkbox schaltet um und
  zurück, „Alle auswählen" hakt alle an (6/6) und wieder ab.

  > Hier stand zwischenzeitlich, die verbliebenen Warnungen zu
  > `Theme.stopClickPropagation()` und `Theme.setupSplitButtonToggle()` seien Contao-eigen,
  > weil die Bezeichner nur im `core-bundle` vorkommen. **Das war ein Fehlschluss.** Contao
  > ruft die beiden Helfer zwar selbst auf, sie warnen aber nur, wenn die Seite das Markup
  > mitbringt, nach dem sie suchen — und das kam aus diesem Paket:
  >
  > ```js
  > if (window.console && $$('.picker_selector,.click2edit').length) { console.warn(…); }
  > ```
  >
  > Wer eine Deprecation prüft, muss also die **Bedingung** lesen, unter der sie feuert,
  > nicht nur den Ort des `console.warn`. Siehe Abschnitt 6.1.

* Baumansicht (`mm_trans_hierarchie` und die zweistufige Variantenhierarchie
  `mm_test_variants`): Auf- und Zuklappen in beide Richtungen, Kindknoten werden
  nachgeladen, und der Faltzustand übersteht den Reload — letzteres belegt, dass die auf
  `DcGeneral.post()` umgestellten Fire-and-Forget-Posts serverseitig ankommen.
* Sichtbarkeits-Schalter in der Render-Settings-Liste: beide Icon-Varianten wechseln
  synchron, der Zustand übersteht den Reload (siehe 4.6).
* `BackendGeneral.toggleWrap()`: `soft → off → soft`, Rückgabe `false`, unbekannte id
  wirft nicht.
* Nach dem Vanilla-Umbau der beiden JS-Dateien erneut durchgespielt: Listenansicht,
  Auswahlmodus, Datei-Widget, Datepicker, beide Baumansichten (auf-/zuklappen samt
  Persistenz — das ist `loadSubTree` in beiden Zweigen), Sichtbarkeits-Schalter mit
  Dark/Light-Icons, Sortier-Drag&Drop. Dazu `displayMessage`/`hideMessage` direkt
  aufgerufen: Box und Overlay werden angelegt, befüllt, wieder entfernt, und ein zweiter
  `hideMessage()` wirft nicht mehr.

**Noch offen** — beim nächsten Durchgang klicken:

* Eingabemaske: Speichern mit Feldfehler (springt die Seite zum Fehler?), Autofokus,
  Farbwähler
* Baum-Picker im Popup: Übernehmen in das aufrufende Feld („Alle auswählen" und das
  einzelne Ankreuzen sind beim Entfernen von `picker_selector` geprüft worden, siehe 6.1)
* „Alle bearbeiten"/„Alle überschreiben": Auswahl eines `fileTree`-Feldes zieht das
  zugehörige Order-Feld mit
* Sichtbarkeits-Schalter in der **Baum-** und der **Parent-Ansicht** — `toggleVisibility`
  verzweigt dort anders, und in keiner der beiden Ansichten der Testdaten gibt es einen
  solchen Button. Für genau diese Zweige fehlt die Absicherung; sollte der Schalter auf
  Contaos Link-Modell umgestellt werden (Abschnitt 9), entfallen sie ohnehin.
* Der `helpwizard`-Zweig aus 4.5 — kein DCA in den Paketen setzt `eval.helpwizard`, der
  Zweig greift nur bei Fremd-DCAs und war deshalb nicht auslösbar.

Zwei Fallen, die beim Testen Zeit gekostet haben:

> Klappt man ein Palette-Fieldset zu, merkt sich Contao das serverseitig. Ein verstecktes
> Widget hat dann keine Bounding-Box, und ein Folgelauf scheitert scheinbar grundlos an
> Drag&Drop.

> Der Icon-Tausch von `toggleVisibility` passiert erst im `onSuccess` des Requests. Wer
> mit einer festen Wartezeit statt auf die Antwort prüft, bekommt sporadische Fehlschläge.

## 8. Zustand der Nachbarpakete

Der Audit lief über die Paketgrenze hinaus, weil die Buttons der MetaModels-Tabellen aus
deren DCAs stammen.

Inzwischen sind **alle 31 MetaModels-Pakete** geprüft. Sie sind frei von den in Contao 5.7
deprecated APIs; was bleibt, steht unten unter „bewusst behalten".

| Paket | erledigt |
| --- | --- |
| `core` | die 27 `onclick`-Attribute der statischen DCAs, die **zur Laufzeit gebauten** Kommandos in `CommandBuilder` (Kopieren/Verschieben/Löschen aller Item-Tabellen — diese Fundstellen tauchen in keiner DCA-Datei auf), die Bearbeiten-Operation in `LoadDataContainer`, der „Alle auswählen"-Schalter in `add-all.html.twig`, die `domready`-Initialisierung von `be_ace_mm.html5` |
| `notelist` | 2 `onclick`-Attribute in `tl_metamodel_notelist.php` |
| `attribute_levenshtein` | Zurück-Button des Suchindex-Neuaufbaus |
| `attribute_contentarticle` | Widget-Skript komplett: `$()`, `addEvent()`, `Request.Contao`, `Browser.exec()` |
| `attribute_translatedcontentarticle` | dito, unterscheidet sich nur im `lang`-Parameter |

> **Achtung beim Suchen:** ein Muster `window.addEvent` trifft auch
> `window.addEventListener`. `attribute_rating` sah dadurch betroffen aus, ist aber sauber.
> Ebenso findet ein Grep nach Dateinamen keine Templates, die über Konventionen gewählt
> werden — `be_ace_mm.html5` wird nirgends namentlich referenziert und ist trotzdem in
> Benutzung (siehe unten).

**Die Contentarticle-Widgets** sind denselben Weg gegangen wie die Picker des dc-general:
`addEventListener()` statt `addEvent()`, `DcGeneral.post()`/`setHtml()` statt
`Request.Contao`. Für `Browser.exec()` braucht es keinen Nachfolger, weil `setHtml()` die
Skripte des neuen Markups selbst ausführt. `dc-general` ist in beiden Paketen harte
Abhängigkeit, die Helfer stehen also bereit.

**Ein MooTools-Aufruf bleibt dort bewusst stehen:** `window.addEvent('sm_hide', …)`.
SimpleModal feuert das Ereignis über `window.fireEvent()`
(`assets/simplemodal/js/simplemodal.js`), also über MooTools' eigenes Eventsystem — ein
`addEventListener` empfängt es nie. Und `Backend.openModalIframe()` nimmt keinen Callback,
anders als das `openModalSelector()`, mit dem der dc-general davonkam. Einen anderen Haken,
um das Schließen des Modals zu bemerken, bietet Contao 5.7 nicht.

**Wo die Änderungen liegen:** die des `core` stehen auf einem eigenen Branch
`hotfix/mootools-removal`, getrennt von der parallel laufenden Twig-Arbeit auf
`hotfix/fix_twig_support`. Wer den zweiten auscheckt, hat die MooTools-Fixes **nicht** dabei
— vor einem Release müssen beide zusammengeführt werden. Die vier übrigen Pakete tragen
ihre Änderung jeweils auf ihrem laufenden Branch.

**Zur Template-Auflösung** (relevant für den Twig-Umbau): `ContaoWidgetManager` lädt die
RTE-Templates über `new BackendTemplate('be_' . $rteBase)`, also die Legacy-Engine.
Trotzdem funktioniert `rte = ace`, obwohl Contao dafür nur noch `be_ace.html.twig`
mitbringt — die Template-Hierarchie von Contao 5 löst Twig auch hier auf. Ebenso
berücksichtigt `TemplateList::getTemplatesForBaseFrom()` die Endung `.html.twig` bereits.
Einer Twig-Fassung der MetaModels-RTE-Templates steht damit nichts im Weg.

## 9. Offene Entscheidung: das Modell des Sichtbarkeits-Schalters

Der Schalter ist die letzte Stelle, an der der dc-general grundsätzlich anders arbeitet als
der Core — und daran hängt ein Anzeigefehler, der sich im jetzigen Modell nicht sauber
beheben lässt.

**Der Befund.** In der Baumansicht einer Variantenhierarchie erben die Varianten Werte vom
nicht-varianten Datensatz, unter anderem `published`. Schaltet man den Elternsatz um,
ändert sich der Zustand der Varianten fachlich mit — ihre Icons bleiben aber stehen, bis
die Seite neu geladen wird. Das ist folgerichtig: `toggleVisibility()` tauscht nach der
Antwort genau **eine** Bildquelle aus, nämlich die des angeklickten Eintrags. Von der
Vererbung weiß der Client nichts, und er kann es auch nicht wissen, ohne die Regeln des
Servers nachzubauen.

**Contaos Modell.** Dort ist der Schalter ein gewöhnlicher Link. Turbo Drive fängt ihn ab,
holt die Antwort und tauscht den `<body>`; der Server rendert dabei jede Zeile neu, und
abgeleitete Zustände stimmen ohne Zutun des Clients. Ein Umstieg würde

* diesen Fehler strukturell erledigen statt ihn zu umgehen,
* `toggleVisibility()` samt Icon-Tausch, Dark-Mode-Sonderfall (4.6) und den beiden in 6.2
  beschriebenen Fallen ersatzlos entfallen lassen,
* den Schalter dem Verhalten des Cores angleichen, das Redakteure ohnehin kennen.

Dagegen steht, dass jeder Klick eine vollständige Liste rendert statt eines
Statuswechsels — bei großen Listen und teuren Renderern der dc-general ist das nicht
umsonst zu haben, und die Baum- und Parent-Ansicht müssten mitgezogen werden.

**Stand:** zur Entscheidung im Team. Bis dahin bleibt das jetzige Verhalten; der
Anzeigefehler betrifft ausschließlich die geerbten Icons der Varianten, der gespeicherte
Zustand ist in allen Fällen korrekt.
