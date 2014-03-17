<?php
/**
 * PHP version 5
 * @package    generalDriver
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Tristan Lins <tristan.lins@bit3.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

// FIXME: we can not do the deprecated notice here as the only way for Contao is to load the class from root namespace.
/**
 * This is the only entry point for Contao to load the DC class.
 *
 * @deprecated
 */
class DC_General extends ContaoCommunityAlliance\DcGeneral\DC_General
{
}
