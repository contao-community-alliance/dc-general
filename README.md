DC_General
==========

Universal data container for Contao.


This data container originally was based upon the Contao DC_Table class as to
be found in Contao 2.10 which is (c) by Leo Feyer of the Contao project.

It was heavily adapted by Oliver Hoff to support more features.
Later on it became the first iteration of DC_General when the abstraction of
data providers, view and controller was introduced for releasing MetaModels.

The present version here is about to become version 1.0.0 and has since
undergone major refactoring, splitting up huge code bloats and introducing
even more abstraction and therefore may be considered a total rewrite.

Therefore do NOT contact Leo Feyer about problems, feature requests etc.

We hope that ultimatively this driver will become the de facto standard
driver for Contao extensions in the future, once proven to be stable enough.

How to use
==========

Simply declare an DCA as usual but put "General" instead of "Table" for the
'config/dataContainer' part.
This will make DC_General to be used in (almost) backwards compatible mode.

There are some notable changes in compatibility considering DC_Table:
1. DC_General does NOT support magic properties but provides setter and getter
   for almost anything you might desire.
2. There is NO activeRecord available as DC_General uses it's own kind of data
   Models internally. To retrieve the current model call getCurrentModel().

You can alter the default behaviour by defining own classes for the controller,
data provider and view.
