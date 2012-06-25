<?php

if (!defined('TL_ROOT'))
    die('You can not access this file directly!');

/**
 * Contao Open Source CMS
 * Copyright (C) 2005-2011 Leo Feyer
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
 * @see InterfaceGeneralView
 * @copyright  MEN AT WORK 2012
 * @package    generalDriver
 * @license    GNU/LGPL
 * @filesource
 */
class GeneralViewDefault extends Controller implements InterfaceGeneralView
{

    protected $notImplMsg = "<div style='text-align:center; font-weight:bold; padding:40px;'>This function/view is not implemented.</div>";

    public function copy(DC_General $objDcGeneral)
    {
        return $this->notImplMsg;
    }

    public function copyAll(DC_General $objDcGeneral)
    {
        return $this->notImplMsg;
    }

    public function create(DC_General $objDcGeneral)
    {
        return $this->edit($objDcGeneral);
    }

    public function cut(DC_General $objDcGeneral)
    {
        return $this->notImplMsg;
    }

    public function cutAll(DC_General $objDcGeneral)
    {
        return $this->notImplMsg;
    }

    public function delete(DC_General $objDcGeneral)
    {
        return $this->notImplMsg;
    }

    public function edit(DC_General $objDcGeneral)
    {
        $objPaletteBuilder = new PaletteBuilder($objDcGeneral);

        $objTemplate = new BackendTemplate('dcbe_general_edit');
        $objTemplate->setData(array(
            'fieldsets' => $objPaletteBuilder->generateFieldsets('dcbe_general_field', array()),
            'oldBE' => $GLOBALS['TL_CONFIG']['oldBeTheme'],
            'versions' => $objDcGeneral->getDataProvider()->getVersions($objDcGeneral->getId()),
            'subHeadline' => sprintf($GLOBALS['TL_LANG']['MSC']['editRecord'], $objDcGeneral->getId() ? 'ID ' . $objDcGeneral->getId() : ''),
            'table' => $objDcGeneral->getTable(),
            'enctype' => $objDcGeneral->isUploadable() ? 'multipart/form-data' : 'application/x-www-form-urlencoded',
            //'onsubmit' => implode(' ', $this->onsubmit),
            'error' => $this->noReload,
            'buttons' => $objDcGeneral->getButtonLabels()
        ));

        // Set JS
        $GLOBALS['TL_JAVASCRIPT'][] = 'system/modules/generalDriver/html/js/generalDriver.js';

        return $objTemplate->parse();

        // Old stuff and so -----------
//        if ($intID && $strSelector)
//        {
//            return $objPaletteBuilder->generateAjaxPalette(
//                            $strSelector, $strSelector . '--' . $this->varWidgetID, $this->getTemplate('be_tableextended_field')
//            );
//        }
//
//        $this->loadDefaultButtons();
//        $this->blnSubmitted && !$this->noReload && $this->executeCallbacks($this->arrDCA['config']['onsubmit_callback'], $this);
//
//        if ($this->blnSubmitted && !$this->noReload)
//        {
//            // Save the current version
//            if ($this->blnCreateNewVersion && !$this->blnAutoSubmitted)
//            {
//                $this->createNewVersion($this->strTable, $this->intId);
//                $this->executeCallbacks($this->arrDCA['config']['onversion_callback'], $this->strTable, $this->intId, $this);
//                $this->log(sprintf('A new version of %s ID %s has been created', $this->strTable, $this->intId), 'DC_Table edit()', TL_GENERAL);
//            }
//
//            // Set the current timestamp (-> DO NOT CHANGE THE ORDER version - timestamp)
//            $this->updateTimestamp($this->strTable, $this->intId);
//
//            if (!$this->blnAutoSubmitted)
//            {
//                foreach ($this->getButtonsDefinition() as $strButtonKey => $arrCallback)
//                {
//                    if (isset($_POST[$strButtonKey]))
//                    {
//                        $this->import($arrCallback[0]);
//                        $this->{$arrCallback[0]}->{$arrCallback[1]}($this);
//                    }
//                }
//            }
//
//            $this->reload();
//        }
//
//        version_compare(VERSION, '2.10', '<') || $this->preloadTinyMce();
//
//        $objTemplate = new BackendTemplate('be_tableextended_edit');
    }

    public function move(DC_General $objDcGeneral)
    {
        return $this->notImplMsg;
    }

    /**
     * Show Informations about a data set
     * 
     * @param DC_General $objDcGeneral
     * @return String 
     */
    public function show(DC_General $objDcGeneral)
    {
        // Init
        $arrDCA = $objDcGeneral->getDCA();
        $fields = array();
        $allowedFields = array('pid', 'sorting', 'tstamp');
        $arrFieldValues = array();
        $arrFieldLabels = array();

        // Get fields
        $objModel = $objDcGeneral->getCurrentModel();
        foreach ($objModel as $key => $value)
        {
            $fields[] = $key;
        }

        // Get allowed fieds from dca
        if (is_array($arrDCA['fields']))
        {
            $allowedFields = array_unique(array_merge($allowedFields, array_keys($arrDCA['fields'])));
        }

        $fields = array_intersect($allowedFields, $fields);


        // Show all allowed fields
        foreach ($fields as $strFieldName)
        {
            if (!in_array($strFieldName, $allowedFields)
                    || $arrDCA['fields'][$strFieldName]['inputType'] == 'password'
                    || $arrDCA['fields'][$strFieldName]['eval']['doNotShow']
                    || $arrDCA['fields'][$strFieldName]['eval']['hideInput'])
            {
                continue;
            }

            // Special treatment for table tl_undo
            if ($objDcGeneral->getTable() == 'tl_undo' && $strFieldName == 'data')
            {
                continue;
            }

            // Load value from model
            $value = deserialize($objModel->getProperty($strFieldName));
            
            // Get the field value
            if (isset($arrDCA['fields'][$strFieldName]['foreignKey']))
            {
                $temp = array();
                $chunks = explode('.', $arrDCA['fields'][$strFieldName]['foreignKey'], 2);

                // ToDo: SH: todo :P
                
                foreach ((array) $value as $v)
                {
//                    $objKey = $this->Database->prepare("SELECT " . $chunks[1] . " AS value FROM " . $chunks[0] . " WHERE id=?")
//                            ->limit(1)
//                            ->execute($v);
//
//                    if ($objKey->numRows)
//                    {
//                        $temp[] = $objKey->value;
//                    }
                }

//                $row[$i] = implode(', ', $temp);
            }
            // Decode array
            else if (is_array($value))
            {
                foreach ($value as $kk => $vv)
                {
                    if (is_array($vv))
                    {
                        $vals       = array_values($vv);
                        $value[$kk] = $vals[0] . ' (' . $vals[1] . ')';
                    }
                }

                $arrFieldValues[$strFieldName] = implode(', ', $value);
            }
            // Date Formate
            else if ($arrDCA['fields'][$strFieldName]['eval']['rgxp'] == 'date')
            {
                $arrFieldValues[$strFieldName] = $this->parseDate($GLOBALS['TL_CONFIG']['dateFormat'], $value);
            }
            // Date Formate
            else if ($arrDCA['fields'][$strFieldName]['eval']['rgxp'] == 'time')
            {
                $arrFieldValues[$strFieldName] = $this->parseDate($GLOBALS['TL_CONFIG']['timeFormat'], $value);
            }
             // Date Formate
            else if ($arrDCA['fields'][$strFieldName]['eval']['rgxp'] == 'datim' || in_array($arrDCA['fields'][$strFieldName]['flag'], array(5, 6, 7, 8, 9, 10)) || $strFieldName == 'tstamp')
            {
                $arrFieldValues[$strFieldName] = $this->parseDate($GLOBALS['TL_CONFIG']['datimFormat'], $value);
            }
            else if ($arrDCA['fields'][$strFieldName]['inputType'] == 'checkbox' && !$arrDCA['fields'][$strFieldName]['eval']['multiple'])
            {
                $arrFieldValues[$strFieldName] = strlen($value) ? $GLOBALS['TL_LANG']['MSC']['yes'] : $GLOBALS['TL_LANG']['MSC']['no'];
            }
            else if ($arrDCA['fields'][$strFieldName]['inputType'] == 'textarea' && ($arrDCA['fields'][$strFieldName]['eval']['allowHtml'] || $arrDCA['fields'][$strFieldName]['eval']['preserveTags']))
            {
                $arrFieldValues[$strFieldName] = nl2br_html5(specialchars($value));
            }
            else if (is_array($arrDCA['fields'][$strFieldName]['reference']))
            {
                $arrFieldValues[$strFieldName] = isset($arrDCA['fields'][$strFieldName]['reference'][$objModel->getProperty($strFieldName)]) ?
                        ((is_array($arrDCA['fields'][$strFieldName]['reference'][$objModel->getProperty($strFieldName)])) ?
                                $arrDCA['fields'][$strFieldName]['reference'][$objModel->getProperty($strFieldName)][0] :
                                $arrDCA['fields'][$strFieldName]['reference'][$objModel->getProperty($strFieldName)]) :
                        $objModel->getProperty($strFieldName);
            }
            else if (array_is_assoc($arrDCA['fields'][$strFieldName]['options']))
            {
                $arrFieldValues[$strFieldName] = $arrDCA['fields'][$strFieldName]['options'][$objModel->getProperty($strFieldName)];
            }
            else
            {
                $arrFieldValues[$strFieldName] = $objModel->getProperty($strFieldName);
            }

            // Label
            if (count($arrDCA['fields'][$strFieldName]['label']))
            {
                $arrFieldLabels[$strFieldName] = is_array($arrDCA['fields'][$strFieldName]['label']) ? $arrDCA['fields'][$strFieldName]['label'][0] : $arrDCA['fields'][$strFieldName]['label'];
            }
            else
            {
                $arrFieldLabels[$strFieldName] = is_array($GLOBALS['TL_LANG']['MSC'][$strFieldName]) ? $GLOBALS['TL_LANG']['MSC'][$strFieldName][0] : $GLOBALS['TL_LANG']['MSC'][$strFieldName];
            }

            if (!strlen($arrFieldLabels[$strFieldName]))
            {
                $arrFieldLabels[$strFieldName] = $strFieldName;
            }
        }

        $objTemplate = new BackendTemplate("dcbe_general_show");
        $objTemplate->headline = sprintf($GLOBALS['TL_LANG']['MSC']['showRecord'], ($objDcGeneral->getId() ? 'ID ' . $objDcGeneral->getId() : ''));
        $objTemplate->arrFields = $arrFieldValues;
        $objTemplate->arrLabels = $arrFieldLabels;

        return $objTemplate->parse();
    }

    public function showAll(DC_General $objDcGeneral)
    {
        $arrDCA = $objDcGeneral->getDCA();
        
        $objView = new ViewBuilder($objDcGeneral);

        if ($arrDCA['list']['sorting']['mode'] == 4)
        {
            return $objView->panel() . $objView->parentView();
        }
        else
        {
            return $objView->panel() . $objView->listView();
        }        
    }

    public function undo(DC_General $objDcGeneral)
    {
        return $this->notImplMsg;
    }

    public function generateAjaxPalette(DC_General $objDcGeneral, $strSelector, $strInputName, $strFieldTemplate)
    {
        return $this->notImplMsg;
    }

}

?>