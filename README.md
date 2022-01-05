[![Build Status](https://github.com/contao-community-alliance/dc-general/actions/workflows/diagnostics.yml/badge.svg)](https://github.com/contao-community-alliance/dc-general/actions)
[![Latest Version tagged](http://img.shields.io/github/tag/contao-community-alliance/dc-general.svg)](https://github.com/contao-community-alliance/dc-general/tags)
[![Latest Version on Packagist](http://img.shields.io/packagist/v/contao-community-alliance/dc-general.svg)](https://packagist.org/packages/contao-community-alliance/dc-general)
[![Installations via composer per month](http://img.shields.io/packagist/dm/contao-community-alliance/dc-general.svg)](https://packagist.org/packages/contao-community-alliance/dc-general)


DC_General
==========

The DC_General is a universal data container for Contao and is an alternative
for the DC_Table of the Contao framework.

With the DC_General we facilitate programming with excellent functions and
influence possibilities.


Different to Contao DC_Table
============================

With the use of the DC_General there are many advantages, e.g.

* Object-oriented data container definitions
* Event driven
* abstraction of the data source
* modular design
* verification of data - no invalid records
* improved configuration of dependencies between data containers
* more control through events


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


Help for the start
==================

You can start with our new [documentation](https://dc-general.readthedocs.io/de/latest/index.html)
(currently in german) or [older one](http://contao-community-alliance.github.io/dc-general-docs/) (in english). 

We have an overview of [DCA mapping](https://dc-general.readthedocs.io/de/latest/reference/dca_mapping.html)
and [Callbacks](https://dc-general.readthedocs.io/de/latest/reference/callbacks.html).

In our [examples](https://github.com/contao-community-alliance/dc-general-example)
you can see the difference to DC_Table.
