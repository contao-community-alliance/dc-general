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
class GeneralCallbackDefault extends System implements InterfaceGeneralCallback
{

    /**
     * The DC
     * @var DC_General 
     */
    private $objDC;

    /**
     * Set the DC
     * 
     * @param DC_General $objDC
     */
    public function setDC($objDC)
    {
        $this->objDC = $objDC;
    }
    

    /**
     * Exectue a callback
     * 
     * @param array $varCallbacks
     * @return array 
     */
    public function executeCallbacks($varCallbacks)
    {
        if ($varCallbacks === null)
        {
            return array();
        }

        if (is_string($varCallbacks))
        {
            $varCallbacks = $GLOBALS['TL_HOOKS'][$varCallbacks];
        }

        if (!is_array($varCallbacks))
        {
            return array();
        }

        $arrArgs    = array_slice(func_get_args(), 1);
        $arrResults = array();

        foreach ($varCallbacks as $arrCallback)
        {
            if (is_array($arrCallback))
            {
                $this->import($arrCallback[0]);
                $arrCallback[0] = $this->{$arrCallback[0]};
                $arrResults[]   = call_user_func_array($arrCallback, $arrArgs);
            }
        }

        return $arrResults;
    }

    /**
     * Call the customer label callback
     * 
     * @param InterfaceGeneralModel $objModelRow
     * @param string $mixedLabel
     * @param array $arrDCA
     * @param array $args
     * @return string 
     */
    public function labelCallback(InterfaceGeneralModel $objModelRow, $mixedLabel, $args)
    {
        // Load DCA
        $arrDCA = $this->objDC->getDCA();
        
        // Check Callback
        if (is_array($arrDCA['label_callback']))
        {
            $strClass  = $arrDCA['label_callback'][0];
            $strMethod = $arrDCA['label_callback'][1];

            $this->import($strClass);

            if (version_compare(VERSION, '2.10', '>'))
            {
                $mixedLabel = $this->$strClass->$strMethod($objModelRow->getPropertiesAsArray(), $mixedLabel, $this->objDC, $args);
            }
            else
            {
                $mixedLabel = $this->$strClass->$strMethod($objModelRow->getPropertiesAsArray(), $mixedLabel, $this->objDC);
            }
        }

        return $mixedLabel;
    }

    /**
     * Call the button callback for the regular operations
     * 
     * @param InterfaceGeneralModel $objModelRow
     * @param array $arrDCA
     * @param string $strLabel
     * @param string $strTitle
     * @param array $arrAttributes
     * @param string $strTable
     * @param array $arrRootIds
     * @param array $arrChildRecordIds
     * @param boolean $blnCircularReference
     * @param string $strPrevious
     * @param string $strNext
     * @return string|null 
     */
    public function buttonCallback($objModelRow, $strLabel, $strTitle, $arrAttributes, $strTable, $arrRootIds, $arrChildRecordIds, $blnCircularReference, $strPrevious, $strNext)
    {
        // Load DCA
        $arrDCA = $this->objDC->getDCA();

        // Check Callback
        if (is_array($arrDCA['button_callback']))
        {
            $strClass  = $arrDCA['button_callback'][0];
            $strMethod = $arrDCA['button_callback'][1];

            $this->import($strClass);

            return $this->$strClass->$strMethod(
                        $objModelRow->getPropertiesAsArray(), 
                        $arrDCA['href'], 
                        $strLabel, 
                        $strTitle, 
                        $arrDCA['icon'], 
                        $arrAttributes, 
                        $strTable, 
                        $arrRootIds, 
                        $arrChildRecordIds, 
                        $blnCircularReference, 
                        $strPrevious, 
                        $strNext
                    );
        }

        return null;
    }

    /**
     * Call the button callback for the global operations
     * 
     * @param array $arrDCA
     * @param str $strLabel
     * @param str $strTitle
     * @param array $arrAttributes
     * @param string $strTable
     * @param array $arrRootIds
     * @return string|null 
     */
    public function globalButtonCallback($strLabel, $strTitle, $arrAttributes, $strTable, $arrRootIds)
    {
        // Load DCA
        $arrDCA = $this->objDC->getDCA();

        // Check Callback
        if (is_array($arrDCA['button_callback']))
        {
            $strClass  = $arrDCA['button_callback'][0];
            $strMethod = $arrDCA['button_callback'][1];

            $this->import($strClass);
            return $this->$strClass->$strMethod($arrDCA['href'], $strLabel, $strTitle, $arrDCA['icon'], $arrAttributes, $strTable, $arrRootIds);
        }

        return NULL;
    }

    /**
     * Call the options callback for the fields
     * 
     * @param type $arrDCA
     * @return array|null 
     */
    public function optionsCallback()
    {
        // Load DCA
        $arrDCA = $this->objDC->getDCA();

        // Check Callback
        if (is_array($arrDCA['options_callback']))
        {
            $strClass  = $arrDCA['options_callback'][0];
            $strMethod = $arrDCA['options_callback'][1];

            $this->import($strClass);
            return $this->$strClass->$strMethod($this->objDC);
        }

        return null;
    }

    /**
     * Trigger the onrestore_callback
     * 
     * @param int $intID ID of current dataset
     * @param string $strTable Name of current Table
     * @param array $arrData Array with all Data
     * @param int $intVersion Version which was restored
     */
    public function onrestoreCallback($intID, $strTable, $arrData, $intVersion)
    {
        // Load DCA
        $arrDCA = $this->objDC->getDCA();

        // Check Callback
        if (is_array($arrDCA['config']['onrestore_callback']))
        {
            foreach ($arrDCA['config']['onrestore_callback'] as $callback)
            {
                if (is_array($callback))
                {
                    $this->import($callback[0]);                    
                    $this->$callback[0]->$callback[1]($intID, $strTable, $arrData, $intVersion);
                }
            }
        }
    }

    /**
     * Call onload_callback (e.g. to check permissions)
     * 
     * @param string $strTable name of current table
     */
    public function onloadCallback()
    {
        // Load DCA
        $arrDCA = $this->objDC->getDCA();

        // Check Callback
        if (is_array($arrDCA['config']['onload_callback']))
        {
            foreach ($arrDCA['config']['onload_callback'] as $callback)
            {
                if (is_array($callback))
                {
                    $this->import($callback[0]);
                    $this->$callback[0]->$callback[1]($this->objDC);
                }
            }
        }
    }

    /**
     * Call the group callback 
     * 
     * @param type $group
     * @param type $mode
     * @param type $field
     * @param InterfaceGeneralModel $objModelRow
     * @return type 
     */
    public function groupCallback($group, $mode, $field, $objModelRow)
    {
        // Load DCA
        $arrDCA = $this->objDC->getDCA();
       
        $currentGroup = $group;

        // Check Callback
        if (is_array($arrDCA['list']['label']['group_callback']))
        {
            $strClass  = $arrDCA['list']['label']['group_callback'][0];
            $strMethod = $arrDCA['list']['label']['group_callback'][1];

            $this->import($strClass);
            $currentGroup = $this->$strClass->$strMethod($currentGroup, $mode, $field, $objModelRow->getPropertiesAsArray(), $this);
            
            if ($currentGroup == null)
            {
                $group = $currentGroup;
            }
        }
        
        return $group;
    }
    
    /**
     * Call the save callback for a widget
     * 
     * @param array $arrConfig Configuration of the widget
     * @param mixed $varNew New Value
     * @return mixed 
     */
    public function saveCallback($arrConfig, $varNew)
    {
        if (is_array($arrConfig['save_callback']))
        {
            foreach ($arrConfig['save_callback'] as $arrCallback)
            {
                $this->import($arrCallback[0]);
                $varNew = $this->$arrCallback[0]->$arrCallback[1]($varNew, $this->objDC);
            }
        }

        return $varNew;
    }
    
    /**
     * Call ondelete_callback
     */
    public function ondeleteCallback()
    {
        // Load DCA
        $arrDCA = $this->objDC->getDCA();

        // Call ondelete_callback
        if (is_array($arrDCA['config']['ondelete_callback']))
        {
            foreach ($arrDCA['config']['ondelete_callback'] as $callback)
            {
                if (is_array($callback))
                {
                    $this->import($callback[0]);
                    $this->$callback[0]->$callback[1]($this->objDC);
                }
            }
        }
    }

    /**
     * Call the onsubmit_callback 
     */
    public function onsubmitCallback()
    {
        // Load DCA
        $arrDCA = $this->objDC->getDCA();
        
        if (is_array($arrDCA['config']['onsubmit_callback']))
        {
            foreach ($arrDCA['config']['onsubmit_callback'] as $callback)
            {
                $this->import($callback[0]);
                $this->$callback[0]->$callback[1]($this->objDC);
            }
        }
    }

}

?>
