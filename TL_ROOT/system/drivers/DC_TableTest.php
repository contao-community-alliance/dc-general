<?php

require_once 'DC_General.php';
require_once 'DC_Table.php';

/**
 * Contao Open Source CMS
 * Copyright (C) 2005-2010 Leo Feyer
 *
 * Formerly known as TYPOlight Open Source CMS.
 *
 * This program is free software: you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation, either
 * version 3 of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this program. If not, please visit the Free
 * Software Foundation website at <http://www.gnu.org/licenses/>.
 *
 * PHP version 5
 * @copyright  MEN AT WORK 2012
 * @copyright  Oliver Hoff 2012
 * @package    drivers
 * @license    GNU/LGPL
 * @filesource
 */
class DC_TableTest extends DC_General implements listable
{

    protected $strMode = false; // edit, editAll, overrideAll
    protected $objDCTable;

    public function __construct($strTable)
    {
        parent::__construct($strTable, null, false);

        $this->objDCTable = new DC_Table($strTable);
        $this->arrDCA = $GLOBALS['TL_DCA'][$strTable];

        $this->addAdminFields();
    }

    public function __get($strKey)
    {
        switch ($strKey)
        {
            case 'treeView':
                return in_array($this->arrDCA['list']['sorting']['mode'], array(5, 6));
                break;

            default:
                if ($this->strMode === false)
                {
                    return $this->objDCTable->$strKey;
                }
                $varReturn = parent::__get($strKey);
                return $varReturn === null ? $this->objDCTable->$strKey : $varReturn;
                break;
        }
    }

    public function edit($intID = null, $strSelector = null)
    {
        $this->strMode = 'edit';
        return $this->generateEdit($intID, $strSelector);
    }

    public function editAll($intID = false, $strSelector = false)
    {
        $this->strMode = 'editAll';
        $this->checkEditable();
        $arrIDs = $this->getIDs();

        if (!$this->loadEditableFields(true))
            return $this->generateFieldsForm();
        if (!$this->hasEditableFields())
            return $this->redirect($this->getReferer());

        if ($strSelector && $intID)
            return isset($arrIDs[$intID]) ? $this->edit($intID, $strSelector) : '';

        $arrPBs = array();
        foreach ($arrIDs as $intID => &$blnCreateNewVersion)
        {
            $this->intId = $intID;
            $this->setWidgetID($this->intId);
            $this->arrWidgets = array();
            $this->arrProcessed = array();

            $this->loadActiveRecord($this->strTable, $this->intId);
            $this->createInitialVersion($this->strTable, $intID);
            $this->blnCreateNewVersion = false;

            $objPB = new PaletteBuilder($this);

            $blnCreateNewVersion = $this->blnCreateNewVersion;

            if ($objPB->isEmpty())
                continue;

            $arrPBs[$intID] = array(
                'title' => $this->objActiveRecord->title ? $this->objActiveRecord->title . ' (ID ' . $intID . ')' : 'ID ' . $intID,
                'widgets' => $this->arrWidgets,
                'pb' => $objPB
            );
        }

        $this->loadDefaultButtons();

        if ($this->blnSubmitted && !$this->noReload)
        {
            foreach ($arrIDs as $intID => $blnCreateNewVersion)
            {
                $this->intId = $intID;
                $this->loadActiveRecord($this->strTable, $this->intId, true); // needed for consistence with onsubmit_callback
                $this->executeCallbacks($this->arrDCA['config']['onsubmit_callback'], $this);

                // Create a new version
                if ($blnCreateNewVersion && !$this->blnAutoSubmitted)
                {
                    $this->createNewVersion($this->strTable, $intID);
                    $this->executeCallbacks($this->arrDCA['config']['onversion_callback'], $this->strTable, $this->intId, $this);
                    $this->log(sprintf('A new version of %s ID %s has been created', $this->strTable, $this->intId), 'DC_Table editAll()', TL_GENERAL);
                }

                // Set current timestamp (-> DO NOT CHANGE ORDER version - timestamp)
                $this->updateTimestamp($this->strTable, $this->intId);
            }

            foreach ($this->getButtonsDefinition() as $strButtonKey => $arrCallback)
            {
                if (isset($_POST[$strButtonKey]))
                {
                    $this->import($arrCallback[0]);
                    $this->{$arrCallback[0]}->{$arrCallback[1]}($this);
                }
            }

            $this->reload();
        }

        $strTemplate = $this->getTemplate('be_tableextended_field');
        foreach ($arrPBs as $intID => &$arrPalette)
        {
            $this->intId = $intID;
            $this->setWidgetID($this->intId);
            $this->loadActiveRecord($this->strTable, $this->intId, $this->blnSubmitted); // do not use cache if form was submitted
            $this->preloadTinyMce();
            $this->arrWidgets = $arrPalette['widgets'];
            $arrPalette['palette'] = $arrPalette['pb']->generateFieldsets($strTemplate, $this->arrStates);
        }

        $objTemplate = new BackendTemplate('be_tableextended_editall');

        $strTableEsc = specialchars($this->strTable);
        $objTemplate->setData(array(
            'rootPalettes' => $arrPBs,
            'oldBE' => $GLOBALS['TL_CONFIG']['oldBeTheme'],
            'table' => $this->strTable,
            'tableEsc' => $strTableEsc,
            'subHeadline' => sprintf($GLOBALS['TL_LANG']['MSC']['all_info'], $strTableEsc),
            'action' => ampersand($this->Environment->request, true),
            'enctype' => $this->blnUploadable ? 'multipart/form-data' : 'application/x-www-form-urlencoded',
//			'onsubmit'		=> implode(' ', $this->onsubmit),
            'error' => $this->noReload,
            'buttons' => $this->getButtonLabels()
        ));

        return $objTemplate->parse();
    }

    public function overrideAll()
    {
        $this->strMode = 'overrideAll';
        $this->checkEditable();
        $arrIDs = $this->getIDs($intRootID);
        $this->setWidgetID($intRootID);

        if (!$this->loadEditableFields(true))
            return $this->generateFieldsForm();
        if (!$this->hasEditableFields())
            return $this->redirect($this->getReferer());

        foreach (array_keys($this->arrFields) as $strField)
        {
            $arrConfig                       = &$this->arrDCA['fields'][$strField];
            $arrConfig['update']             = ($arrConfig['inputType'] == 'checkbox' || $arrConfig['inputType'] == 'checkboxWizard')
                    && $arrConfig['eval']['multiple'];
            $arrConfig['eval']['alwaysSave'] = true;
            unset($arrConfig['eval']['submitOnChange']);
            unset($arrConfig);
        }

        $this->loadDefaultButtons();

        if ($this->blnSubmitted)
        {
            end($arrIDs); // traverse array backwards to keep calculated palette of first entry
            while (null !== ($intID = key($arrIDs)))
            {
                $this->intId = $intID;
                $this->arrWidgets = array();
                $this->arrProcessed = array();

                $this->loadActiveRecord($this->strTable, $this->intId);
                $this->createInitialVersion($this->strTable, $intID);
                $this->blnCreateNewVersion = false;

                $objPB = new PaletteBuilder($this);

                $arrIDs[$intID] = $this->blnCreateNewVersion;
                prev($arrIDs);
            }

            if (!$this->noReload)
            {
                foreach ($arrIDs as $intID => $blnCreateNewVersion)
                {
                    $this->intId = $intID;

                    $this->loadActiveRecord($this->strTable, $this->intId, true); // needed for consistence with onsubmit_callback
                    $this->executeCallbacks($this->arrDCA['config']['onsubmit_callback'], $this);

                    // Create a new version
                    if ($blnCreateNewVersion)
                    {
                        $this->createNewVersion($this->strTable, $intID);
                        $this->executeCallbacks($this->arrDCA['config']['onversion_callback'], $this->strTable, $this->intId, $this);
                        $this->log(sprintf('A new version of record ID %s (table %s) has been created', $this->intId, $this->strTable), 'DC_Table editAll()', TL_GENERAL);
                    }

                    // Set current timestamp (-> DO NOT CHANGE ORDER version - timestamp)
                    $this->updateTimestamp($this->strTable, $this->intId);
                }

                foreach ($this->getButtonsDefinition() as $strButtonKey => $arrCallback)
                {
                    if (isset($_POST[$strButtonKey]))
                    {
                        $this->import($arrCallback[0]);
                        $this->{$arrCallback[0]}->{$arrCallback[1]}($this);
                    }
                }

                $this->reload();
            }
        }
        else
        {
            $this->intId = $intRootID;

            $this->loadActiveRecord($this->strTable, $this->intId);
            $this->createInitialVersion($this->strTable, $intRootID);

            $objPB = new PaletteBuilder($this);
        }

        $this->preloadTinyMce();
        $objTemplate = new BackendTemplate('be_tableextended_overrideall');

        $strTableEsc = specialchars($this->strTable);
        $objTemplate->setData(array(
            'fieldsets' => $objPB->generateFieldsets($this->getTemplate('be_tableextended_field'), $this->arrStates),
            'oldBE' => $GLOBALS['TL_CONFIG']['oldBeTheme'],
            'subHeadline' => sprintf($GLOBALS['TL_LANG']['MSC']['all_info'], $strTableEsc),
            'table' => $this->strTable,
            'tableEsc' => $strTableEsc,
            'action' => ampersand($this->Environment->request, true),
            'enctype' => $this->blnUploadable ? 'multipart/form-data' : 'application/x-www-form-urlencoded',
//			'onsubmit'		=> implode(' ', $this->onsubmit),
            'error' => $this->noReload,
            'buttons' => $this->getButtonLabels()
        ));

        return $objTemplate->parse();
    }

    protected function updateTimestamp($strTable, $intID)
    {
        $this->Database->prepare(
                'UPDATE ' . $strTable . ' SET tstamp = ? WHERE id = ?'
        )->execute(time(), $intID);
    }

    protected function isUniqueValue($strTable, $strField, $varNew)
    {
        $objUnique = $this->Database->prepare('
			SELECT	*
			FROM	' . $strTable . '
			WHERE	' . $strField . ' = ?
		')->execute($varNew);

        return !$objUnique->numRows;
    }

    protected function resetFallback($strField, $strTable)
    {
        $this->Database->query('UPDATE ' . $strTable . ' SET ' . $strField . ' = \'\'');
    }

    protected function storeValue($strField, $strTable, $intID, $varNew)
    {
        $objUpdateStmt = $this->Database->prepare(
                        'UPDATE ' . $strTable . ' SET ' . $strField . ' = ? WHERE id = ?'
                )->execute(array($varNew, $intID));
        return $objUpdateStmt->affectedRows;
    }

    protected function loadActiveRecord($strTable, $intID, $blnDontUseCache = false)
    {
        $strMethod = $blnDontUseCache ? 'executeUncached' : 'execute';
        $objRow    = $this->Database->prepare('
			SELECT	*
			FROM	' . $strTable . '
			WHERE	id = ?
		')->limit(1)->$strMethod($intID);

        if ($objRow->numRows)
            return $this->objActiveRecord = $objRow;

        $this->log('Could not load record ID "' . $intID . '" of table "' . $strTable . '"!', 'DC_TableExtended::loadActiveRecord()', TL_ERROR);
        $this->redirect('contao/main.php?act=error');
    }

    protected function loadDefaultButtons()
    {
        parent::loadDefaultButtons();

        if ($this->strMode != 'edit')
            return;

        if ($this->Input->get('s2e'))
            $this->arrDCA['buttons']['saveNedit'] = array('TableExtendedButtons', 'saveAndEdit');

        if ($this->arrDCA['list']['sorting']['mode'] == 4
                || strlen($this->ptable)
                || $this->arrDCA['config']['switchToEdit'])
            $this->arrDCA['buttons']['saveNback'] = array('TableExtendedButtons', 'saveAndBack');

        if (!$this->arrDCA['config']['closed'])
            $this->arrDCA['buttons']['saveNcreate'] = array('TableExtendedButtons', 'saveAndCreate');
    }

    protected function setVersion($strTable, $intID, $strVersion)
    {
        $objData = $this->Database->prepare('
			SELECT	*
			FROM	tl_version
			WHERE	fromTable = ?
			AND		pid = ?
			AND		version = ?
		')->limit(1)->execute($strTable, $intID, $strVersion);

        if (!$objData->numRows)
            return;

        $arrData = deserialize($objData->data);

        if (!is_array($arrData))
            return;

        $this->Database->prepare('UPDATE ' . $objData->fromTable . ' %s WHERE id = ?')->set($arrData)->execute($this->intId);
        $this->Database->prepare('UPDATE tl_version SET active=\'\' WHERE pid = ?')->execute($this->intId);
        $this->Database->prepare('UPDATE tl_version SET active = 1 WHERE pid = ? AND version = ?')->execute($this->intId, $strVersion);

        $this->log(sprintf('Version %s of record ID %s (table %s) has been restored', $strVersion, $this->intId, $this->strTable), 'DC_Table edit()', TL_GENERAL);

        $this->executeCallbacks(
                $this->arrDCA['config']['onrestore_callback'], $this->intId, $this->strTable, $arrData, $strVersion
        );
    }

    protected function getVersions($strTable, $intID)
    {
        if (!$this->arrDCA['config']['enableVersioning'])
            return;

        $objVersion = $this->Database->prepare('
			SELECT	tstamp, version, username, active
			FROM	tl_version
			WHERE	fromTable = ?
			AND		pid = ?
			ORDER BY version DESC
		')->execute($strTable, $intID);

        if (!$objVersion->numRows)
            return;

        return $objVersion->fetchAllAssoc();
    }

    protected function addAdminFields()
    {
        if (!$this->User->isAdmin)
            return;

        if (!isset($this->arrDCA['fields']['sorting']) && $this->Database->fieldExists('sorting', $this->strTable))
        {
            $this->arrDCA['fields']['sorting'] = array(
                'label' => &$GLOBALS['TL_LANG']['MSC']['sorting'],
                'inputType' => 'text',
                'eval' => array('rgxp' => 'digit')
            );
        }

        if (!isset($this->arrDCA['fields']['pid']) && $this->Database->fieldExists('pid', $this->strTable))
        {
            $this->arrDCA['fields']['pid'] = array(
                'label' => &$GLOBALS['TL_LANG']['MSC']['pid'],
                'inputType' => 'text',
                'eval' => array('rgxp' => 'digit')
            );
        }
    }

    // get current IDs from session
    protected function getIDs(&$intRootID = null)
    {
        $arrSession = $this->Session->getData();
        $arrIDs     = $arrSession['CURRENT']['IDS'];

        if (!$arrIDs)
            $this->redirect($this->getReferer());

        $intRootID = reset($arrIDs);
        return array_flip($arrIDs);
    }

    protected function generateFieldsForm()
    {
        $arrFields = array();
        foreach (array_keys($this->arrFields) as $strField)
        {
            $arrConfig            = $this->getFieldDefinition($strField);
            $strField             = specialchars($strField);
            $arrFields[$strField] = $arrConfig['label'][0] ? $arrConfig['label'][0] : ($GLOBALS['TL_LANG']['MSC'][$strField][0] ? $GLOBALS['TL_LANG']['MSC'][$strField][0] : $strField);
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

    public function __call($strMethod, $arrArgs)
    {
        switch ($strMethod)
        {
            case 'cutAll':
            case 'copyAll':
            case 'deleteAll':
            case 'deleteChilds':
            case 'ajaxTreeView':
                return $this->delegateToDCTable($strMethod, $arrArgs);
                break;
        }
    }

    public function show()
    {
        $arrArgs = func_get_args();
        return $this->delegateToDCTable('show', $arrArgs);
    }

    public function showAll()
    {
        $arrArgs = func_get_args();
        return $this->delegateToDCTable('showAll', $arrArgs);
    }

    public function create()
    {
        $arrArgs = func_get_args();
        return $this->delegateToDCTable('create', $arrArgs);
    }

    public function cut()
    {
        $arrArgs = func_get_args();
        return $this->delegateToDCTable('cut', $arrArgs);
    }

    public function cutAll()
    {
        $arrArgs = func_get_args();
        return $this->delegateToDCTable('cutAll', $arrArgs);
    }

    public function copy()
    {
        $arrArgs = func_get_args();
        return $this->delegateToDCTable('copy', $arrArgs);
    }

    public function copyAll()
    {
        $arrArgs = func_get_args();
        return $this->delegateToDCTable('copyAll', $arrArgs);
    }

    public function delete()
    {
        $arrArgs = func_get_args();
        return $this->delegateToDCTable('delete', $arrArgs);
    }

    public function deleteAll()
    {
        $arrArgs = func_get_args();
        return $this->delegateToDCTable('deleteAll', $arrArgs);
    }

    public function deleteChilds()
    {
        $arrArgs = func_get_args();
        return $this->delegateToDCTable('deleteChilds', $arrArgs);
    }

    public function undo()
    {
        $arrArgs = func_get_args();
        return $this->delegateToDCTable('undo', $arrArgs);
    }

    public function move()
    {
        $arrArgs = func_get_args();
        return $this->delegateToDCTable('move', $arrArgs);
    }

    protected function delegateToDCTable($strMethod, $arrArgs)
    {
        return call_user_func_array(array($this->objDCTable, $strMethod), $arrArgs);
    }

    public function getPalette()
    {
        return "";
    }

}

?>