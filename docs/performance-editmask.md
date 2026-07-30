# Laufzeit der Eingabemaske

> Stand: **erster Schritt umgesetzt** — der quadratische Aufbau in
> `ContaoWidgetManager::getWidget()` ist beseitigt. Der zweite Schritt (die Konvertierungen je
> `setProperty` in MetaModels) wurde **versucht und wieder verworfen**, siehe
> [Verworfen: der Vergleich auf der Speicherform](#verworfen-der-vergleich-auf-der-speicherform).
> Dabei kam der eigentliche Hebel zum Vorschein: die
> [kaputte Änderungserkennung](#der-eigentliche-hebel-die-änderungserkennung).

## Der Befund

`ContaoWidgetManager::processInput()` ruft `getWidget()` **je Property** auf. Jeder Aufruf
baute bisher das gesamte Modell neu auf:

```php
$model = clone $this->model;                              // ① voller Model-Klon
foreach ($inputValues->getIterator() as $name => $value) { // ② über ALLE Werte
    $values->setPropertyValue($name, $this->encodeValue($name, $value, $inputValues));
}
$controller->updateModelFromPropertyBag($model, $values);  // ③ setProperty für ALLE
```

Bei *n* Properties also *n* Klone, *n²* `encodeValue`-Ereignisse und *n²* `setProperty`.

Verstärkt wird das in MetaModels: **ein** `setProperty()` in
`MetaModels\DcGeneral\Data\Model` kostet **drei** Attribut-Konvertierungen —

```php
$varInternalValue = $objAttribute->widgetToValue($varValue, $item->get('id'));   // 1
if ($varValue !== $this->getProperty($strPropertyName)) {                        // 2 → valueToWidget
    $item->set($strPropertyName, $varInternalValue);
    DifferentValuesException::compare($varValue, $this->getProperty(...), false); // 3 → valueToWidget
```

— bei übersetzten Modellen zuzüglich eines Sprachwechsels je Property.

## Warum der Neuaufbau überhaupt nötig ist

Jedes Widget muss die Eingaben **aller** Felder sehen, nicht nur die eigene:
Anzeigebedingungen und abhängige Auswahlfelder werden gegen die übrigen Properties
ausgewertet. Deshalb wird das Modell aus dem gesamten Wertebeutel aufgebaut und nicht aus
einem Einzelwert. Das ist richtig — falsch war nur, es **je Widget** zu tun.

## Die Änderung

Die Werte sind für alle Widgets eines Durchlaufs dieselben. Das Modell wird daher **einmal**
gebaut und zwischengespeichert (`modelWithInput()`); der Schlüssel deckt den Inhalt des
Beutels ab, ein geänderter Wert baut neu.

Jeder Aufrufer erhält weiterhin eine **eigene Instanz** — `cloneModel()` gibt einen Klon des
zwischengespeicherten Modells zurück. Das ist gefahrlos, weil `MetaModels\DcGeneral\Data\Model::__clone()`
das Item per `copy()` tief kopiert; ein Listener am `BuildWidgetEvent` kann den Cache also
nicht verunreinigen. `DefaultModel::__clone()` verwirft die Id absichtlich, deshalb setzt
`cloneModel()` sie wieder.

**Die Semantik bleibt unverändert:** Jedes Widget sieht denselben vollständig befüllten
Modellzustand wie zuvor.

## Messung

Speichern von `mm_employees::11` — 27 Widgets, unübersetztes Modell, Symfony-**dev**-Modus,
Xdebug **aus**. Zähler über temporäre Instrumentierung an den Konvertierungsstellen, Wandzeit
des POST über den Browser.

| | vorher | nachher | Faktor |
|---|---:|---:|---:|
| `valueToWidget` | 1.785 | **221** | 8,1 |
| `setProperty` | 1.349 | **99** | 13,6 |
| `widgetToValue` | 1.325 | **75** | 17,7 |
| `encodeValue` | 1.323 | **73** | 18,1 |
| `getWidget` | 76 | 76 | — |
| Wandzeit POST | ≈ 20.000 ms | **≈ 9.500 ms** | ≈ 2,1 |

Die Aufrufzahlen sind deterministisch und unabhängig von der Laufzeitumgebung; die Wandzeit
ist es nicht — im dev-Modus liegt sie höher als in Produktion. Für den Vergleich zählt, dass
beide Messungen unter denselben Bedingungen liefen.

> **Zur Belastbarkeit der Wandzeiten:** Sie stammen aus Läufen mit aktiver Instrumentierung,
> die selbst Zeit kostet. Ohne sie liegt derselbe Speichervorgang bei rund 4.000 ms Median.
> Die Wandzeit streut außerdem stark (siehe unten). Belastbar an dieser Tabelle sind die
> **Aufrufzahlen**; die Wandzeit taugt nur als Größenordnung.

Dass die Wandzeit „nur" um den Faktor 2 sinkt, während die Konvertierungen um Faktor 8 bis 18
fallen, heißt: Der Rest der Zeit steckt woanders — die 76 `getWidget`-Aufrufe für 27 Properties
deuten auf mehrfache Durchläufe der Maske (Validierung, Neuaufbau, Rendern der Antwort).

**Nebenbefund:** Xdebug war *nicht* die Ursache der Langsamkeit. Mit `xdebug.mode=debug` und
`start_with_request=yes` lagen die Zeiten bei 22 s, ohne Xdebug bei 18–23 s — kein
nennenswerter Unterschied. Die Vermutung, der fehlschlagende Debugger-Verbindungsaufbau koste
spürbar, hat sich nicht bestätigt.

## Verworfen: der Vergleich auf der Speicherform

Naheliegender zweiter Schritt in `MetaModels\DcGeneral\Data\Model::setProperty()`: nicht auf
der Widget-Form vergleichen, sondern auf der Speicherform. Der interne Wert liegt aus
`widgetToValue()` ohnehin schon vor, das spart ein `valueToWidget()` je Property:

```php
- if ($varValue !== $this->getProperty($strPropertyName)) {   // ruft valueToWidget()
+ if ($varInternalValue !== $item->get($strPropertyName)) {   // ruft nichts
```

Umgesetzt, gemessen — und **wieder zurückgenommen**. Zwei Gründe:

**1. Kein messbarer Gewinn.** Die Konvertierungen gingen zurück (`valueToWidget` 221 → 147,
−33 %), die Wandzeit nicht. Verschränkte A/B-Messung, sechs Speichervorgänge je Variante,
abwechselnd, ohne Cache-Löschen dazwischen:

| | Median | Min | Max |
|---|---:|---:|---:|
| ohne Änderung | 4.161 ms | 3.228 | 7.105 |
| mit Änderung | 4.426 ms | 3.025 | 5.849 |

Der Median liegt *mit* der Änderung 266 ms höher, die Bänder überlappen fast vollständig. Bei
dieser Streuung sind eingesparte 74 Konvertierungen schlicht nicht sichtbar — sie sind bei
diesen Attributen zu billig.

**2. Die Änderungserkennung wird schlechter.** Der interne Wert aus `widgetToValue()` und der
gespeicherte aus `$item->get()` sind **nicht dieselbe Struktur**. Der gespeicherte trägt bei
Tabellen-Attributen die DB-Metadaten mit (`id`, `tstamp`, `att_id`, `item_id`), die
`widgetToValue()` nie erzeugt — ein strikter Vergleich meldet dort *immer* „geändert".
Gemessen an einem Speichervorgang ohne jede Eingabeänderung:

| | als geändert markierte Properties |
|---|---:|
| ohne Änderung | 442 |
| mit Änderung | **655** (+48 %) |

`tabletext` etwa war vorher unauffällig und wird nachher bei jedem Speichern neu geschrieben.
Ein Umbau, der ohne messbaren Gewinn zusätzliche Schreibvorgänge erzeugt, ist keiner.

## Der eigentliche Hebel: die Änderungserkennung

Die Gegenprobe hat etwas Wichtigeres zutage gefördert: **Auch ohne jede Änderung meldet ein
Speichervorgang 442 „geänderte" Properties.** Die Erkennung ist schon im Ist-Zustand kaputt,
und zwar systematisch:

| Muster | Beispiel | alt → neu |
|---|---|---|
| `NULL` gegen Leerstring | `decimal`, `numeric`, `geo_lat`, `geo_long`, `death_year` | `NULL` → `''` |
| Zusatzschlüssel in der Speicherform | `file` | mit `*_sorted` → ohne |
| Entity-Kodierung nicht symmetrisch | `longtext` | `&amp;`/`&shy;` → `[&amp;]`/`[-]` |
| Teilstruktur statt Vollstruktur | `rating` | `votecount` + `meanvalue` → nur `meanvalue` |
| DB-Metadaten in der Speicherform | `tablemulti` | mit `id`/`tstamp` → ohne |
| Zeitstempel wandert bei jedem Lauf | `timestamp` | `1784842373` → `1784842432` |

Jede dieser Zeilen bedeutet: Das Attribut wird bei **jedem** Speichern neu geschrieben, obwohl
sich nichts geändert hat. Das kostet nicht eine Konvertierung, sondern einen kompletten
Schreibweg samt Folgearbeit. Hier liegt der Gewinn — nicht im Einsparen einzelner
`valueToWidget`-Aufrufe.

Das ist Arbeit in `metamodels/core` (je Attributtyp), nicht im dc-general, und sie ist nicht
nebenbei zu erledigen: Jede Korrektur muss belegen, dass sie echte Änderungen weiterhin
erkennt. Reproduzieren lässt sich der Befund mit `.playwrite/verify-noop-save.js` plus einer
temporären Ausgabe an der Stelle, an der `IS_CHANGED` gesetzt wird.

## Was offen bleibt

1. **Die Änderungserkennung je Attributtyp** — siehe oben. Der größte bekannte Hebel.
2. **Die 76 `getWidget`-Aufrufe.** Zu klären, welche Durchläufe das sind und ob sich einer
   davon einsparen lässt.
3. **Der Sprachwechsel je Property** bei übersetzten Modellen — noch nicht gemessen. Ein
   Benchmark an einem übersetzten Modell fehlt.
4. **Wo die restlichen ~4 Sekunden stecken**, ist unbekannt. Die Konvertierungen sind es nach
   Schritt 2 nachweislich nicht mehr. Der nächste Schritt wäre ein Profiler-Lauf statt weiterer
   Vermutungen.

## Messung wiederholen

`.playwrite/bench-save.js` misst die Wandzeit eines Speichervorgangs:

```bash
cd .playwrite
BENCH_URL='/contao/metamodel/mm_employees?act=edit&id=mm_employees::11' node bench-save.js
```

Für die Aufrufzahlen war eine temporäre Instrumentierung nötig (statische Zähler an
`Model::getProperty`/`setProperty` und an `getWidget`/`encodeValue`, Ausgabe per
`register_shutdown_function`). Sie ist nach der Messung wieder entfernt worden — für eine
Wiederholung muss sie neu gesetzt werden.

**Einzelmessungen taugen nicht.** Die Streuung liegt bei Faktor zwei; ein einzelner Lauf kann
jede beliebige These stützen. Wer zwei Stände vergleicht, sollte sie **verschränkt** messen —
abwechselnd A, B, A, B, mindestens sechs Läufe je Variante, die ersten zwei als Aufwärmläufe
verwerfen — und den Median vergleichen, nicht das Minimum.

Zwei Skripte prüfen die Korrektheit, die bei solchen Umbauten zuerst bricht:

| Skript | prüft |
|---|---|
| `verify-save-persists.js` | Eine echte Änderung wird auch wirklich in die Datenbank geschrieben. |
| `verify-noop-save.js` | Speichern ohne Änderung — Grundlage für die Zählung oben. |

**Beim Messen beachten:** `verify-dnd.js` sortiert `tl_metamodel_dcasetting` per Drag & Drop
um und verändert damit dauerhaft die Reihenfolge der Eingabemaske — nach einem Lauf steht die
Legende woanders. Wer Messreihen fährt, sollte das Skript aus der Runde nehmen.
