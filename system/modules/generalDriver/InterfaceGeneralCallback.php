<?php if (!defined('TL_ROOT')) die('You can not access this file directly!');

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
 * @copyright  MEN AT WORK 2012
 * @package    generalDriver
 * @license    GNU/LGPL
 * @filesource
 */
interface InterfaceGeneralCallback
{

    /**
     * Set the DC
     *
     * @param DC_General $objDC
     */
    public function setDC($objDC);

    /**
     * Get the DC
     *
     * @return DC_General
     */
    public function getDC();

    /**
     * Exectue a callback
     *
     * @param array $varCallbacks
     * @return array
     */
    public function executeCallbacks($varCallbacks);

    /**
     * Call the customer label callback
     *
     * @param InterfaceGeneralModel $objModelRow
     * @param string $mixedLabel
     * @param array $args
     * @return string
     */
    public function labelCallback(InterfaceGeneralModel $objModelRow, $mixedLabel, $args);

    /**
     * Call the button callback for the regular operations
     *
     * @param InterfaceGeneralModel $objModelRow
     * @param string $strLabel
     * @param string $strTitle
     * @param array $arrAttributes
     * @param string $strTable
     * @param array $arrRootIds
     * @param array $arrChildRecordIds
     * @param boolean $blnCircularReference
     * @param string $strPrevious
     * @param string $strNext
     *
     * @return string|null
     */
    public function buttonCallback($objModelRow, $arrOperation, $strLabel, $strTitle, $arrAttributes, $strTable, $arrRootIds, $arrChildRecordIds, $blnCircularReference, $strPrevious, $strNext);

    /**
     * Call the button callback for the global operations
     *
     * @param str $strLabel
     * @param str $strTitle
     * @param array $arrAttributes
     * @param string $strTable
     * @param array $arrRootIds
     *
     * @return string|null
     */
    public function globalButtonCallback($strLabel, $strTitle, $arrAttributes, $strTable, $arrRootIds);

    /**
     * Call the header callback
     *
     * @param array $arrAdd
     * @return array|null
     */
    public function headerCallback($arrAdd);

    /**
     * Call the child record callback
     *
     * @param array $arrRow
     * @return string|null
     */
    public function childRecordCallback($arrRow);

    /**
     * Call the options callback for given the fields
     *
     * @param string $strField
     * @return array|null
     */
    public function optionsCallback($strField);

    /**
     * Call the onrestore callback
     *
     * @param int $intID ID of current dataset
     * @param string $strTable Name of current Table
     * @param array $arrData Array with all Data
     * @param int $intVersion Version which was restored
     */
    public function onrestoreCallback($intID, $strTable, $arrData, $intVersion);

    /**
     * Call the load callback
     *
     * @param string $strField
     * @param mixed $varValue
     * @return mixed|null
     */
    public function loadCallback($strField, $varValue);

    /**
     * Call onload_callback (e.g. to check permissions)
     *
     * @param string $strTable name of current table
     */
    public function onloadCallback();

    /**
     * Call the group callback
     *
     * @param type $group
     * @param type $mode
     * @param type $field
     * @param InterfaceGeneralModel $objModelRow
     *
     * @return type
     */
    public function groupCallback($group, $mode, $field, $objModelRow);

    /**
     * Call the save callback for a widget
     *
     * @param array $arrConfig Configuration of the widget
     * @param mixed $varNew New Value
     *
     * @return mixed
     */
    public function saveCallback($arrConfig, $varNew);

    /**
     * Call ondelete_callback
     *
     * @return void
     */
    public function ondeleteCallback();

    /**
     * Call the onsubmit_callback
     *
     * @return void
     */
    public function onsubmitCallback();

    /**
     * Call the oncreate_callback
     *
     * @param mixed $insertID The id from the new record
     * @param array $arrRecord the new record
     *
     * @return void
     */
    public function oncreateCallback($insertID, $arrRecord);


    /**
     * Get the current pallette
     *
     * @param DC_General $objDC
     * @param array $arrPalette
     */
    public function parseRootPaletteCallback($arrPalette);
}

?>
