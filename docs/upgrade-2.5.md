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

## Removed (breaking)

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
