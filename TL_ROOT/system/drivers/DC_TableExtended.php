<?php

require_once 'DC_MemoryExtended.php';
require_once 'DC_Table.php';

class DC_TableExtended extends DC_MemoryExtended implements listable {
	
	protected $strMode = false; // edit, editAll, overrideAll
	
	protected $objDCTable;
	
	public function __construct($strTable) {
		parent::__construct($strTable, null, false);
		
		$this->objDCTable = new DC_Table($strTable);
		$this->arrDCA = $GLOBALS['TL_DCA'][$strTable];
		
		$this->addAdminFields();
	}
	
	public function __get($strKey) {
		switch($strKey) {
			case 'treeView':
				return in_array($this->arrDCA['list']['sorting']['mode'], array(5, 6));
				break;
				
			default:
				if($strMode === false) {
					return $this->objDCTable->$strKey;
				}
				$varReturn = parent::__get($strKey);
				return $varReturn === null ? $this->objDCTable->$strKey : $varReturn;
				break;
		}
	}
	
	public function edit($intID = null, $strSelector = null) {
		$this->strMode = 'edit';
		return parent::edit($intID, $strSelector);
	}
	
	public function editAll($intID = false, $strSelector = false) {
		$this->strMode = 'editAll';
		$this->checkEditable();
		$arrIDs = $this->getIDs();
		
		if(!$this->loadEditableFields(true))
			return $this->generateFieldsForm();
		if(!$this->hasEditableFields())
			return $this->redirect($this->getReferer());
			
		if($strSelector && $intID)
			return isset($arrIDs[$intID]) ? $this->edit($intID, $strSelector) : '';
			
		$arrPBs = array();
		foreach($arrIDs as $intID => &$blnCreateNewVersion) {
			$this->intId = $intID;
			$this->setWidgetID($this->intId);
			$this->arrWidgets = array();
			$this->arrProcessed = array();
			
			$this->loadActiveRecord();
			$this->createInitialVersion($this->strTable, $intID);
			$this->blnCreateNewVersion = false;
			
			$objPB = new PaletteBuilder($this);
			
			$blnCreateNewVersion = $this->blnCreateNewVersion;
			
			if($objPB->isEmpty())
				continue;

			$arrPBs[$intID] = array(
				'title' => $this->objActiveRecord->title
					? $this->objActiveRecord->title . ' (ID ' . $intID . ')'
					: 'ID ' . $intID,
				'widgets'	=> $this->arrWidgets,
				'pb'		=> $objPB
			);
		}
		
		$this->loadDefaultButtons();
		
		if($this->blnSubmitted && !$this->noReload) {
			foreach($arrIDs as $intID => $blnCreateNewVersion) {
				$this->intId = $intID;
				$this->loadActiveRecord(true); // needed for consistence with onsubmit_callback
				$this->executeCallbacks($this->arrDCA['config']['onsubmit_callback'], $this);
			
				// Create a new version
				if($blnCreateNewVersion && !$this->blnAutoSubmitted) {
					$this->createNewVersion($this->strTable, $intID);
					$this->executeCallbacks($this->arrDCA['config']['onversion_callback'], $this->strTable, $this->intId, $this);
					$this->log(sprintf('A new version of %s ID %s has been created', $this->strTable, $this->intId), 'DC_Table editAll()', TL_GENERAL);
				}

				// Set current timestamp (-> DO NOT CHANGE ORDER version - timestamp)
				$this->updateTimestamp();
			}
			
			foreach($this->getButtonsDefinition() as $strButtonKey => $arrCallback) {
				if(isset($_POST[$strButtonKey])) {
					$this->import($arrCallback[0]);
					$this->{$arrCallback[0]}->{$arrCallback[1]}($this);
				}
			}
			
			$this->reload();
		}
		
		$strTemplate = $this->getTemplate('be_tableextended_field');
		foreach($arrPBs as $intID => &$arrPalette) {
			$this->intId = $intID;
			$this->setWidgetID($this->intId);
			$this->loadActiveRecord($this->blnSubmitted); // do not use cache if form was submitted
			$this->preloadTinyMce();
			$this->arrWidgets = $arrPalette['widgets'];
			$arrPalette['palette'] = $arrPalette['pb']->generateFieldsets($strTemplate, $this->arrStates);
		}
		
		$objTemplate = new BackendTemplate('be_tableextended_editall');
		
		$strTableEsc = specialchars($this->strTable);
		$objTemplate->setData(array(
			'rootPalettes'	=> $arrPBs,
			'oldBE'			=> $GLOBALS['TL_CONFIG']['oldBeTheme'],
			'table'			=> $this->strTable,
			'tableEsc'		=> $strTableEsc,
			'subHeadline'	=> sprintf($GLOBALS['TL_LANG']['MSC']['all_info'], $strTableEsc),
			'action'		=> ampersand($this->Environment->request, true),
			'enctype'		=> $this->blnUploadable ? 'multipart/form-data' : 'application/x-www-form-urlencoded',
//			'onsubmit'		=> implode(' ', $this->onsubmit),
			'error'			=> $this->noReload,
			'buttons'		=> $this->getButtonLabels()
		));
		
		return $objTemplate->parse();
	}
	
	public function overrideAll() {
		$this->strMode = 'overrideAll';
		$this->checkEditable();
		$arrIDs = $this->getIDs($intRootID);
		$this->setWidgetID($intRootID);
		
		if(!$this->loadEditableFields(true))
			return $this->generateFieldsForm();
		if(!$this->hasEditableFields())
			return $this->redirect($this->getReferer());
		
		foreach(array_keys($this->arrFields) as $strField) {
			$arrConfig = &$this->arrDCA['fields'][$strField];
			$arrConfig['update'] = ($arrConfig['inputType'] == 'checkbox' || $arrConfig['inputType'] == 'checkboxWizard')
					&& $arrConfig['eval']['multiple'];
			$arrConfig['eval']['alwaysSave'] = true;
			unset($arrConfig['eval']['submitOnChange']);
			unset($arrConfig);
		}
		
		$this->loadDefaultButtons();
		
		if($this->blnSubmitted) {
			end($arrIDs); // traverse array backwards to keep calculated palette of first entry
			while(null !== ($intID = key($arrIDs))) {
				$this->intId = $intID;
				$this->arrWidgets = array();
				$this->arrProcessed = array();
			
				$this->loadActiveRecord();
				$this->createInitialVersion($this->strTable, $intID);
				$this->blnCreateNewVersion = false;
				
				$objPB = new PaletteBuilder($this);
				
				$arrIDs[$intID] = $this->blnCreateNewVersion;
				prev($arrIDs);
			}
			
			if(!$this->noReload) {
				foreach($arrIDs as $intID => $blnCreateNewVersion) {
					$this->intId = $intID;
					
					$this->loadActiveRecord(true); // needed for consistence with onsubmit_callback
					$this->executeCallbacks($this->arrDCA['config']['onsubmit_callback'], $this);

					// Create a new version
					if($blnCreateNewVersion) {
						$this->createNewVersion($this->strTable, $intID);
						$this->executeCallbacks($this->arrDCA['config']['onversion_callback'], $this->strTable, $this->intId, $this);
						$this->log(sprintf('A new version of record ID %s (table %s) has been created', $this->intId, $this->strTable), 'DC_Table editAll()', TL_GENERAL);
					}

					// Set current timestamp (-> DO NOT CHANGE ORDER version - timestamp)
					$this->updateTimestamp();
				}
				
				foreach($this->getButtonsDefinition() as $strButtonKey => $arrCallback) {
					if(isset($_POST[$strButtonKey])) {
						$this->import($arrCallback[0]);
						$this->{$arrCallback[0]}->{$arrCallback[1]}($this);
					}
				}
				
				$this->reload();
			}
		} else {
			$this->intId = $intRootID;
			
			$this->loadActiveRecord();
			$this->createInitialVersion($this->strTable, $intRootID);
			
			$objPB = new PaletteBuilder($this);
		}
		
		$this->preloadTinyMce();
		$objTemplate = new BackendTemplate('be_tableextended_overrideall');
		
		$strTableEsc = specialchars($this->strTable);
		$objTemplate->setData(array(
			'fieldsets'		=> $objPB->generateFieldsets($this->getTemplate('be_tableextended_field'), $this->arrStates),
			'oldBE'			=> $GLOBALS['TL_CONFIG']['oldBeTheme'],
			'subHeadline'	=> sprintf($GLOBALS['TL_LANG']['MSC']['all_info'], $strTableEsc),
			'table'			=> $this->strTable,
			'tableEsc'		=> $strTableEsc,
			'action'		=> ampersand($this->Environment->request, true),
			'enctype'		=> $this->blnUploadable ? 'multipart/form-data' : 'application/x-www-form-urlencoded',
//			'onsubmit'		=> implode(' ', $this->onsubmit),
			'error'			=> $this->noReload,
			'buttons'		=> $this->getButtonLabels()
		));
		
		return $objTemplate->parse();
	}
	
	protected function updateTimestamp() {
		$this->Database->prepare(
			'UPDATE ' . $this->strTable . ' SET tstamp = ? WHERE id = ?'
		)->execute(time(), $this->intId);
	}
	
	protected function isUniqueValue($varNew) {
		$objUnique = $this->Database->prepare('
			SELECT	*
			FROM	' . $this->strTable . '
			WHERE	' . $this->strField . ' = ?
			AND		id != ?
		')->execute($varNew, $this->intId);
		
		return !$objUnique->numRows;
	}
	
	protected function resetFallback() {
		$this->Database->query('UPDATE ' . $this->strTable . ' SET ' . $this->strField . ' = \'\'');
	}
	
	protected function storeValue($varNew) {
		$objUpdateStmt = $this->Database->prepare(
			'UPDATE ' . $this->strTable . ' SET ' . $this->strField . ' = ? WHERE id = ?'
		)->execute(array($varNew, $this->intId));
		return $objUpdateStmt->affectedRows;
	}
	
	protected function loadActiveRecord($blnDontUseCache = false) {
		$strMethod = $blnDontUseCache ? 'executeUncached' : 'execute';
		$objRow = $this->Database->prepare('
			SELECT	*
			FROM	' . $this->strTable . '
			WHERE	id = ?
		')->limit(1)->$strMethod($this->intId);

		if($objRow->numRows)
			return $this->objActiveRecord = $objRow;
		
		$this->log('Could not load record ID "' . $this->intId . '" of table "' . $this->strTable . '"!', 'DC_TableExtended::loadActiveRecord()', TL_ERROR);
		$this->redirect('contao/main.php?act=error');
	}
	
	protected function loadDefaultButtons() {
		parent::loadDefaultButtons();
		
		if($this->strMode != 'edit')
			return;
			
		if($this->Input->get('s2e'))
			$this->arrDCA['buttons']['saveNedit'] = array('TableExtendedButtons', 'saveAndEdit');
		
		if($this->arrDCA['list']['sorting']['mode'] == 4
		|| strlen($this->ptable)
		|| $this->arrDCA['config']['switchToEdit'])
			$this->arrDCA['buttons']['saveNback'] = array('TableExtendedButtons', 'saveAndBack');
		
		if(!$this->arrDCA['config']['closed'])
			$this->arrDCA['buttons']['saveNcreate'] = array('TableExtendedButtons', 'saveAndCreate');
	}
	
	protected function setVersion($strVersion) {
		
		$objData = $this->Database->prepare('
			SELECT	*
			FROM	tl_version
			WHERE	fromTable = ?
			AND		pid = ?
			AND		version = ?
		')->limit(1)->execute($this->strTable, $this->intId, $strVersion);

		if(!$objData->numRows)
			return;
		
		$arrData = deserialize($objData->data);
		
		if(!is_array($arrData))
			return;
			
		$this->Database->prepare('UPDATE ' . $objData->fromTable . ' %s WHERE id = ?')->set($arrData)->execute($this->intId);
		$this->Database->prepare('UPDATE tl_version SET active=\'\' WHERE pid = ?')->execute($this->intId);
		$this->Database->prepare('UPDATE tl_version SET active = 1 WHERE pid = ? AND version = ?')->execute($this->intId, $strVersion);

		$this->log(sprintf('Version %s of record ID %s (table %s) has been restored', $strVersion, $this->intId, $this->strTable), 'DC_Table edit()', TL_GENERAL);

		$this->executeCallbacks(
			$this->arrDCA['config']['onrestore_callback'],
			$this->intId,
			$this->strTable,
			$arrData,
			$strVersion
		);
	}
	
	protected function getVersions() {
		if(!$this->arrDCA['config']['enableVersioning'])
			return;
			
		$objVersion = $this->Database->prepare('
			SELECT	tstamp, version, username, active
			FROM	tl_version
			WHERE	fromTable = ?
			AND		pid = ?
			ORDER BY version DESC
		')->execute($this->strTable, $this->intId);

		if(!$objVersion->numRows)
			return;
		
		return $objVersion->fetchAllAssoc();
	}
	
	protected function addAdminFields() {
		if(!$this->User->isAdmin)
			return;
		
		if(!isset($this->arrDCA['fields']['sorting']) && $this->Database->fieldExists('sorting', $this->strTable)) {
			$this->arrDCA['fields']['sorting'] = array(
				'label'		=> &$GLOBALS['TL_LANG']['MSC']['sorting'],
				'inputType'	=> 'text',
				'eval'		=> array('rgxp' => 'digit')
			);
		}

		if(!isset($this->arrDCA['fields']['pid']) && $this->Database->fieldExists('pid', $this->strTable)) {
			$this->arrDCA['fields']['pid'] = array(
				'label'		=> &$GLOBALS['TL_LANG']['MSC']['pid'],
				'inputType'	=> 'text',
				'eval'		=> array('rgxp'=>'digit')
			);
		}
	}
	
	// get current IDs from session
	protected function getIDs(&$intRootID = null) {
		$arrSession = $this->Session->getData();
		$arrIDs = $arrSession['CURRENT']['IDS'];
		
		if(!$arrIDs)
			$this->redirect($this->getReferer());
		
		$intRootID = reset($arrIDs);
		return array_flip($arrIDs);
	}
	
	protected function generateFieldsForm() {
		$arrFields = array();
		foreach(array_keys($this->arrFields) as $strField) {
			$arrConfig = $this->getFieldDefinition($strField);
			$strField = specialchars($strField);
			$arrFields[$strField] = $arrConfig['label'][0]
				? $arrConfig['label'][0]
				: ($GLOBALS['TL_LANG']['MSC'][$strField][0]
					? $GLOBALS['TL_LANG']['MSC'][$strField][0]
					: $strField);
		}
		natcasesort($arrFields);
		
		$objTemplate = new BackendTemplate('be_tableextended_fields');
		$objTemplate->setData(array(
			'fields' => $arrFields,
			'error' => $_POST && !count($_POST['all_fields']),
			'help' => $GLOBALS['TL_CONFIG']['showHelp'] && $GLOBALS['TL_LANG']['MSC']['all_fields'][1],
			'table' => specialchars($this->strTable)
		));
		return $objTemplate->parse();
	}
	
	public function __call($strMethod, $arrArgs) {
		switch($strMethod) {
			case 'cutAll':
			case 'copyAll':
			case 'deleteAll':
			case 'deleteChilds':
			case 'ajaxTreeView':
				return $this->delegateToDCTable($strMethod, $arrArgs);
				break;
		}
	}
	
	public function delete() {
		$arrArgs = func_get_args();
		return $this->delegateToDCTable('delete', $arrArgs);
	}
	
	public function show() {
		$arrArgs = func_get_args();
		return $this->delegateToDCTable('show', $arrArgs);
	}
	
	public function showAll() {
		$arrArgs = func_get_args();
		return $this->delegateToDCTable('showAll', $arrArgs);
	}
	
	public function undo() {
		$arrArgs = func_get_args();
		return $this->delegateToDCTable('undo', $arrArgs);
	}
	
	public function create() {
		$arrArgs = func_get_args();
		return $this->delegateToDCTable('create', $arrArgs);
	}
	
	public function cut() {
		$arrArgs = func_get_args();
		return $this->delegateToDCTable('cut', $arrArgs);
	}
	
	public function copy() {
		$arrArgs = func_get_args();
		return $this->delegateToDCTable('copy', $arrArgs);
	}
	
	public function move() {
		$arrArgs = func_get_args();
		return $this->delegateToDCTable('move', $arrArgs);
	}
	
	protected function delegateToDCTable($strMethod, $arrArgs) {
		return call_user_func_array(array($this->objDCTable, $strMethod), $arrArgs);
	}
	
}
