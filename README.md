DC_General
==========

Universal data container for Contao.

The present version here is about to become version 2.0.0 and has since
undergone major refactoring, splitting up huge code bloats and introducing
even more abstraction and therefore may be considered a total rewrite.

We hope that ultimatively this driver will become the de facto standard
driver for Contao extensions in the future, once proven to be stable enough.

How to use
==========

Simply declare an DCA as usual but put "General" instead of "Table" for the
'config/dataContainer' part.
This will make DC_General to be used.

There are some notable changes in compatibility considering DC_Table:
1. DC_General does NOT support magic properties but provides setter and getter
   for almost anything you might desire.
2. There is NO activeRecord available as DC_General uses it's own kind of data
   Models internally.
3. The system is totally event driven and relevant information (like the model
   in scope) is attached to the events.

