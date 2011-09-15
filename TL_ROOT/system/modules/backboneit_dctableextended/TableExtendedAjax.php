<?php

class TableExtendedAjax extends Backend {
	
	private static $arrMethods = array(
		'edit' => true,
		'editAll' => true
	);
	
	public function executePostActions($strAction, $objDC) {
		if($strAction != 'toggleSubpaletteExtended'
		|| !$objDC instanceof DC_TableExtended)
			return;
		
//		$strMethod = $this->Input->get('act');
//		if(!isset(self::$arrMethods[$strMethod]))
//			exit;
		$strMethod = $this->Input->get('act') == 'editAll' ? 'editAll' : 'edit';
		
		$strSelector = $this->Input->post('FORM_INPUTS');
		$strSelector = reset($strSelector);
		
		$intPos = strrpos($strSelector, '_');
		$intID = intval(substr($strSelector, $intPos + 1));
		$strSelector = substr($strSelector, 0, $intPos);
		
		$strReturn = $objDC->$strMethod($intID, $strSelector);
		
		echo version_compare(VERSION, '2.10', '<')
			? $strReturn
			: json_encode(array('content' => $strReturn, 'token' => REQUEST_TOKEN));
				
		exit;
	}

	protected function __construct() {
		parent::__construct();
	}
	
	private static $objInstance;
	
	public static function getInstance() {
		if(!self::$objInstance)
			self::$objInstance = new self();
		return self::$objInstance;
	}

}
