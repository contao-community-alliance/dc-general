# Turbo Drive und DC_General

> Status: **Turbo ist für Navigation aktiv, die Formulare dieses Bundles sind ausgenommen.**
> Der Ausschluss sitzt als `data-turbo="false"` an den sieben `<form>`-Elementen der Templates,
> nicht mehr pauschal am `<body>`.

## Warum die Formulare ausgenommen sind

Turbo Drive rendert die Antwort auf eine Formularabsendung **nur, wenn sie eine Weiterleitung
ist**. Eine 200-Antwort mit HTML verwirft es stillschweigend; gerendert wird eine 200 nur,
wenn der Status einen Fehler anzeigt.

DC_General baut auf dem Gegenteil auf. Der Auto-Submit — ausgelöst von `submitOnChange`, im
Markup als `onclick="BackendGeneral.autoSubmit('<table>')"`, in `generalDriver.js` über
`form.requestSubmit()` — schickt die Eingaben, lässt die Maske **mit diesen Werten** neu
rendern und speichert dabei ausdrücklich nicht (`SUBMIT_TYPE=auto`). Es gibt also kein Ziel,
auf das weitergeleitet werden könnte: Der Zustand steckt allein in der Antwort.

Gemessen am Devstack, Absendung von Hand nachgestellt:

```
status: 200   redirected: false   Content-Type: text/html
enthaeltLongtext: false   ← die neu gerenderte Maske lässt das Widget korrekt weg
```

Der Server arbeitet korrekt, Turbo wirft die Antwort weg. Ohne den Ausschluss reagieren
**Anzeigebedingungen stillschweigend nicht mehr** — kein Konsolenfehler, das Widget bleibt
einfach stehen. Reproduziert über sechs Läufe: mit Turbo 4/5, ohne 5/5.

## Was der Ausschluss nicht betrifft

Links. Turbo übernimmt die Navigation im MetaModels-Backend unverändert, samt
`contao--scroll-offset`, das auf `turbo:render` die Scrollposition wiederherstellt.

Vollständig gegen aktives Turbo geprüft: Listen- und Baumansicht, Auf- und Zuklappen
(Baum wie Legenden), Sprungnavigation, Sichtbarkeits-Schalter, Drag-&-Drop-Sortierung,
Auswahlmodus mit „Alle bearbeiten"/„Alle überschreiben", Baum-Picker im Popup, Split-Button,
Message-Box, Back-URL und die Anzeigebedingungen.

## Der Weg zurück zu Turbo

`data-turbo="false"` ist eine Grenze, keine Lösung. Idiomatisch wäre eine **Turbo-Stream**-
Antwort: Kommt die Anfrage mit `Accept: text/vnd.turbo-stream.html`, liefert der Auto-Submit
statt der vollen Seite ein `<turbo-stream action="replace" target="…">` mit der neu
gerenderten Maske. Streams rendert Turbo auch bei 200 ohne Weiterleitung — die
„nicht speichern"-Semantik bliebe erhalten, und es würde nur der betroffene Teil getauscht.

Das ist eine neue Fähigkeit im `EditMask`-Pfad und will eigenständig getestet werden, weil an
`SUBMIT_TYPE=auto` alle Anzeigebedingungen und abhängigen Auswahlfelder hängen. Bis dahin
bleibt der Ausschluss.

**Nicht empfohlen:** `form.submit()` statt `form.requestSubmit()` in `autoSubmit()`. Das
funktioniert — `submit()` löst kein `submit`-Ereignis aus, Turbo greift also nicht ein, im
Test 5/5 — umgeht Turbo aber über einen Nebeneffekt und dazu Validierung und
Ereignis-Handler. Beim nächsten Blick auf die Zeile weiß niemand mehr, warum dort nicht
`requestSubmit()` steht.

## Fallstricke beim Testen

Zwei, die Zeit gekostet haben:

> **Nie mit festen Wartezeiten prüfen.** Der Unterbaum-Request antwortet mit rund 9,5 kB, die
> der Client anschließend einbaut; eine feste Wartezeit liest den Zustand des vorigen Klicks.
> Das erzeugte einen scheinbaren Turbo-Fehler beim Baum, der keiner war — auf `waitForResponse`
> umgestellt, seither stabil.

> **Reihenfolge der Läufe beachten.** Ein Prüfskript, das den Sitzungszustand verändert und
> nicht zurücksetzt, lässt den nächsten Lauf scheitern. Ein A/B-Vergleich aus zwei
> aufeinanderfolgenden Läufen misst dann die Reihenfolge, nicht die Einstellung.
