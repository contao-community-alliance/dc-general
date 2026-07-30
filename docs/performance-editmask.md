# Laufzeit der Eingabemaske

> Stand: **erster Schritt umgesetzt** — der quadratische Aufbau in
> `ContaoWidgetManager::getWidget()` ist beseitigt. Der zweite Schritt (die Konvertierungen je
> `setProperty` in MetaModels) wurde **versucht und wieder verworfen**, siehe
> [Verworfen: der Vergleich auf der Speicherform](#verworfen-der-vergleich-auf-der-speicherform).
>
> **Danach gemessen statt geraten** — und das Ergebnis stellt alles Weitere infrage:
> [Unser Code macht 5,6 % der Laufzeit aus](#wo-die-zeit-wirklich-hingeht), der Rest ist
> Entwicklungs-Infrastruktur. Wer hier weiterarbeitet, sollte **zuerst im prod-Modus messen**.

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

> **Korrektur.** An dieser Stelle stand zunächst der Nebenbefund, Xdebug sei *nicht* die
> Ursache der Langsamkeit — gemessen an 22 s mit gegen 18–23 s ohne. **Diese Messung war
> ungültig.** Sie hat zweimal dieselbe Konfiguration verglichen: Das Ändern der
> `xdebug.ini` allein wirkt nicht, php-fpm muss neu geladen werden, und der dafür verwendete
> Befehl traf den falschen Prozess (siehe
> [php-fpm neu laden](#php-fpm-neu-laden-sonst-misst-man-nichts)). Xdebug lief also in beiden
> Läufen mit. Der korrekt gemessene Wert steht unten: **Faktor 2,6**.

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

## Wo die Zeit wirklich hingeht

Statt weiter zu raten: ein Profillauf. Zuerst der Symfony-Profiler, der immer schon mitläuft
und die wichtigste Frage in einem Blick beantwortet — **Datenbank oder Rechenzeit?**

| Speichervorgang (Profiler-Token `4c7634`) | |
|---|---:|
| Gesamtzeit | 2.817 ms |
| davon Datenbank | **332 ms (12 %)** |
| Abfragen | 550 (103 verschiedene) |
| Spitzenspeicher | 42 MiB |

**Rechenzeit, nicht Datenbank.** Damit ist auch die Obergrenze für die oben beschriebene
Änderungserkennung bekannt: Selbst wenn *jede* überflüssige Schreiboperation verschwände,
wären höchstens 332 ms zu holen — nicht die Sekunden, die der Vorgang tatsächlich braucht.

Dann Xdebug im Profilmodus (`xdebug.mode=profile`), ausgewertet nach **Eigenzeit** je
Herkunft:

| Herkunft | Eigenzeit | Anteil |
|---|---:|---:|
| Symfony (übrige) | 6.391 ms | 23,1 % |
| Debug-EventDispatcher *(nur dev)* | 4.884 ms | 17,7 % |
| Logging / Monolog | 4.611 ms | 16,7 % |
| PHP-intern / Aufwärmen | 2.846 ms | 10,3 % |
| PhpParser | 2.547 ms | 9,2 % |
| Profiler / VarDumper *(nur dev)* | 2.078 ms | 7,5 % |
| Contao-Core | 1.434 ms | 5,2 % |
| Doctrine | 1.061 ms | 3,8 % |
| **MetaModels** | **959 ms** | **3,5 %** |
| **dc-general** | **570 ms** | **2,1 %** |

**Unser Code macht 5,6 % aus.** Über 40 % sind Entwicklungs-Infrastruktur, die in Produktion
gar nicht läuft. Auffällig sind **14.320 Log-Einträge pro Speichervorgang**; sie erklären auch
einen großen Teil der Symfony-Zeile (`Request::getUri()` u. ä. wird 14.215-mal aufgerufen,
einmal je Log-Eintrag durch den Request-Processor).

Einschränkung: Xdebug instrumentiert jeden Funktionsaufruf und überzeichnet daher Code mit
vielen kleinen Aufrufen — also gerade die Instrumentierung selbst. Ihr Anteil ist eher zu hoch
angesetzt. Am Verhältnis ändert das nichts: Es ist zu deutlich, um am Ergebnis zu rütteln.

### Xdebug kostet Faktor 2,6

Verschränkt gemessen, vier Speichervorgänge je Einstellung, mit korrektem php-fpm-Reload
dazwischen:

| `xdebug.mode` | Median | Min | Max |
|---|---:|---:|---:|
| `off` | **4.188 ms** | 4.147 | 5.932 |
| `debug` | **10.902 ms** | 10.093 | 11.358 |

`start_with_request=yes` versucht bei *jeder* Anfrage eine Verbindung zum Debugger. Wer nicht
gerade debuggt, sollte `xdebug.mode=off` setzen — das ist die mit Abstand größte Ersparnis in
dieser Umgebung und kostet keine Zeile Code.

### php-fpm neu laden, sonst misst man nichts

Eine geänderte `xdebug.ini` wirkt **nur nach einem Reload von php-fpm** — die PHP-CLI liest
sie sofort neu und täuscht Erfolg vor. Im Devstack ist php-fpm **PID 7**; PID 1 ist
`entrypoint.sh`:

```bash
docker exec -u root <container> kill -USR2 7
```

Kontrolle immer über die Webseite, nie über die CLI: `/_profiler/phpinfo` zeigt, was der
Webserver tatsächlich geladen hat.

## Was offen bleibt

1. **Ein Profillauf im prod-Modus.** Alles oben ist im dev-Modus gemessen, wo über 40 % der
   Zeit auf Werkzeuge entfallen, die in Produktion fehlen. Erst prod zeigt die echte
   Verteilung der Anwendungslast — vorher ist jede weitere Optimierung Raten.
2. **Die 14.320 Log-Einträge je Speichervorgang.** Auch in Produktion nicht umsonst, und die
   Zahl allein ist ein Geruch.
3. **Die Änderungserkennung je Attributtyp** — begrenzt durch die 332 ms Datenbankzeit.
4. **Die 76 `getWidget`-Aufrufe.** Zu klären, welche Durchläufe das sind und ob sich einer
   davon einsparen lässt.
5. **Der Sprachwechsel je Property** bei übersetzten Modellen — noch nicht gemessen.

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
