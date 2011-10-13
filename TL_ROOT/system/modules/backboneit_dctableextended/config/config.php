<?php

$GLOBALS['TL_CONFIG']['backboneit_dctableextended_excludes'] = array('tl_layout');

$GLOBALS['TL_HOOKS']['loadDataContainer'][] = array('TableExtendedDCA', 'setup');
$GLOBALS['TL_HOOKS']['executePostActions'][] = array('MemoryExtendedAjax', 'hookExecutePostActions');

if(version_compare(VERSION, '2.10', '<')) {
	$GLOBALS['TL_HOOKS']['loadDataContainer'][] = array('TableExtendedDCA', 'fixPagePicker');
}
