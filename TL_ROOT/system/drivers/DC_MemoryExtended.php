<?php

require_once 'DC_TableExtended.php';

class DC_MemoryExtended extends DC_TableExtended {
	
	public function __construct($strTable, array $arrDCA = null) {
		$this->strTable = $strTable;
		$this->arrDCA = $arrDCA ? $arrDCA : $GLOBALS['TL_DCA'][$strTable];
		 
		// Check whether the table is defined
		if(!strlen($this->strTable) || !count($this->arrDCA)) {
			$this->log('Could not load data container configuration for "' . $strTable . '"', 'DC_Table __construct()', TL_ERROR);
			trigger_error('Could not load data container configuration', E_USER_ERROR);
		}
		
		$this->import('Input');
		
		$this->intId = $this->Input->get('id');
		$this->ptable = $this->arrDCA['config']['ptable'];
		$this->ctable = $this->arrDCA['config']['ctable'];
//		$this->treeView = in_array($this->arrDCA['list']['sorting']['mode'], array(5, 6)); // not used?
//		$this->root = null; // not used

		$this->initialize();
	
		$this->executeCallbacks($this->arrDCA['config']['onload_callback'], $this);
		
		if(!empty($this->ctable)
		&& !$this->Input->get('act')
		&& !$this->Input->get('key')
		&& !$this->Input->get('token')) {
			$arrSession = $this->Session->get('referer');
			$arrSession[$this->strTable] = $this->Environment->requestUri;
			$this->Session->set('referer', $arrSession);
		}
	}
	
	protected function updateTimestamp() {
	}
	
	protected function isUniqueValue($varNew) {
		return true;
	}
	
	protected function resetFallback() {
	}
	
	protected function storeValue($varNew) {
		return true;
	}
	
	protected function loadActiveRecord($blnDontUseCache = false) {
		$arrCB = $this->arrDCA['config']['loadActiveRecord'];
		if(!$arrCB) {
			$this->objActiveRecord = new stdClass();
			return $this->objActiveRecord;
		}
		
		$this->import($arrCB[0]);
		$this->objActiveRecord = (object) $this->{$arrCB[0]}->{$arrCB[1]}($this, $blnDontUseCache);
		
		if(!is_array($this->objActiveRecord)) {
			$this->log('Could not load record ID "' . $this->intId . '" of table "' . $this->strTable . '"!', 'DC_TableExtended::loadActiveRecord()', TL_ERROR);
			$this->redirect('contao/main.php?act=error');
		}
		
		return $this->objActiveRecord;
	}
	
	protected function setVersion($strVersion) {
		$arrCB = $this->arrDCA['config']['setVersion'];
		if(!$arrCB)
			return;
			
		$this->import($arrCB[0]);
		$this->{$arrCB[0]}->{$arrCB[1]}($this, $strVersion);
	}
	
	protected function getVersions() {
		$arrCB = $this->arrDCA['config']['getVersions'];
		if(!$arrCB)
			return;
			
		$this->import($arrCB[0]);
		return $this->{$arrCB[0]}->{$arrCB[1]}($this);
	}
	
}
