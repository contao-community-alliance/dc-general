<?php

class MemoryExtendedButtons extends Backend {

	protected function __construct() {
		parent::__construct();
	}

	public function save(DC_MemoryExtended $objDC) {
		$this->reload();
	}
	
	public function saveAndClose(DC_MemoryExtended $objDC) {
		setcookie('BE_PAGE_OFFSET', 0, 0, '/');

		$_SESSION['TL_INFO'] = '';
		$_SESSION['TL_ERROR'] = '';
		$_SESSION['TL_CONFIRM'] = '';
			
		$this->redirect($this->getReferer());
	}
	
	private static $objInstance;
	
	public static function getInstance() {
		if(!self::$objInstance)
			self::$objInstance = new self();
		return self::$objInstance;
	}

}
