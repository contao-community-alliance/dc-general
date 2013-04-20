<?php

/**
 * PHP version 5
 * @package	   generalDriver
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

/**
 * Hooks 
 */
$GLOBALS['TL_HOOKS']['executePostActions'][] = array('GeneralAjax', 'hookExecutePostActions');

/**
 * JS
 */
if(TL_MODE == 'BE')
{
	$GLOBALS['TL_JAVASCRIPT'][] = 'system/modules/generalDriver/html/js/generalDriver.js';
}