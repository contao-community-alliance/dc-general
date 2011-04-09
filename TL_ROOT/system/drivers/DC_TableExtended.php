<?php

require_once 'DC_Table.php';

class DC_TableExtended extends DC_Table {
	
	protected $arrInputs; // set: fields submitted
	protected $blnSubmitted;
	protected $blnAutoSubmitted;
	protected $strFieldTemplate;
	protected $arrStates; // field set states
	
	protected $arrSelectors; // set: selectors fields, that need a dynamic reload
	protected $arrFields; // map: fields possible for editing -> field dca
	
	protected $intWidgetID;
	
	protected $arrRootPalette; // tree: parsed palette
	protected $arrSubpalettes; // map: selector -> subpalette (depending on active record, null if not palette not parsed)
	
	protected $arrWidgets; // map: field -> widget
	protected $arrProcessed; // set: fields processed
	
	private static $arrDates = array(
		'date' => true,
		'time' => true,
		'datim' => true
	);
	
	private static $arrIEEventFix = array(
		'checkbox' => true,
		'checkboxWizard' => true,
		'radio' => true,
		'radioTable' => true
	);
	
	public function __construct($strTable) {
		parent::__construct($strTable);
		$this->import('Encryption');
		$this->import('BackendUser', 'User');
		$this->blnSubmitted = $this->Input->post('FORM_SUBMIT') == $this->strTable;
		$this->blnAutoSubmitted = $this->Input->post('SUBMIT_TYPE') == 'auto';
		$this->arrInputs = ($arrInputs = $this->Input->post('FORM_INPUTS')) ? array_flip($arrInputs) : array();
		$this->arrStates = $this->Session->get('fieldset_states');
		$this->arrStates = $this->arrStates[$strTable];
		$this->strFieldTemplate = $this->getTemplate('be_tableextended_field');
		$GLOBALS['TL_JAVASCRIPT'][] = 'system/modules/backboneit_dctableextended/js/dctableextended.js';
	}
	
	protected function compileRootPalette(&$arrRootPalette) {
		$strClass = 'tl_tbox';
		
		foreach($arrRootPalette as &$arrFieldset) {
			if($strLegend = &$arrFieldset['legend']) {
				$arrClasses = explode(':', substr($strLegend, 1, -1));
				$strLegend = array_shift($arrClasses);
				$arrClasses = array_flip($arrClasses);
				if(isset($this->arrStates[$strLegend])) {
					if($this->arrStates[$strLegend])
						unset($arrClasses['hide']);
					else
						$arrClasses['collapsed'] = true;
				}
				$strClass .= ' ' . implode(' ', array_keys($arrClasses));
				$arrFieldset['label'] = isset($GLOBALS['TL_LANG'][$this->strTable][$strLegend]) ? $GLOBALS['TL_LANG'][$this->strTable][$strLegend] : $strLegend;
			}
			
			$arrFieldset['class'] = $strClass;
			$arrFieldset['palette'] = $this->generatePalette($arrFieldset['palette']);
			
			$strClass = 'tl_box';
		}
	}
	
	protected function generatePalette(array $arrPalette) {
		ob_start();
		foreach($arrPalette as $varField) {
			if(is_array($varField)) {
				echo '<div id="sub_' . $strName /* this is the input name from the last loop */ . '">', $this->generatePalette($varField), '</div>';
			} else {
				$objWidget = $this->getWidget($varField);
				if(!$objWidget instanceof Widget) {
					echo $objWidget;
					continue;
				}
	
				$arrConfig = $this->arrFields[$varField];
				
				$strClass = $arrConfig['eval']['tl_class'];
				
				// this should be correctly specified in DCAs
//				if($arrConfig['inputType'] == 'checkbox'
//				&& !$arrConfig['eval']['multiple']
//				&& strpos($strClass, 'w50') !== false
//				&& strpos($strClass, 'cbx') === false)
//					$strClass .= ' cbx';
					
				if($arrConfig['eval']['submitOnChange'] && isset($this->arrSelectors[$varField])) {
					$objWidget->onclick = '';
					$objWidget->onchange = '';
					$strClass .= ' selector';
				}
		
				$strName = specialchars($objWidget->name);
				$blnUpdate = $arrConfig['update'];
				$strDatepicker = $arrConfig['eval']['datepicker'] ? sprintf($arrConfig['eval']['datepicker'], 'ctrl_' . specialchars($objWidget->id)) : null;
		
				include($this->strFieldTemplate);
			}
		}
		return ob_get_clean();
	}
	
	protected function getWidget($strField) {
		if(isset($this->arrWidgets[$strField]))
			return $this->arrWidgets[$strField];
			
		$arrConfig = &$this->arrFields[$strField];
		if(!is_array($arrConfig))
			return;
		
		$this->strField = $strField;
		$this->strInputName = $strField . '_' . $this->intWidgetID; //$this->intId;
		$this->varValue = deserialize($arrConfig['eval']['encrypt'] ? $this->Encryption->decrypt($this->objActiveRecord->$strField) : $this->objActiveRecord->$strField);
	
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
			$objWidget = $this->$arrConfig['input_field_callback'][0]->$arrConfig['input_field_callback'][1]($this, $arrConfig['eval']['xlabel']);
			return $this->arrWidgets[$strField] = isset($objWidget) ? $objWidget : ''; 
		}
		
		$strClass = $GLOBALS['BE_FFL'][$arrConfig['inputType']];
		if(!$this->classFileExists($strClass)) {
			if($GLOBALS['TL_CONFIG']['debugMode'])
				throw new Exception("[DCA Config Error] No widget class found for input-type [{$arrConfig['inputType']}].");
			return;
		}
		
		// FIXME TEMPORARY WORKAROUND! To be fixed in the core: Controller::prepareForWidget(..)
		if(isset(self::$arrDates[$arrConfig['eval']['rgxp']])
		&& !$arrConfig['eval']['mandatory']
		&& $this->varValue === 0)
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
		
		if(!$this->blnSubmitted // no form submit
		|| !isset($this->arrInputs[$strField . '_' . $this->intWidgetID])
		|| !isset($this->arrFields[$strField]) // field excluded or not selected
		|| !($objWidget = $this->getWidget($strField)) instanceof Widget) // not a widget
			return;
		
		$objWidget->validate();
		
		if($objWidget->hasErrors()) {
			$this->noReload = true;
			return;
		}
		
		if(!$objWidget->submitInput())
			return;
		
		$varNew = $objWidget->value;
		$arrConfig = $this->arrFields[$this->strField];
			
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

			if(!varNew)
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
		
		if(is_array($varNew))
			$varNew = serialize($varNew);
			
		// value hasnt changed
		if($this->objActiveRecord->$strField == $varNew && !$arrConfig['eval']['alwaysSave'])
			return;
		
		if($varNew != '') {
			if($arrConfig['eval']['encrypt'])
				$varNew = $this->Encryption->encrypt($varNew);
		
			elseif($arrConfig['eval']['unique']) {
				$objUnique = $this->Database->prepare('
					SELECT
						*
					FROM 
						' . $this->strTable . '
					WHERE
						' . $strField . ' = ?
					AND
						id != ?
				')->execute($varNew, $this->intId);
		
				if($objUnique->numRows) {
					$this->noReload = true;
					$objWidget->addError(sprintf($GLOBALS['TL_LANG']['ERR']['unique'], $objWidget->label));
					return;
				}
			}
			
			// OH: completly correct would be "if" instead of "elseif",
			// but this is a very rare case, where only one value is stored in the field
			// and a new value must differ from the existing value
			// lets treat fallback and unique as exclusive
			elseif($arrConfig['eval']['fallback'])
				$this->Database->execute('UPDATE ' . $this->strTable . ' SET ' . $strField . ' = \'\'');
		} elseif($arrConfig['eval']['doNotSaveEmpty']) // value is empty
			return;
			
		$objUpdateStmt = $this->Database->prepare(
			'UPDATE ' . $this->strTable . ' SET ' . $this->strField . ' = ? WHERE id = ?'
		)->execute($varNew, $this->intId);

		if(!$objUpdateStmt->affectedRows)
			return;
		elseif(!$arrConfig['eval']['submitOnChange'] && $this->objActiveRecord->$strField != $varNew)
			$this->blnCreateNewVersion = true;
		
		$this->objActiveRecord->$strField = $varNew;
	}
	
	public function edit($intID = null, $strSelector = null) {
		$this->checkEditable();
		
		if($intID)
			$this->intId = $intID;
		$this->intWidgetID = $this->intId;
		
		$this->checkVersion(); //version switched?
		
		if(!$this->filterFields())
			return '';
			
		$this->calculateSelectors();
			
		$this->loadActiveRecord();
		$this->createInitialVersion($this->strTable, $this->intId);
		
		$this->blnCreateNewVersion = false; // just in case...
		$this->calculatePalettes();
		
//		echo '<pre>'; print_r($intID); echo '</pre>';
//		echo '<pre>'; print_r($strSelector); echo '</pre>';
//		echo '<pre>'; print_r($this->arrInputs); echo '</pre>';
//		echo '<pre>'; print_r($this->arrFields); echo '</pre>';
//		echo '<pre>'; print_r($this->arrSelectors); echo '</pre>';
//		echo '<pre>'; print_r($this->arrRootPalette); echo '</pre>';
//		echo '<pre>'; print_r($this->arrSubpalettes); echo '</pre>';
//		exit;

		if($intID && $strSelector) {
			if(is_array($arrSubpalette = $this->arrSubpalettes[$strSelector]))
				return '<div id="sub_' . $strSelector . '_' . $intID . '">' . $this->generatePalette($arrSubpalette) . '</div>';
			return '';
		}
		
		if($this->blnSubmitted && !$this->noReload) {
			$this->executeCallbacks($GLOBALS['TL_DCA'][$this->strTable]['config']['onsubmit_callback'], $this);

			// Save the current version
			if($this->blnCreateNewVersion && !$this->blnAutoSubmitted) {
				$this->createNewVersion($this->strTable, $this->intId);
				$this->log(sprintf('A new version of %s ID %s has been created', $this->strTable, $this->intId), 'DC_Table edit()', TL_GENERAL);
			}

			// Set the current timestamp (-> DO NOT CHANGE THE ORDER version - timestamp)
			$this->Database->prepare(
				'UPDATE ' . $this->strTable . ' SET tstamp = ? WHERE id = ?'
			)->execute(time(), $this->intId);

			$_SESSION['TL_INFO'] = '';
			$_SESSION['TL_ERROR'] = '';
			$_SESSION['TL_CONFIRM'] = '';

			setcookie('BE_PAGE_OFFSET', 0, 0, '/');
			
			if(isset($_POST['saveNclose']))
				$this->redirect($this->getReferer());

			elseif(isset($_POST['saveNedit'])) {
				$strUrl = $this->addToUrl($GLOBALS['TL_DCA'][$this->strTable]['list']['operations']['edit']['href']);

				$strUrl = preg_replace('/(&amp;)?s2e=[^&]*/i', '', $strUrl);
				$strUrl = preg_replace('/(&amp;)?act=[^&]*/i', '', $strUrl);

				$this->redirect($strUrl);
			}

			elseif(isset($_POST['saveNback'])) {
				if($this->ptable == '')
					$this->redirect($this->Environment->script . '?do=' . $this->Input->get('do'));
				
				elseif($this->ptable == 'tl_theme' && $this->strTable == 'tl_style_sheet') // TODO: try to abstract this
					$this->redirect($this->getReferer(false, $this->strTable));
				
				else
					$this->redirect($this->getReferer(false, $this->ptable));
			}

			elseif(isset($_POST['saveNcreate'])) {
				$strUrl = $this->Environment->script . '?do=' . $this->Input->get('do');

				if(isset($_GET['table']))
					$strUrl .= '&amp;table=' . $this->Input->get('table');

				if($this->treeView)
					$strUrl .= '&amp;act=create&amp;mode=1&amp;pid=' . $this->intId;

				// Parent view
				elseif($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['mode'] == 4)
					$strUrl .= $this->Database->fieldExists('sorting', $this->strTable) ? '&amp;act=create&amp;mode=1&amp;pid=' . $this->intId . '&amp;id=' . $this->activeRecord->pid : '&amp;act=create&amp;mode=2&amp;pid=' . $this->activeRecord->pid;
				
				// List view
				else
					$strUrl .= strlen($this->ptable) ? '&amp;act=create&amp;mode=2&amp;pid=' . CURRENT_ID : '&amp;act=create';

				$this->redirect($strUrl);
			}

			$this->reload();
		}
		
		$this->compileRootPalette($this->arrRootPalette);
		
		$objTemplate = new BackendTemplate('be_tableextended_edit');
		
		$objTemplate->setData(array(
			'fieldsets'		=> $this->arrRootPalette,
			'oldBE'			=> $GLOBALS['TL_CONFIG']['oldBeTheme'],
			'versions'		=> $this->getVersions(),
			'subHeadline'	=> sprintf($GLOBALS['TL_LANG']['MSC']['editRecord'], $this->intId ? 'ID ' . $this->intId : ''),
			'table'			=> $this->strTable,
			'enctype'		=> $this->blnUploadable ? 'multipart/form-data' : 'application/x-www-form-urlencoded',
			'onsubmit'		=> implode(' ', $this->onsubmit),
			'error'			=> $this->noReload,
			'createButton'	=> !$GLOBALS['TL_DCA'][$this->strTable]['config']['closed'],
			'editButton'	=> $this->Input->get('s2e'),
			'backButton'	=> $GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['mode'] == 4
				|| strlen($this->ptable)
				|| $GLOBALS['TL_DCA'][$this->strTable]['config']['switchToEdit']
		));
		
		return $objTemplate->parse();
	}
	
	public function editAll($intID = false, $strSelector = false) {
		$arrIDs = $this->getIDs();
		$this->checkEditable();
		
		if(!$this->filterFields(true)) // no fields set
			return $this->generateFieldsForm();
		if($strSelector && $intID)
			return isset($arrIDs[$intID]) ? $this->edit($intID, $strSelector) : '';
			
		$this->calculateSelectors();
		$arrRootPalettes = array();
		foreach($arrIDs as $intID => &$blnCreateNewVersion) {
			$this->intId = $this->intWidgetID = $intID;
			$this->loadActiveRecord();
			$this->createInitialVersion($this->strTable, $intID);
			$blnCreateNewVersion = $this->calculatePalettes();
			
			if(count($this->arrRootPalette)) {
				if($this->objActiveRecord->title)
					$strTitle = $this->objActiveRecord->title . ' (ID ' . $intID . ')';
				else
					$strTitle = 'ID ' . $intID;
			
				$arrRootPalettes[$intID] = array(
					'title' => $strTitle,
					'widgets' => $this->arrWidgets,
					'palette' => $this->arrRootPalette
				);
			}
		}

		if($this->blnSubmitted && !$this->noReload) {
			foreach($arrIDs as $intID => $blnCreateNewVersion) {
				$this->intId = $intID;
				$this->loadActiveRecord(true); // needed for consistence with onsubmit_callback
				$this->executeCallbacks($GLOBALS['TL_DCA'][$this->strTable]['config']['onsubmit_callback'], $this);
			
				// Create a new version
				if($blnCreateNewVersion && !$this->blnAutoSubmitted) {
					$this->createNewVersion($this->strTable, $intID);
					$this->log(sprintf('A new version of %s ID %s has been created', $this->strTable, $this->intId), 'DC_Table editAll()', TL_GENERAL);
				}

				// Set current timestamp (-> DO NOT CHANGE ORDER version - timestamp)
				$this->Database->prepare(
					'UPDATE ' . $this->strTable . ' SET tstamp = ? WHERE id = ?'
				)->execute(time(), $intID);
			}
			
			// Reload the page to prevent _POST variables from being sent twice
			if($this->Input->post('saveNclose')) {
				setcookie('BE_PAGE_OFFSET', 0, 0, '/');
				$this->redirect($this->getReferer());
			}

			$this->reload();
		}
		
		foreach($arrRootPalettes as $intID => &$arrRootPalette) {
			$this->intId = $this->intWidgetID = $intID;
			$this->loadActiveRecord($this->blnSubmitted); // use cache if form was submitted
			$this->arrWidgets = $arrRootPalette['widgets'];
			$this->compileRootPalette($arrRootPalette['palette']);
		}
		
		$objTemplate = new BackendTemplate('be_tableextended_editall');
		
		$strTableEsc = specialchars($this->strTable);
		$objTemplate->setData(array(
			'rootPalettes' => $arrRootPalettes,
			'oldBE' => $GLOBALS['TL_CONFIG']['oldBeTheme'],
			'table' => $this->strTable,
			'tableEsc' => $strTableEsc,
			'subHeadline' => sprintf($GLOBALS['TL_LANG']['MSC']['all_info'], $strTableEsc),
			'action' => ampersand($this->Environment->request, true),
			'enctype' => $this->blnUploadable ? 'multipart/form-data' : 'application/x-www-form-urlencoded',
//			'onsubmit' => implode(' ', $this->onsubmit),
			'error' => $this->noReload
		));
		
		return $objTemplate->parse();
	}
	
	public function overrideAll() {
		$arrIDs = $this->getIDs($intRootID);
		$this->checkEditable();
		
		if(!$this->filterFields(true)) // no fields set
			return $this->generateFieldsForm();
		foreach($this->arrFields as &$arrConfig) {
			$arrConfig['update'] = ($arrConfig['inputType'] == 'checkbox' || $arrConfig['inputType'] == 'checkboxWizard')
					&& $arrConfig['eval']['multiple'];
			$arrConfig['eval']['alwaysSave'] = true;
			unset($arrConfig['eval']['submitOnChange']);
		}
		
		$this->intWidgetID = $intRootID;
		if($this->blnSubmitted) {
			end($arrIDs); // traverse array backwards to keep calculated palette of first entry
			while(null !== ($intID = key($arrIDs))) {
				$this->intId = $intID;
				$this->loadActiveRecord();
				$this->createInitialVersion($this->strTable, $intID);
				
				$arrIDs[$intID] = $this->calculatePalettes();
				prev($arrIDs);
			}
			
			if(!$this->noReload) {
				foreach($arrIDs as $intID => $blnCreateNewVersion) {
					$this->intId = $intID;
					
					$this->loadActiveRecord(true); // needed for consistence with onsubmit_callback
					$this->executeCallbacks($GLOBALS['TL_DCA'][$this->strTable]['config']['onsubmit_callback'], $this);

					// Create a new version
					if($blnCreateNewVersion) {
						$this->createNewVersion($this->strTable, $intID);
						$this->log(sprintf('A new version of record ID %s (table %s) has been created', $this->intId, $this->strTable), 'DC_Table editAll()', TL_GENERAL);
					}

					// Set current timestamp (-> DO NOT CHANGE ORDER version - timestamp)
					$this->Database->prepare(
						'UPDATE ' . $this->strTable . ' SET tstamp = ? WHERE id = ?'
					)->execute(time(), $this->intId);
				}
				
				if($this->Input->post('saveNclose')) {
					setcookie('BE_PAGE_OFFSET', 0, 0, '/');
					$this->redirect($this->getReferer());
				}

				$this->reload();
			}
		} else {
			$this->intId = $intRootID;
			
			$this->loadActiveRecord();
			$this->createInitialVersion($this->strTable, $intRootID);
			
			$this->calculatePalettes();
		}
		
		$this->compileRootPalette($this->arrRootPalette);
		
		$objTemplate = new BackendTemplate('be_tableextended_overrideall');
		
		$strTableEsc = specialchars($this->strTable);
		$objTemplate->setData(array(
			'fieldsets' => $this->arrRootPalette,
			'oldBE' => $GLOBALS['TL_CONFIG']['oldBeTheme'],
			'subHeadline' => sprintf($GLOBALS['TL_LANG']['MSC']['all_info'], $strTableEsc),
			'table' => $this->strTable,
			'tableEsc' => $strTableEsc,
			'action' => ampersand($this->Environment->request, true),
			'enctype' => $this->blnUploadable ? 'multipart/form-data' : 'application/x-www-form-urlencoded',
//			'onsubmit' => implode(' ', $this->onsubmit),
			'error' => $this->noReload
		));
		
		return $objTemplate->parse();
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
		foreach($this->arrFields as $strField => $arrConfig) {
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
	
	protected function filterFields($blnUserSelection = false) {
		if(isset($this->arrFields))
			return count($this->arrFields);
			
		$arrFields = $GLOBALS['TL_DCA'][$this->strTable]['fields'];
		
		if($this->User->isAdmin) {
			if(!isset($arrFields['sorting']) && $this->Database->fieldExists('sorting', $this->strTable))
				$arrFields['sorting'] = array(
					'label'		=> &$GLOBALS['TL_LANG']['MSC']['sorting'],
					'inputType'	=> 'text',
					'eval'		=> array('rgxp' => 'digit')
				);
	
			if(!isset($arrFields['pid']) && $this->Database->fieldExists('pid', $this->strTable))
				$arrFields['pid'] = array(
					'label'		=> &$GLOBALS['TL_LANG']['MSC']['pid'],
					'inputType'	=> 'text',
					'eval'		=> array('rgxp'=>'digit')
				);
		}
		
		$this->arrFields = array_filter($arrFields, create_function('$arr', 'return !$arr[\'exclude\'];'));
		
		if(!$blnUserSelection)
			return count($this->arrFields);
			
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

		return count($this->arrFields);
	}
	
	protected function loadActiveRecord($blnUseCache = false) {
		$strMethod = $blnUseCache ? 'executeUncached' : 'execute';
		$objRow = $this->Database->prepare('
			SELECT
				*
			FROM
				' . $this->strTable . '
			WHERE
				id = ?
		')->limit(1)->$strMethod($this->intId);

		if($objRow->numRows)
			return $this->objActiveRecord = $objRow;
		
		$this->log('Could not load record ID "' . $this->intId . '" of table "' . $this->strTable . '"!', 'DC_TableExtended::loadActiveRecord()', TL_ERROR);
		$this->redirect('contao/main.php?act=error');
	}
	
	protected function checkVersion() {
		if(!$GLOBALS['TL_DCA'][$this->strTable]['config']['enableVersioning'])
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
		
		$objData = $this->Database->prepare('
			SELECT
				*
			FROM
				tl_version
			WHERE
				fromTable = ?
			AND
				pid = ?
			AND
				version = ?
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
			$GLOBALS['TL_DCA'][$this->strTable]['config']['onrestore_callback'],
			$this->intId,
			$this->strTable,
			$arrData,
			$strVersion
		);
	}
	
	protected function getVersions() {
		if(!$GLOBALS['TL_DCA'][$this->strTable]['config']['enableVersioning'])
			return;
			
		$objVersion = $this->Database->prepare('
			SELECT
				tstamp,
				version,
				username,
				active
			FROM
				tl_version
			WHERE
				fromTable = ?
			AND
				pid = ?
			ORDER BY
				version DESC
		')->execute($this->strTable, $this->intId);

		if(!$objVersion->numRows)
			return;
		
		return $objVersion;
	}
	
	public function checkEditable() {
		if($this->isEditable())
			return;
			
		$this->log('Table ' . $this->strTable . ' is not editable', 'DC_Table edit()', TL_ERROR);
		$this->redirect('contao/main.php?act=error');
	}
	
	public function isEditable() {
		return !$GLOBALS['TL_DCA'][$this->strTable]['config']['notEditable'];
	}
	
	// calculates all fields which could have a subpalette
	protected function calculateSelectors() {
		if(isset($this->arrSelectors))
			return;
		$this->arrSelectors = array();
		
		if($arrSubpalettes = $GLOBALS['TL_DCA'][$this->strTable]['subpalettes'])
			$this->calculateSelectorsHelper($arrSubpalettes);
	}
	
	private function calculateSelectorsHelper($arrSubpalettes) {
		if(!$arrSubpalettes)
			return;
			
		foreach($arrSubpalettes as $strField => $varSubpalette) {
			if(isset($this->arrFields[$strField]))
				$this->arrSelectors[$strField] = true; // mark field
			if(!is_array($varSubpalette))
				continue;
				
			foreach($varSubpalette as $arrNested)
				if(is_array($arrNested))
					$this->calculateSelectorsHelper($arrNested['subpalettes']);
		}
	}
	
	protected function calculatePalettes() {
		$this->arrSubpalettes = array();
		$this->arrRootPalette = array();
		$this->arrProcessed = array();
		$this->arrWidgets = array();
		$this->blnCreateNewVersion = false;
		
		$arrStack = count($GLOBALS['TL_DCA'][$this->strTable]['subpalettes'])
			? array($GLOBALS['TL_DCA'][$this->strTable]['subpalettes'])
			: array();
		
		foreach(trimsplit(';', $this->selectRootPalette()) as $strPalette) {
			if($strPalette[0] == '{')
				list($strLegend, $strPalette) = explode(',', $strPalette, 2);
				
			$arrPalette = array();
			$this->parsePalette($strPalette, $arrPalette, $arrStack);
			
			if($arrPalette) {
				$this->arrRootPalette[] = array(
					'legend' => $strLegend,
					'palette' => $arrPalette
				);
			}
		}
		
		return $this->blnCreateNewVersion;
	}
	
	/**
	 * @param unknown_type $strPalette
	 * 		the palette string to parse
	 * @param array $arrPalette
	 * 		the field stack of the current palette (to inline subpalettes with uneditable selector)
	 * @param array $arrStack
	 * 		the context stack of subpalettes
	 * @return string|string
	 */
	protected function parsePalette($strPalette, array &$arrPalette, array &$arrStack) {		
		if(!$strPalette)
			return;
		$intSize = count($arrStack);
		
		foreach(trimsplit(',', $strPalette) as $strField) {
			if(!$strField)
				continue;
			
			if(isset($this->arrFields[$strField])) {
				$this->processInput($strField);
				$arrPalette[] = $strField;
				$arrSubpalette = array();
				$this->parsePalette($this->getSubpalette($strField, $arrStack), $arrSubpalette, $arrStack);
				if($arrSubpalette) {
					$arrPalette[] = &$arrSubpalette;
					if($this->arrSubpalettes[$strField])
						$this->arrSubpalettes[$strField] = &$arrSubpalette;
					unset($arrSubpalette);
				}
			} else // selector field not editable, inline editable fields of active subpalette
				$this->parsePalette($this->getSubpalette($strField, $arrStack), $arrPalette, $arrStack);
			
			if($intSize !== count($arrStack))
				array_pop($arrStack);
		}
	}
	
	/**
	 * @param unknown_type $strField
	 * 		the selector of the subpalette in question
	 * @param array $arrPalette
	 * 		an existing field stack
	 * @param array $arrStack
	 * 		context stack of subpalettes
	 */
	protected function getSubpalette($strField, array &$arrStack) {
		if($this->arrSubpalettes[$strField]) {
			if($GLOBALS['TL_CONFIG']['debugMode'])
				throw new Exception("[DCA Config Error] Recursive subpalette detected. Involved field: [$strField]");
			return;
		}
		
		for($i = count($arrStack) - 1; $i > -1; $i--) {
			$varSubpalette = $arrStack[$i][$strField];
			
			// switch by selector value
			if(isset($varSubpalette)) {
				$this->arrSubpalettes[$strField] = true; // mark for dynamic reload
				if(is_array($varSubpalette))
					$varSubpalette = $varSubpalette[$this->objActiveRecord->$strField];
				elseif($this->objActiveRecord->$strField) // old style
					return $varSubpalette;
				else
					continue;
			} else {
				$varSubpalette = $arrStack[$i][$strField . '_' . $this->objActiveRecord->$strField];
			}
			
			if(is_string($varSubpalette)) { // simple subpalette
				return $varSubpalette;
			} elseif(is_array($varSubpalette)) {
				if(is_array($varSubpalette['subpalettes']))
					$arrStack[] = $varSubpalette['subpalettes'];
				return $varSubpalette['palette'];
			}
		}
	}

	protected function selectRootPalette() {
		$arrPalettes = $GLOBALS['TL_DCA'][$this->strTable]['palettes'];
		$arrSelectors = $arrPalettes['__selector__'];
		
		if(!$arrSelectors)
			return $arrPalettes['default'];
		
		$arrKeys = array();
		foreach($arrSelectors as $strSelector) {
			$this->processInput($strSelector);
			$varValue = $this->objActiveRecord->$strSelector;
			
			if(!strlen($varValue))
				continue;
			
			// !!! DO NOT USE $this->arrFields, it could be an excluded or unselected field
			if($GLOBALS['TL_DCA'][$this->strTable]['fields'][$strSelector]['inputType'] == 'checkbox'
			&& !$GLOBALS['TL_DCA'][$this->strTable]['fields'][$strSelector]['eval']['multiple'])
				$arrKeys[] = $strSelector;
			else
				$arrKeys[] = $varValue;
		}

		// Build possible palette names from the selector values
		if(!$arrKeys)
			return $arrPalettes['default'];
		elseif(count($arrKeys) > 1)
			$arrKeys = $this->combiner($arrKeys);
			
		// Get an existing palette
		foreach($arrKeys as $strKey)
			if(strlen($arrPalettes[$strKey]))
				return $arrPalettes[$strKey];
		
		return $arrPalettes['default'];
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
	
//	public function isValidOption($strField, $strKey) {
//		$this->createWidgets(array($strField));
//		return !isset($this->arrWidgetConfigs[$strField]['options'])
//			|| isset($this->arrWidgetConfigs[$strField]['options'][$strKey]);
//	}
	
}
