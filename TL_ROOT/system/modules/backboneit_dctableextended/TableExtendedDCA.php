<?php

class TableExtendedDCA extends Backend {
	
	protected $arrExcludes;

	protected function __construct() {
		parent::__construct();
		$this->arrExcludes = array_flip(deserialize($GLOBALS['TL_CONFIG']['backboneit_dctableextended_excludes'], true));
	}
	
	public function isExtendedTable($strTable) {
		return !isset($this->arrExcludes[$strTable]);
	}
	
	public function setup($strTable) {
		$strDC = &$GLOBALS['TL_DCA'][$strTable]['config']['dataContainer'];
		if($strDC == 'Table' && $this->isExtendedTable($strTable))
			$strDC = 'TableExtended';
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
	
	private static $objInstance;
	
	public static function getInstance() {
		if(!self::$objInstance)
			self::$objInstance = new self();
		return self::$objInstance;
	}

}
