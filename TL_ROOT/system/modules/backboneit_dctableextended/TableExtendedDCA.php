<?php

class TableExtendedDCA extends Backend {
	
	protected $arrExcludes;
	
	public function isExtendedTable($strTable) {
		return !isset($this->arrExcludes[$strTable]);
	}
	
	public function getExclusionOptions() {
		$arrOptions = array();
		$arrTables = array_flip($this->Database->listTables());
		
		foreach($this->Config->getActiveModules() as $strModule)
			if(is_dir($strDir = TL_ROOT . '/system/modules/' . $strModule . '/dca'))
				foreach(scan($strDir) as $strTable)
					if(isset($arrTables[$strTable = substr($strTable, 0, -4)]))
						$arrOptions[$strTable][] = $strModule;
		
		foreach($arrOptions as $strTable => &$arrModules)
			$arrModules = $strTable . ' (' . implode(', ', $arrModules) . ')';
			
		ksort($arrOptions);
		
		return $arrOptions;
	}
	
	public function setup($strTable) {
		if(!$this->isExtendedTable($strTable))
			return;
			
		$strDC = &$GLOBALS['TL_DCA'][$strTable]['config']['dataContainer'];
		if($strDC == 'Table')
			$strDC = 'TableExtended';
	}

	public function fixPagePicker($strTable) {
		if($strTable != 'tl_content')
			return;
			
		foreach($GLOBALS['TL_DCA']['tl_content']['fields']['url']['wizard'] as &$arrCallback) {
			if($arrCallback[0] == 'tl_content' && $arrCallback[1] == 'pagePicker') {
				$arrCallback[0] = 'TableExtendedDCA';
				break;
			}
		}
	}
	
	public function pagePicker(DataContainer $dc) {
		$strField = 'ctrl_' . $dc->inputName;
		return ' ' . $this->generateImage('pickpage.gif', $GLOBALS['TL_LANG']['MSC']['pagepicker'], 'style="vertical-align:top; cursor:pointer;" onclick="Backend.pickPage(\'' . $strField . '\')"');
	}
	
	protected function __construct() {
		parent::__construct();
		$this->arrExcludes = array_flip(deserialize($GLOBALS['TL_CONFIG']['backboneit_dctableextended_excludes'], true));
	}
	
	private static $objInstance;
	
	public static function getInstance() {
		if(!self::$objInstance)
			self::$objInstance = new self();
		return self::$objInstance;
	}

}
