<?php

class DC_MemoryExtended extends DataContainer implements editable {
	
	protected $arrDCA; // the DCA of this table
	protected $blnSubmitted;
	protected $blnAutoSubmitted;
	protected $arrStates; // field set states
	protected $arrInputs; // set: fields submitted
	
	protected $blnCreateNewVersion = false;
	protected $blnUploadable = false;
	protected $arrFields; // map: fields possible for editing -> field dca
	protected $arrWidgets = array(); // map: field -> widget
	protected $arrProcessed = array(); // set: fields processed
	protected $arrButtons = array();
	
	protected $intWidgetID;
	
	protected static $arrDates = array(
		'date' => true,
		'time' => true,
		'datim' => true
	);
	
	public function __construct($strTable, array $arrDCA = null, $blnOnloadCallback = true) {
		parent::__construct();
		$this->strTable = $strTable;
		$this->arrDCA = $arrDCA ? $arrDCA : $GLOBALS['TL_DCA'][$strTable];
		 
		// Check whether the table is defined
		if(!strlen($this->strTable) || !count($this->arrDCA)) {
			$this->log('Could not load data container configuration for "' . $strTable . '"', 'DC_Table __construct()', TL_ERROR);
			trigger_error('Could not load data container configuration', E_USER_ERROR);
		}
		
//		$this->import('Encryption');
		$this->import('BackendUser', 'User');
		$this->blnSubmitted		= $_POST['FORM_SUBMIT'] == $this->strTable;
		$this->blnAutoSubmitted	= $_POST['SUBMIT_TYPE'] == 'auto';
		$this->arrInputs		= $_POST['FORM_INPUTS'] ? array_flip($this->Input->post('FORM_INPUTS')) : array();
		$this->arrStates		= $this->Session->get('fieldset_states');
		$this->arrStates		= (array) $this->arrStates[$this->strTable];
		$GLOBALS['TL_JAVASCRIPT'][] = 'system/modules/backboneit_dctableextended/js/dctableextended' . (version_compare(VERSION, '2.10', '<') ? '-2.9' : '') . '.js';

		$this->intId = $this->Input->get('id');
//		$this->root = null; // not used
	
		$blnOnloadCallback && $this->executeCallbacks($this->arrDCA['config']['onload_callback'], $this);
	}
	
	public function __get($strKey) {
		switch($strKey) {
			case 'createNewVersion':
				return $this->blnCreateNewVersion;
				break;
				
			case 'dca':
				return $this->dca;
				break;
				
			default:
				return parent::__get($strKey);
				break;
		}
	}
	
	public function edit($intID = null, $strSelector = null) {
		$this->checkEditable();
		
		$intID && $this->intId = $intID;
		$this->setWidgetID($this->intId);
		
		$this->checkVersion(); //version switched?
		
		$this->loadEditableFields();
		if(!$this->hasEditableFields())
			return $this->redirect($this->getReferer());
			
		$this->loadActiveRecord();
		$this->createInitialVersion($this->strTable, $this->intId);
		$this->blnCreateNewVersion = false; // just in case...
		
		$objPaletteBuilder = new PaletteBuilder($this);
		
		if($intID && $strSelector) {
			return $objPaletteBuilder->generateAjaxPalette(
				$strSelector,
				$strSelector . '::' . $this->intWidgetID,
				$this->getTemplate('be_tableextended_field')
			);
		}
		
		$this->loadDefaultButtons();
		
		if($this->blnSubmitted && !$this->noReload) {
			$this->executeCallbacks($this->arrDCA['config']['onsubmit_callback'], $this);

			// Save the current version
			if($this->blnCreateNewVersion && !$this->blnAutoSubmitted) {
				$this->createNewVersion($this->strTable, $this->intId);
				$this->executeCallbacks($this->arrDCA['config']['onversion_callback'], $this->strTable, $this->intId, $this);
				$this->log(sprintf('A new version of %s ID %s has been created', $this->strTable, $this->intId), 'DC_Table edit()', TL_GENERAL);
			}

			// Set the current timestamp (-> DO NOT CHANGE THE ORDER version - timestamp)
			$this->updateTimestamp();

			$_SESSION['TL_INFO'] = '';
			$_SESSION['TL_ERROR'] = '';
			$_SESSION['TL_CONFIRM'] = '';

			foreach($this->getButtonsDefinition() as $strButtonKey => $arrCallback) {
				if(isset($_POST[$strButtonKey])) {
					$this->import($arrCallback[0]);
					$this->{$arrCallback[0]}->{$arrCallback[1]}($this);
				}
			}
			
			$this->reload();
		}
		
		$this->preloadTinyMce();
		
		$objTemplate = new BackendTemplate('be_tableextended_edit');
		
		$objTemplate->setData(array(
			'fieldsets'		=> $objPaletteBuilder->generateFieldsets($this->getTemplate('be_tableextended_field'), $this->arrStates),
			'oldBE'			=> $GLOBALS['TL_CONFIG']['oldBeTheme'],
			'versions'		=> $this->getVersions(),
			'subHeadline'	=> sprintf($GLOBALS['TL_LANG']['MSC']['editRecord'], $this->intId ? 'ID ' . $this->intId : ''),
			'table'			=> $this->strTable,
			'enctype'		=> $this->blnUploadable ? 'multipart/form-data' : 'application/x-www-form-urlencoded',
			'onsubmit'		=> implode(' ', $this->onsubmit),
			'error'			=> $this->noReload,
			'buttons'		=> $this->getButtonLabels()
		));
		
		return $objTemplate->parse();
	}
	
	public function checkEditable() {
		if($this->isEditable())
			return;
			
		$this->log('Table ' . $this->strTable . ' is not editable', 'DC_Table edit()', TL_ERROR);
		$this->redirect('contao/main.php?act=error');
	}
	
	public function isEditable() {
		return !$this->arrDCA['config']['notEditable'];
	}
	
	public function hasEditableFields() {
		return count($this->arrFields) != 0;
	}
	
	public function isEditableField($strField) {
		return isset($this->arrFields[$strField]);
	}
	
	public function getSubpalettesDefinition() {
		return is_array($this->arrDCA['subpalettes']) ? $this->arrDCA['subpalettes'] : array();
	}
	
	public function getPalettesDefinition() {
		return is_array($this->arrDCA['palettes']) ? $this->arrDCA['palettes'] : array();
	}
	
	public function getButtonsDefinition() {
		return is_array($this->arrDCA['buttons']) ? $this->arrDCA['buttons'] : array();
	}
	
	public function getFieldDefinition($strField) {
		return is_array($this->arrDCA['fields'][$strField]) ? $this->arrDCA['fields'][$strField] : null;
	}
	
	public function getValue($strField) {
//		echo " field: $strField";
//		var_dump($this->objActiveRecord->$strField);
		$this->processInput($strField);
//		var_dump($this->objActiveRecord->$strField);
		return $this->objActiveRecord->$strField;
	}
	
	public function getWidget($strField) {
		if(isset($this->arrWidgets[$strField]))
			return $this->arrWidgets[$strField];
			
		if(!$this->isEditableField($strField))
			return;
			
		$arrConfig = $this->getFieldDefinition($strField);
		if(!$arrConfig)
			return;
		
		$this->strField = $strField;
		$this->strInputName = $strField . '::' . $this->intWidgetID;
		$this->varValue = deserialize(/*$arrConfig['eval']['encrypt'] ? $this->Encryption->decrypt($this->objActiveRecord->$strField) : */$this->objActiveRecord->$strField);
	
		if(is_array($arrConfig['load_callback'])) {
			foreach($arrConfig['load_callback'] as $arrCallback) {
				if(is_array($arrCallback)) {
					$this->import($arrCallback[0]);
					$this->varValue = $this->$arrCallback[0]->$arrCallback[1]($this->varValue, $this);
				}
			}
			// TODO: OH: remove this for clearance! source: DC_Table 1666
			// the value should be only "marked up" for editing in browser via load_callback
			// API should expose "true" internal (DB) value
			// $this->objActiveRecord->$strField = $this->varValue;
		}
		
		$arrConfig['eval']['xlabel'] = $this->getXLabel($arrConfig);
		if(is_array($arrConfig['input_field_callback'])) {
			$this->import($arrConfig['input_field_callback'][0]);
			$objWidget = $this->{$arrConfig['input_field_callback'][0]}->{$arrConfig['input_field_callback'][1]}($this, $arrConfig['eval']['xlabel']);
			return $this->arrWidgets[$strField] = isset($objWidget) ? $objWidget : ''; 
		}
		
		$strClass = $GLOBALS['BE_FFL'][$arrConfig['inputType']];
		if(!$this->classFileExists($strClass)) {
			throw new Exception("[DCA Config Error] No widget class found for input-type [{$arrConfig['inputType']}].");
		}
		
		// FIXME TEMPORARY WORKAROUND! To be fixed in the core: Controller::prepareForWidget(..)
		if(isset(self::$arrDates[$arrConfig['eval']['rgxp']])
		&& !$arrConfig['eval']['mandatory']
		&& is_numeric($this->varValue) && $this->varValue == 0)
			$this->varValue = '';
			
		// OH: why not $required = $mandatory always? source: DataContainer 226
		$arrConfig['eval']['required'] = $this->varValue == '' && $arrConfig['eval']['mandatory'] ? true : false;
		// OH: the whole prepareForWidget(..) thing is an only mess
		// widgets should parse the configuration by themselfs, depending on what they need
		$arrPrepared = $this->prepareForWidget(
			$arrConfig,
			$this->strInputName,
			$this->varValue,
			$this->strField,
			$this->strTable
		);
		//$arrConfig['options'] = $arrPrepared['options'];
	
		$objWidget = new $strClass($arrPrepared);
		// OH: what is this? source: DataContainer 232
		$objWidget->currentRecord = $this->intId;
		
		if($objWidget instanceof uploadable)
			$this->blnUploadable = true;
		
		// OH: xlabel, wizard: two ways to rome? wizards are the better way I think
		$objWidget->wizard = implode('', $this->executeCallbacks($arrConfig['wizard'], $this));	
		
		return $this->arrWidgets[$strField] = $objWidget;
	}
	
	protected function getXLabel($arrConfig) {
		$strXLabel = '';

		// Toggle line wrap (textarea)
		if ($arrConfig['inputType'] == 'textarea' && !strlen($arrConfig['eval']['rte']))
			$strXLabel .= ' ' . $this->generateImage(
				'wrap.gif',
				$GLOBALS['TL_LANG']['MSC']['wordWrap'],
				sprintf(
					'title="%s" class="toggleWrap" onclick="Backend.toggleWrap(\'ctrl_%s\');"',
					specialchars($GLOBALS['TL_LANG']['MSC']['wordWrap']),
					$this->strInputName
				)
			);
		
		// Add the help wizard
		if ($arrConfig['eval']['helpwizard'])
			$strXLabel .= sprintf(
				' <a href="contao/help.php?table=%s&amp;field=%s" title="%s" onclick="Backend.openWindow(this, 600, 500); return false;">%s</a>',
				$this->strTable,
				$this->strField,
				specialchars($GLOBALS['TL_LANG']['MSC']['helpWizard']),
				$this->generateImage(
					'about.gif',
					$GLOBALS['TL_LANG']['MSC']['helpWizard'],
					'style="vertical-align:text-bottom;"'
				)
			);
			
		// Add the popup file manager
		if ($arrConfig['inputType'] == 'fileTree'
		&& $this->strTable . '.' . $this->strField != 'tl_theme.templates')
			$strXLabel .= sprintf(
				' <a href="contao/files.php" title="%s" onclick="Backend.getScrollOffset(); Backend.openWindow(this, 750, 500); return false;">%s</a>',
				specialchars($GLOBALS['TL_LANG']['MSC']['fileManager']),
				$this->generateImage(
					'filemanager.gif',
					$GLOBALS['TL_LANG']['MSC']['fileManager'],
					'style="vertical-align:text-bottom;"'
				)
			);
			
		// Add table import wizard
		elseif ($arrConfig['inputType'] == 'tableWizard')
			$strXLabel .= sprintf(
				' <a href="%s" title="%s" onclick="Backend.getScrollOffset();">%s</a> %s%s',
				ampersand($this->addToUrl('key=table')),
				specialchars($GLOBALS['TL_LANG'][$this->strTable]['importTable'][1]),
				$this->generateImage(
					'tablewizard.gif',
					$GLOBALS['TL_LANG'][$this->strTable]['importTable'][0],
					'style="vertical-align:text-bottom;"'
				),
				$this->generateImage(
					'demagnify.gif',
					$GLOBALS['TL_LANG']['tl_content']['shrink'][0],
					'title="' . specialchars($GLOBALS['TL_LANG']['tl_content']['shrink'][1]) . '" style="vertical-align:text-bottom; cursor:pointer;" onclick="Backend.tableWizardResize(0.9);"'
				),
				$this->generateImage(
					'magnify.gif',
					$GLOBALS['TL_LANG']['tl_content']['expand'][0],
					'title="' . specialchars($GLOBALS['TL_LANG']['tl_content']['expand'][1]) . '" style="vertical-align:text-bottom; cursor:pointer;" onclick="Backend.tableWizardResize(1.1);"'	
				)
			);
			
		// Add list import wizard
		elseif ($arrData['inputType'] == 'listWizard')
			$strXLabel .= sprintf(
				' <a href="%s" title="%s" onclick="Backend.getScrollOffset();">%s</a>',
				ampersand($this->addToUrl('key=list')),
				specialchars($GLOBALS['TL_LANG'][$this->strTable]['importList'][1]),
				$this->generateImage(
					'tablewizard.gif',
					$GLOBALS['TL_LANG'][$this->strTable]['importList'][0],
					'style="vertical-align:text-bottom;"'
				)
			);
		
		return $strXLabel;
	}
	
	protected function processInput($strField) {
		if(isset($this->arrProcessed[$strField]))
			return;
			
		$this->arrProcessed[$strField] = true;
		$this->strField = $strField;
		$this->strInputName = $strField . '::' . $this->intWidgetID;
		
		if(!$this->blnSubmitted // no form submit
		|| !isset($this->arrInputs[$this->strInputName])
		|| !$this->isEditableField($strField))
			return;
		
		$objWidget = $this->getWidget($strField);
		if(!($objWidget instanceof Widget))
			return;
		
		$objWidget->validate();
		
		if($objWidget->hasErrors()) {
			$this->noReload = true;
			return;
		}
		
		if(!$objWidget->submitInput())
			return;
		
		$varNew = $objWidget->value;
		$arrConfig = $this->getFieldDefinition($strField);
			
		if(is_array($varNew)) {
			ksort($varNew);
		} elseif($varNew != '' && isset(self::$arrDates[$arrConfig['eval']['rgxp']])) { // OH: this should be a widget feature
			$objDate = new Date($varNew, $GLOBALS['TL_CONFIG'][$arrConfig['eval']['rgxp'] . 'Format']);
			$varNew = $objDate->tstamp;
		}
	
		//Handle multi-select fields in "override all" mode
		// OH: this should be a widget feature
		if(($arrConfig['inputType'] == 'checkbox' || $arrConfig['inputType'] == 'checkboxWizard')
		&& $arrConfig['eval']['multiple']
		&& $this->Input->get('act') == 'overrideAll') {
			if(!is_array($arrNew))
				$arrNew = array();

			switch($this->Input->post($objWidget->name . '_update')) {
				case 'add':
					$varNew = array_values(array_unique(array_merge(deserialize($this->objActiveRecord->$strField, true), $arrNew)));
					break;

				case 'remove':
					$varNew = array_values(array_diff(deserialize($this->objActiveRecord->$strField, true), $arrNew));
					break;

				case 'replace':
					$varNew = $arrNew;
					break;
			}

			if(!$varNew)
				$varNew = '';
		}
	
		try {
			if(is_array($arrConfig['save_callback'])) {
				foreach($arrConfig['save_callback'] as $arrCallback) {
					$this->import($arrCallback[0]);
					$varNew = $this->$arrCallback[0]->$arrCallback[1]($varNew, $this);
				}
			}
		} catch (Exception $e) {
			$this->noReload = true;
			$objWidget->addError($e->getMessage());
			return;
		}
		
		// value hasnt changed
		if(!$arrConfig['eval']['alwaysSave']) {
			if(deserialize($this->objActiveRecord->$strField) == $varNew)
				return;
			if($arrConfig['eval']['doNotSaveEmpty'] && $varNew != '') // value is empty
				return;
		}
		
		if($varNew != '') {
			if($arrConfig['eval']['encrypt']) {
				//$varNew = $this->Encryption->encrypt(is_array($varNew) ? serialize($varNew) : $varNew);
		
			} elseif($arrConfig['eval']['unique'] && !$this->isUniqueValue($varNew)) {
				$this->noReload = true;
				$objWidget->addError(sprintf($GLOBALS['TL_LANG']['ERR']['unique'], $objWidget->label));
				return;
				
			// OH: completly correct would be "if" instead of "elseif",
			// but this is a very rare case, where only one value is stored in the field
			// and a new value must differ from the existing value
			// lets treat fallback and unique as exclusive
			} elseif($arrConfig['eval']['fallback']) {
				$this->resetFallback();
			}		
		}
		
		if(!$this->storeValue($varNew))
			return;
		elseif(!$arrConfig['eval']['submitOnChange'] && $this->objActiveRecord->$strField != $varNew)
			$this->blnCreateNewVersion = true;
		
		$this->objActiveRecord->$strField = $varNew;
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
	
	protected function checkVersion() {
		if(!$this->arrDCA['config']['enableVersioning'])
			return;
		
		if(!$this->Input->post('FORM_SUBMIT') == 'tl_version')
			return;
		
		$strVersion = $this->Input->post('version');
		if(!strlen($strVersion))
			return;
		
		$this->setVersion($strVersion);
		
		$this->reload();
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
	
	protected function loadEditableFields($blnUserSelection = false) {
		$this->arrFields = array_flip(array_keys(array_filter($this->arrDCA['fields'], create_function('$arr', 'return !$arr[\'exclude\'];'))));
		
		if(!$blnUserSelection)
			return true;
			
		if(!$this->Input->get('fields'))
			return false;
			
		$arrSession = $this->Session->getData();
		if($this->Input->post('FORM_SUBMIT') == $this->strTable . '_all') {
			$arrSession['CURRENT'][$this->strTable] = deserialize($this->Input->post('all_fields'));
			$this->Session->setData($arrSession);
			$this->reload(); // OH: i think its better
		}
		
		if(!is_array($arrSession['CURRENT'][$this->strTable]))
			return false;
		
		$this->arrFields = array_intersect_key($this->arrFields, array_flip($arrSession['CURRENT'][$this->strTable]));

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
	
	protected function loadDefaultButtons() {
		$this->arrDCA['buttons']['save'] = array('MemoryExtendedButtons', 'save');
		$this->arrDCA['buttons']['saveNclose'] = array('MemoryExtendedButtons', 'saveAndClose');
	}
	
	protected function getButtonLabels() {
		$arrButtons = array();
		
		foreach(array_keys($this->getButtonsDefinition()) as $strButton) {
			if(isset($GLOBALS['TL_LANG'][$this->strTable][$strButton])) {
				$strLabel = $GLOBALS['TL_LANG'][$this->strTable][$strButton];
			} elseif(isset($GLOBALS['TL_LANG']['MSC'][$strButton])) {
				$strLabel = $GLOBALS['TL_LANG']['MSC'][$strButton];
			} else {
				$strLabel = $strButton;
			}
			$arrButtons[$strButton] = $strLabel;
		}
		
		return $arrButtons;
	}
	
	protected function setWidgetID($intID) {
		if(preg_match('/^[0-9]+$/', $intID)) {
			$this->intWidgetID = intval($intID);
		} else {
			$this->intWidgetID = 'b' . str_replace('=', '_', base64_encode($intID));
		}
	}
	
	protected function preloadTinyMce() {
		if(!$this->getSubpalettesDefinition())
			return;

		foreach($this->arrFields as $strField) {
			$arrConfig = $this->getFieldDefinition($strField);
			if(!isset($arrConfig['eval']['rte']))
				continue;

			if(strncmp($arrConfig['eval']['rte'], 'tiny', 4) !== 0)
				continue;

			list($strFile, $strType) = explode('|', $arrConfig['eval']['rte']);
			$strID = 'ctrl_' . $strField . '::' . $this->intWidgetID;

			$GLOBALS['TL_RTE'][$strFile][$strID] = array(
				'id'   => $strID,
				'file' => $strFile,
				'type' => $strType
			);
		}
	}
		
	protected function executeCallbacks($varCallbacks) {
		if ($varCallbacks === null)
			return array();
		if (is_string($varCallbacks))
			$varCallbacks = $GLOBALS['TL_HOOKS'][$varCallbacks];
		if (!is_array($varCallbacks))
			return array();
		
		$arrArgs = array_slice(func_get_args(), 1);
		$arrResults = array();
		foreach ($varCallbacks as $arrCallback) {
			if (is_array($arrCallback)) {
				$this->import($arrCallback[0]);
				$arrCallback[0] = $this->{$arrCallback[0]};
				$arrResults[] = call_user_func_array($arrCallback, $arrArgs);
			}
		}
		return $arrResults;
	}
	
	public function create() {
		call_user_func_array(array($this, 'edit'), func_get_args());
	}
	
	public function cut() {
		call_user_func_array(array($this, 'edit'), func_get_args());
	}
	
	public function copy() {
		call_user_func_array(array($this, 'edit'), func_get_args());
	}
	
	public function move() {
		call_user_func_array(array($this, 'edit'), func_get_args());
	}
	
}
