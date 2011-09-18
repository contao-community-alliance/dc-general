<?php

class TableExtendedAjax extends Backend {
	
	public function executePostActions($strAction, $objDC) {
		if($strAction != 'toggleSubpaletteExtended'
		|| !$objDC instanceof DC_TableExtended)
			return;
		
		$strMethod = $this->Input->get('act') == 'editAll' ? 'editAll' : 'edit';
		
		$strSelector = $this->Input->post('FORM_INPUTS');
		$strSelector = reset($strSelector);
		
		$intPos = strrpos($strSelector, '::');
		$intID = substr($strSelector, $intPos + 2);
		if(!is_numeric($intID)) $intID = base64_decode(str_replace('_', '=', substr($intID, 1)));
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
