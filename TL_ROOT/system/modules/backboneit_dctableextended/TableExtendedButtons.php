<?php

class TableExtendedButtons extends Backend {

	protected function __construct() {
		parent::__construct();
	}

	public function saveAndEdit(DC_TableExtended $objDC) {
		setcookie('BE_PAGE_OFFSET', 0, 0, '/');

		$_SESSION['TL_INFO'] = '';
		$_SESSION['TL_ERROR'] = '';
		$_SESSION['TL_CONFIRM'] = '';
		
		$strUrl = $this->addToUrl($objDC->dca['list']['operations']['edit']['href']);

		$strUrl = preg_replace('/(&amp;)?s2e=[^&]*/i', '', $strUrl);
		$strUrl = preg_replace('/(&amp;)?act=[^&]*/i', '', $strUrl);

		$this->redirect($strUrl);
	}
	
	public function saveAndBack(DC_TableExtended $objDC) {
		setcookie('BE_PAGE_OFFSET', 0, 0, '/');

		$_SESSION['TL_INFO'] = '';
		$_SESSION['TL_ERROR'] = '';
		$_SESSION['TL_CONFIRM'] = '';
		
		if($objDC->parentTable == '') {
			$this->redirect($this->Environment->script . '?do=' . $this->Input->get('do'));
		
		} elseif(($objDC->parentTable == 'tl_theme' && $objDC->table == 'tl_style_sheet')
		|| ($objDC->parentTable == 'tl_page' && $objDC->table == 'tl_article')) { // TODO: try to abstract this
			$this->redirect($this->getReferer(false, $objDC->table));
		
		} else {
			$this->redirect($this->getReferer(false, $objDC->parentTable));
		}
	}
	
	public function saveAndCreate(DC_TableExtended $objDC) {
		setcookie('BE_PAGE_OFFSET', 0, 0, '/');

		$_SESSION['TL_INFO'] = '';
		$_SESSION['TL_ERROR'] = '';
		$_SESSION['TL_CONFIRM'] = '';
		
		$strUrl = $this->Environment->script . '?do=' . $this->Input->get('do');

		if(isset($_GET['table']))
			$strUrl .= '&amp;table=' . $this->Input->get('table');

		if($objDC->treeView) {
			$strUrl .= '&amp;act=create&amp;mode=1&amp;pid=' . $objDC->id;

		} elseif($objDC->dca['list']['sorting']['mode'] == 4) { // Parent view
			$strUrl .= $this->Database->fieldExists('sorting', $objDC->table)
				? '&amp;act=create&amp;mode=1&amp;pid=' . $objDC->id . '&amp;id=' . $objDC->activeRecord->pid
				: '&amp;act=create&amp;mode=2&amp;pid=' . $objDC->activeRecord->pid;
		
		} else { // List view
			$strUrl .= strlen($objDC->parentTable)
				? '&amp;act=create&amp;mode=2&amp;pid=' . CURRENT_ID
				: '&amp;act=create';
		}
		
		$this->redirect($strUrl);
	}
	
	private static $objInstance;
	
	public static function getInstance() {
		if(!self::$objInstance)
			self::$objInstance = new self();
		return self::$objInstance;
	}

}
