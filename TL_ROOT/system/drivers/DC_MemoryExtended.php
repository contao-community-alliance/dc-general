<?php

require_once 'DC_TableExtended.php';

class DC_MemoryExtended extends DC_TableExtended {
	
	public function __construct($strTable) {
		$this->initialize();
	
		$this->intId = $this->Input->get('id');
		
		// Check whether the table is defined
		if(!strlen($strTable) || !count($GLOBALS['TL_DCA'][$strTable])) {
			$this->log('Could not load data container configuration for "' . $strTable . '"', 'DC_Table __construct()', TL_ERROR);
			trigger_error('Could not load data container configuration', E_USER_ERROR);
		}
	}
	
}
