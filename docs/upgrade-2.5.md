# Upgrade to dc-general 2.5

> Scope: this covers the **Contao 5.7 support and legacy-cleanup** changes only.
> Fold it into the complete 2.4 → 2.5 release notes as the breaking/BC block.

## Requirements

- Now requires **Contao `^5.7`** and **PHP `^8.4`**.

## Back-end referer handling reworked (Contao 5.7)

Contao 5.7 no longer maintains the session-based referer (`System::getReferer()`
now derives the back URL from `DcaUrlAnalyzer`, which cannot resolve dc-general's
custom data providers). Back navigation — the "back" buttons and the
"save and close" redirects — is now built **deterministically from the current
request** via the new `ViewHelpers::getBackUrl()`.

The url is derived from the request, so the target follows from the parameters rather than
from where one came: `act`, `id` and `rt` are stripped, and **without an action `table` and
`pid` go as well**. Coming from an edit mask the back link therefore leads to the list those
two describe, while on that list itself it leads one level up to the parent list. The old
session referer knew the actual history and needed no such distinction — anyone relying on
`getBackUrl()` in own code should be aware of the difference.

## Back-end javascript reworked (MooTools removal)

The scripts no longer use the MooTools library; see `docs/mootools-removal.md` for the
whole picture. What matters for consumers:

**The files were renamed.** Anyone loading them by path or overriding them has to adjust:

| before | now |
| --- | --- |
| `js/dcGeneralAjax.js` | `js/generalAjax.js` |
| `js/vanillaGeneral.js` | `js/generalBase.js` |
| `js/generalDriver_src.js` | `js/generalDriver.js` |

The `_src` suffix is gone for good - there is no build step, the shipped file is the source.
`generalAjax.js` is registered first now, the others build on it.

**New javascript API** in `generalAjax.js`, replacing the MooTools `Request.Contao`:

- `DcGeneral.post(url, data)` - returns a promise resolving to `{content, javascript}`
- `DcGeneral.get(url)` - fire and forget
- `DcGeneral.setHtml(element, html)` - swaps markup **and** runs the scripts it brings along
- `DcGeneral.runScript(code)` - what `Browser.exec()` did

**Removed javascript API:**

- `GeneralAjaxCaller` together with `GeneralEnvironment.getAjax()` / `setAjax()`. Its
  `sendPost()` had no caller and was broken anyway - it passed the payload to
  `setRequestHeader()` instead of sending it as the body. Use `DcGeneral.post()`.
- `BackendGeneral.setLegendState()` - see below.

**Added:** `BackendGeneral.toggleWrap(id)`, because Contao 5 dropped `Backend.toggleWrap()`
without a replacement while the dc-general still offers the button.

## Removed (breaking)

- **The `setLegendState` chain** - the javascript function, the ajax action, the abstract
  `Ajax::setLegendState()`, its implementation in `Ajax3X`, the reader
  `EditMask::getLegendStates()` and the `LEGENDS` session key. The chain was broken at every
  link: nothing emitted an `onclick` for the function, so the session key was never written
  and the legends always followed the palette definition alone. Contao 5.7 folds its
  fieldsets through its own `toggle-fieldset` controller. Nothing changes in the rendering.
- **`saveNback` button** ("Save and go back") removed from the edit mask and the
  edit-all / override view, following Contao core, which removed the same button
  in 5.7.0. "Save and close" covers the single-edit case; in edit-all the
  remaining "Save" button now applies **and** returns to the list.
- **`StoreRefererListener`** removed — it wrote a session structure that the
  Contao 5.7 core no longer reads.
- **`TreeSelect`** and **`FileSelect`** classes removed — unusable since
  Contao 5.0 (they relied on `BackendUser::authenticate()`, removed in Contao 5.0).
- **`FileTree::updateAjax()`** (the legacy `loadFiletree` / `Contao\FileSelector`
  path) removed. `Contao\FileSelector` no longer exists in Contao 5, so the path
  fataled if invoked. The widget already uses the modern Contao picker
  (`PickerBuilderInterface::getUrl('file')`).

## Fixed along the way

- **Drag and drop sorting** in list views answered with HTTP 500 and lost the new order.
  The request url was assembled from `window.location.search` plus a `"?"`, but the search
  part already carries that character, so it ended up inside the value of the last
  parameter and the server tried to load a data container named `"tl_x?"`.
- **The visibility toggle** swapped only the light icon; the dark one kept the previous
  state until the page was reloaded. Both variants are always in the markup, so the file
  name of the dark one must not be derived from the active color scheme.
- **Two `Backend` APIs that Contao 5 no longer ships** were still called and therefore
  threw rather than warned: `Backend.toggleWrap()` (now `BackendGeneral.toggleWrap()`) and
  `Backend.openWindow()` of the help wizard, which follows Contao and uses
  `Backend.openModalIframe()` now. The help url is built from the `contao_backend_help`
  route instead of a hard coded `/contao/help`.
- **`hideMessage()`** called `remove()` outside its null checks and threw when no message
  box was open.
- **Legacy callback notation in the DCA builder:** the callback assertion only let real PHP
  callables pass, so a data container using the classic `['tl_page', 'adjustDca']` notation
  aborted with an `InvalidArgumentException`. That notation references a non static method
  and is not callable until the class has been instantiated, which the callback listeners do
  at invocation time. It is accepted again.

## Deprecations

- dc-general no longer uses `GetReferrerEvent` internally for its own navigation.
  The event itself remains available in `contao-community-alliance/events-contao-bindings`.
- Added the missing `E_USER_DEPRECATED` warnings to the deprecated Controller
  relationship methods (`isRootModel` / `setRootModel` / `setParent` /
  `setSameParent`, which delegate to `RelationshipManager`).
- Normalised `DefaultConfig::getIds()` / `setIds()` from `E_USER_NOTICE` to
  `E_USER_DEPRECATED`.

No public API scheduled for removal in 3.0 was removed in this release — those
deprecations stay for the 3.0 major (see `docs/3.0-cleanup-tasks.md`).

## Downstream note

The `_dcg_referer_update` route flag is now a no-op (it was only read by the
removed `StoreRefererListener`) and was removed from the MetaModels routes.
Consumers referencing the removed classes or the flag should adjust accordingly.
