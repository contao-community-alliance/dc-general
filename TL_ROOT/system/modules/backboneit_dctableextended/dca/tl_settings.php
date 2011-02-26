<?php

$GLOBALS['TL_DCA']['tl_settings']['palettes']['default']
	.= ";{backboneit_dctableextended_legend},backboneit_dctableextended_excludes";

$GLOBALS['TL_DCA']['tl_settings']['fields']['backboneit_dctableextended_excludes'] = array(
	'label'			=> &$GLOBALS['TL_LANG']['tl_settings']['backboneit_dctableextended_excludes'],
	'exclude'		=> true,
	'inputType'		=> 'checkbox',
	'options_callback' => array('TableExtendedDCA', 'getExclusionOptions'),
	'eval'			=> array('multiple'=>true)
);
