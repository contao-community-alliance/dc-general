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
class GeneralController_Default extends Controller implements InterfaceGeneralController
{

    protected $notImplMsg = "<div style='text-align:center; font-weight:bold; padding:40px;'>The function/view &quot;%s&quot; is not implemented.</div>";

    public function __call($name, $arguments)
    {
        switch ($name)
        {
            case "edit":
                return $this->runEdit($arguments[0]);
                break;
            
            default:
                return sprintf($this->notImplMsg, $name);
                break;
        };
    }

    /**
     * Perform low level saving of the current model in a DC.
     * NOTE: the model will get populated with the new values within this function.
     * Therefore the current submitted data will be stored within the model but only on
     * success also be saved into the DB.
     * 
     * @param DC_General $objDC the DC that adapts the save operation.
     * 
     * @return bool true if the save operation was successful, false otherwise.
     */
    protected function doSave(DC_General $objDC)
    {
        $objDBModel = $objDC->getCurrentModel();
        // process input and update changed properties.
        foreach ($objDC->getFieldList() as $key => $value)
        {
            $varNewValue = $objDC->processInput($key);
            if($objDBModel->getProperty($key) != $varNewValue)
            {
                $objDBModel->setProperty($key, $varNewValue);
            }
        }
        // if we may not store the value, we keep the changes
        // in the current model and return (DO NOT SAVE!).
        if ($objDC->isNoReload() == true)
        {
            return false;
        }
        // everything went ok, now save the new values.
        $objDC->getDataProvider()->save($objDBModel);
        return true;
    }

    protected function runEdit(DC_General $objDC)
    {
        // Check if table is editable
        if (!$objDC->isEditable())
        {
            $this->log('Table ' . $objDC->getTable() . ' is not editable', 'DC_General edit()', TL_ERROR);
            $this->redirect('contao/main.php?act=error');
        }

        // Load fields and co
        $objDC->loadEditableFields();
        $objDC->setWidgetID($objDC->getId());

        // Check if we have fields
        if (!$objDC->hasEditableFields())
        {
            $this->redirect($this->getReferer());
        }

        // Load something
        $objDC->preloadTinyMce();

        // Set buttons
        $objDC->addButton("save");
        $objDC->addButton("saveNclose");

        // Load record from data provider
        $objDBModel = $objDC->getDataProvider()->fetch($objDC->getId());
        if($objDBModel == null)
        {
            $objDBModel = $objDC->getDataProvider()->getEmpty();
        }
        $objDC->setCurrentModel($objDBModel);

        // Check submit
        if ($objDC->isSubmitted() == true)
        {
            if (isset($_POST["save"]))
            {
                // process input and update changed properties.
                if ($this->doSave($objDC))
                {
                    $this->reload();
                }
            }
            else if (isset($_POST["saveNclose"]))
            {
                // process input and update changed properties.
                if ($this->doSave($objDC))
                {
                    setcookie('BE_PAGE_OFFSET', 0, 0, '/');
                    $_SESSION['TL_INFO']    = '';
                    $_SESSION['TL_ERROR']   = '';
                    $_SESSION['TL_CONFIRM'] = '';
                    $this->redirect($this->getReferer());
                }
            }
        }
    }

    protected function runShowAll(DC_General $objDC)
    {
        $this->listView($objDC);
    }

    protected function listView(DC_General $objDC)
    {
//        $arrDCA = $objDC->getDCA();
//                
//        // Load record from data provider
//        $objDBModel = $objDC->getDataProvider();        
//                
//        $objDC->setCurrentModel($objDBModel);
    }

}

?>
