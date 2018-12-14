[![Build Status](https://travis-ci.org/contao-community-alliance/dc-general.png)](https://travis-ci.org/contao-community-alliance/dc-general)
[![Latest Version tagged](http://img.shields.io/github/tag/contao-community-alliance/dc-general.svg)](https://github.com/contao-community-alliance/dc-general/tags)
[![Latest Version on Packagist](http://img.shields.io/packagist/v/contao-community-alliance/dc-general.svg)](https://packagist.org/packages/contao-community-alliance/dc-general)
[![Installations via composer per month](http://img.shields.io/packagist/dm/contao-community-alliance/dc-general.svg)](https://packagist.org/packages/contao-community-alliance/dc-general)

DC_General
==========

Universal data container for Contao.

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

