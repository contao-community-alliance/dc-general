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
class GeneralView_Default extends Controller implements InterfaceGeneralView
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
        return $this->notImplMsg;
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

        $objTemplate = new BackendTemplate('be_general_edit');
        $objTemplate->setData(array(
            'fieldsets' => $objPaletteBuilder->generateFieldsets('be_general_field', array()),
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

    public function show(DC_General $objDcGeneral)
    {
        return $this->notImplMsg;
    }

    public function showAll(DC_General $objDcGeneral)
    {
        $objView = new ViewBuilder($objDcGeneral);
        
        return $objView->listView();
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