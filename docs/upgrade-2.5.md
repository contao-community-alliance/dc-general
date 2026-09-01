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

**Check your tables that have no list view.** A table whose data provider only serves the
edit mode has no list to go back to - `TableRowsAsRecordsDataProvider` is the one shipped
here: it aggregates all rows of a parent into a single record and throws on `fetchAll()`.
With the session referer this rarely mattered, since it returned the page one came from. The
back url is derived from the request now and would otherwise point at that missing list.

The symptom is an exception on "save and close", not a warning:

```
TableRowsAsRecordsDataProvider::fetchAll not available,
as the data provider is intended for edit mode only.
```

**Own providers say so through `EditOnlyDataProviderInterface`.** Implementing it settles
both consumers: the back url leaves `table` and `pid` behind so that closing lands one level
up, and the list handler forwards to the edit action instead of fetching a collection. The
interface also answers which record aggregates a given parent, which the forward needs - the
list url of such a table carries only `pid`, and forwarding without an id opens an empty mask
that looks exactly like lost data.

The older `config/forceEdit` in the DCA keeps working and is honoured the same way. The
interface is the more reliable source though: a flag is easy to forget, while a provider that
cannot list knows it.

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
- `DcGeneral.get(url, options)` - returns the `fetch` promise; `options` is merged into the
  request, so a caller that must not follow the answer passes `{redirect: 'manual'}`
- `DcGeneral.setHtml(element, html)` - swaps markup **and** runs the scripts it brings along
- `DcGeneral.runScript(code)` - what `Browser.exec()` did

**Removed javascript API:**

- `GeneralAjaxCaller` together with `GeneralEnvironment.getAjax()` / `setAjax()`. Its
  `sendPost()` had no caller and was broken anyway - it passed the payload to
  `setRequestHeader()` instead of sending it as the body. Use `DcGeneral.post()`.
- `BackendGeneral.setLegendState()` - see below.
- `BackendGeneral.toggleVisibility()`. The visibility toggle is a plain link now, the way
  Contao renders its own toggle operation: the server flips the stored value and the list is
  rendered anew. Nothing swaps an icon any more, so the function, its icon name arithmetic,
  the dark mode special case and the branching over list, tree and parent view are gone -
  180 lines out of `generalDriver.js`, together with the helpers `siblingMatching()` and
  `firstChildMatching()` they left behind.

  This fixes a defect that could not be solved in the old model: rows inheriting the value -
  variants inherit `published` from their base record - kept showing the stale state until a
  reload, because only the clicked icon was swapped. Reproducing the server's inheritance
  rules in the browser was never realistic.

  Server side the change is additive. `ToggleHandler` still honours a `state` parameter and
  only flips the stored value when none is passed, so existing links keep working. It now
  redirects to the list once the new state is stored - the toggle action renders none itself,
  which was of no consequence while an ajax call threw the answer away.

  **This needs Turbo Drive**, or every toggle turns into a visible page load. See
  `docs/turbo.md`.

**Added:** `BackendGeneral.toggleWrap(id)`, because Contao 5 dropped `Backend.toggleWrap()`
without a replacement while the dc-general still offers the button.

**The markup of eleven templates changed.** This is the part that reaches beyond the
bundle: anyone overriding a dc-general template, or styling and scripting against its
output, works against a different contract now.

| gone from the markup | replaced by |
| --- | --- |
| `onclick="Backend.getScrollOffset()"`, `onfocus="…"` | `data-action="contao--scroll-offset#store"` (`focus->…` on inputs) |
| `onclick="Backend.toggleCheckboxes(this)"` | `data-controller="contao--check-all"` on the container, `#toggleAll` on the trigger, `#toggleInput` plus `data-contao--check-all-target="input"` on the rows |
| `class="click2edit"` on the row | `data-controller="contao--deeplink"` plus `data-contao--deeplink-target` (`primary` / `secondary`) on the operation links |
| `id="sbtog"` on the split button | `contao--toggle-sender` / `contao--toggle-receiver`, mirroring Contao's `backend/data_container/buttons.html.twig` |
| `class="picker_selector"` on the picker list | nothing — see `docs/mootools-removal.md` 6.1 for why no replacement was needed |
| the two `window.addEvent('domready', …)` blocks of `dcbe_general_edit` | `data-contao--scroll-offset-target="widgetError"`; the autofocus is Contao's own now |

Affected: `dcbe_general_common_list`, `dcbe_general_treeview`, `dcbe_general_treeview_entry`,
`dcbe_general_edit`, `dcbe_general_field`, `dcbe_general_show`, `dc_general_submit_button`,
`widget_filetree`, `widget_common_picker`, `widget_treepicker_entry`,
`widget_treepicker_popup`.

Custom CSS is only at risk for `#sbtog`, which was a real id in the document. `click2edit`
was never stable to style against — Contao's own controller strips the class while wiring
it up — and `picker_selector` carried no rules anywhere in Contao.

**Assets removed:** the two source maps (`js/generalDriver.js.map`,
`css/generalDriver.css.map`), the empty `sass/_languagePanel.scss` — a nought byte leftover,
the `.tl_language_panel` rules always lived in `generalDriver.css` — and `images/drag.gif`,
which had no user left. There is no build step and no sass source any more: the shipped css
and js **are** the source.

## New: pagination below listings

Paginated listings now carry a bar below the table showing "Page x of y" and the page numbers,
the way the Contao back end does. It applies to the list view, the parent view and the tree
view.

**The tree view changed twice.** It used to hide the limit element altogether and render every
node; the selector is visible there now and the tree is limited like any other listing. What is
counted are the **root nodes** only - with MetaModels the variant bases. Every base is rendered
with its complete subtree, so "Page 1 of 3" means "base 1-3 of 7", not "row 1-3 of 20".
Paginating over all nodes would cut parent-child relations in half. The expand state lives in
the session and survives browsing.

Should a limit element of your own be in play: the total is counted with the root condition
applied whenever the definition is in `MODE_HIERARCHICAL`, otherwise the amount of pages would
depend on how many children happen to hang below a node.

Nothing to do on your side. Two things are worth knowing if you build on top of dc-general:

- **`TotalAwareLimitElementInterface` is new.** A pagination needs to know how many records match
  the current filter, and the limit element is the one place where that number already exists.
  It is a separate interface rather than an addition to `LimitElementInterface` because that
  would break every implementation out there; the two are to be merged in 3.0.
  `DefaultLimitElement` implements it. A limit element that does not is simply skipped, the bar
  is then left out.
- **The url parameter `lp` is taken.** It carries the requested page and is turned into an offset
  by the limit element, which stores it like any other panel state. Submitting the panel wins
  over it, and the panel form action no longer carries it - a changed filter would otherwise
  jump right back to the page the user came from.

The calculation lives in `ListPagination`, plain arithmetic without dependencies. The window of
offered pages slides with the current page, exactly as Contao's does - verified against 72
combinations of total, page size, current page and window width. By default every page is
offered, which is what a back end listing wants; the window width is available as a parameter.

The pages are plain links, not form buttons. The panel state lives in the session, so a link
carries everything needed, the selection form is left alone and Turbo handles the navigation.

Overriding the look is done through `dcbe_general_pagination.html5`. The `tl_pagination` class it
uses comes from the back end theme, so light and dark mode are handled.

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
- **`FileTree::updateAjax()`** removed together with its `executePostActions` hook
  registration. It entered the legacy `Contao\FileSelector` path, and that class no longer
  exists in Contao 5, so the method fataled if invoked. The widget itself already uses the
  modern Contao picker (`PickerBuilderInterface::getUrl('file')`).
- **The `loadFiletree` and `loadPagetree` ajax actions** removed: the two implementations
  in `Ajax3X`, the abstract declarations in `Ajax` and both entries in the dispatch list of
  `Ajax::executePostActions()`. They instantiated `$GLOBALS['BE_FFL']['fileSelector']` and
  `['pageSelector']`, and Contao 5.7 registers neither — the widget classes are gone, so
  either action ended in a fatal rather than a deprecation. Measured against the devstack:
  both answered **HTTP 500** with a 740 kB error page before, both answer **HTTP 204** with
  an empty body now, because the request falls through to Contao's own ajax handler, which
  does not know the action either. Nothing had been requesting them in the first place.

  Subclasses are affected in one direction only: an implementation of the two former
  abstract methods keeps working (it is simply no longer called), while an override
  carrying `#[\Override]` has to drop the attribute.

  **`reloadFiletree` and `reloadPagetree` stay.** They look like part of the same legacy
  block but are not: both delegate to `Ajax3X::reloadTree()`, which builds its widget
  through the `ContaoWidgetManager` and never touches `$GLOBALS['BE_FFL']`. The bundle's
  own `widget_filetree` template requests `reloadFiletree`, and the MultiColumnWizard
  rewrites both onto its `*_mcw` variants.

## Services take their dependencies through the constructor

Five classes that are registered as services reached into the container at the point of
use. They are injected now, so the container is no longer touched on any active path.

**`WidgetBuilder` is the one that breaks.** Its listener method `handleEvent()` was
`static`, which is why it had no collaborators at hand: it fetched the translator from the
container and kept the scope determinator in a static property, assigned as a side effect
of the constructor so that the per-event instances could reach it.

| before | now |
| --- | --- |
| `public static function handleEvent(BuildWidgetEvent $event)` | `public function handleEvent(BuildWidgetEvent $event)` |
| `__construct($environment, $translator, ?RequestScopeDeterminator $scope = null)` | `__construct($environment, $translator, RequestScopeDeterminator $scope, RouterInterface $router)` |
| `private static $scopeDeterminator` | ordinary instance property |

Calling `WidgetBuilder::handleEvent()` statically or constructing the class with two
arguments no longer works. The class is annotated `final`, carries no `@api` marker and has
no user outside this bundle, which is why the argument became required rather than optional
— note that the `WidgetBuilder` in `metamodels/filter_by_related` is an unrelated class
that happens to share the name.

**The other four keep working unchanged.** `BackendPickerController` is `final readonly`
and only ever built by the container, so `kernel.debug` became a plain constructor
argument. `HardCodedPopulator`, `EditAllHandler` and `OverrideAllHandler` gained *optional*
arguments — the session factory, the edit information, the locales — because all three are
marked `@api` and consumers may construct them; for that case the container lookup remains
as a fallback. Anyone wiring these services in own configuration should pass the new
arguments, the shipped definitions already do.

What is deliberately left alone: the container calls in `PagePickerProvider` and
`BackendViewPopulator` are BC shims with their own `E_USER_DEPRECATED` announcing removal
in 3.0, and roughly fifty further calls sit in classes that are not services at all —
everything below `DC_General` is built with `new`, because Contao instantiates the data
container driver by class name.

## `doNotSaveEmpty` is honoured now (behaviour change)

`ModelManipulator::updateModelFromPropertyBag()` ignored the `doNotSaveEmpty` eval flag
entirely - only its counterpart `alwaysSave` was evaluated, which is the worse half of the
pair to get right: an empty value was not only written, it was also forced onto the model as
changed. It now mirrors Contao's own `DC_Table::save()`: a property flagged `doNotSaveEmpty`
keeps its stored value when an empty one arrives instead of being overwritten. An array is
never "empty" in this sense, not even an empty one, matching Contao's own check.

**This changes behaviour for existing DCAs, it is not just a fix.** A property carrying the
flag used to lose its value the moment an empty one was submitted; after this release it
keeps what was stored. Anyone who set `doNotSaveEmpty` and relied on the old, broken
behaviour to clear the property should remove the flag instead - that is what it always
claimed not to do (`contao-community-alliance/dc-general#385`).

## Fixed along the way

- **Drag and drop sorting** in list views answered with HTTP 500 and lost the new order.
  The request url was assembled from `window.location.search` plus a `"?"`, but the search
  part already carries that character, so it ended up inside the value of the last
  parameter and the server tried to load a data container named `"tl_x?"`.
- **The visibility toggle** no longer swaps icons on the client at all. It used to replace
  the image source of the clicked entry after the ajax call, which could only ever be right
  for that one entry — in a variant hierarchy the inherited rows kept their old icon until
  the page was reloaded, and the light and dark variant could drift apart. The toggle now
  follows Contao's model: a plain link, a server side redirect and a re-rendered listing. See
  section 9 of `docs/mootools-removal.md`. Anything that relied on `toggleVisibility()` or on
  the toggle answering with ajax has to follow suit.
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

## Performance

Nothing to do on your side — no signature changed and no behaviour differs. Listed because
the numbers are noticeable.

- **The edit mask is built once per pass instead of once per property.**
  `ContaoWidgetManager::getWidget()` rebuilt the whole model for every single widget, which
  made the cost quadratic in the number of properties. On a mask with 27 widgets the
  attribute conversions dropped from 1.785 to 221 calls per save.
- **`RequestScopeDeterminator` remembers the scope per request.** It asked Contao's
  `ScopeMatcher` on every call — roughly 6.000 times per save, for a question whose answer
  cannot change within a request. Down to 244 calls.

Measurements, method and the things that turned out **not** to be worth doing are in
`docs/performance-editmask.md`. Two results from there are worth knowing when someone reports
a slow back end:

- A save takes **746 ms with `APP_ENV=prod`** and **4.188 ms in dev** — check the environment
  before the code.
- `xdebug.mode=debug` together with `start_with_request=yes` costs **factor 2,6** on its own,
  because every request tries to reach a debugger.

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
