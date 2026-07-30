# Laufzeit der Eingabemaske

> Stand: **erster Schritt umgesetzt** — der quadratische Aufbau in
> `ContaoWidgetManager::getWidget()` ist beseitigt. Die Verdreifachung je `setProperty` in
> MetaModels ist erkannt, aber noch offen.

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

Dass die Wandzeit „nur" um den Faktor 2 sinkt, während die Konvertierungen um Faktor 8 bis 18
fallen, heißt: Der Rest der Zeit steckt woanders — die 76 `getWidget`-Aufrufe für 27 Properties
deuten auf mehrfache Durchläufe der Maske (Validierung, Neuaufbau, Rendern der Antwort).

**Nebenbefund:** Xdebug war *nicht* die Ursache der Langsamkeit. Mit `xdebug.mode=debug` und
`start_with_request=yes` lagen die Zeiten bei 22 s, ohne Xdebug bei 18–23 s — kein
nennenswerter Unterschied. Die Vermutung, der fehlschlagende Debugger-Verbindungsaufbau koste
spürbar, hat sich nicht bestätigt.

## Was offen bleibt

1. **Die drei Konvertierungen je `setProperty`** in MetaModels. Der Selbstvergleich
   (`$varValue !== $this->getProperty(...)`) und die anschließende `DifferentValuesException::compare`
   rufen beide `valueToWidget` auf. Ein einmaliges Zwischenspeichern des konvertierten Werts
   innerhalb von `setProperty` würde zwei davon sparen. Liegt in `metamodels/core`, nicht hier.
2. **Die 76 `getWidget`-Aufrufe.** Zu klären, welche Durchläufe das sind und ob sich einer
   davon einsparen lässt.
3. **Der Sprachwechsel je Property** bei übersetzten Modellen — noch nicht gemessen. Ein
   Benchmark an einem übersetzten Modell fehlt.

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

**Beim Messen beachten:** `verify-dnd.js` sortiert `tl_metamodel_dcasetting` per Drag & Drop
um und verändert damit dauerhaft die Reihenfolge der Eingabemaske — nach einem Lauf steht die
Legende woanders. Wer Messreihen fährt, sollte das Skript aus der Runde nehmen.
