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

    protected $notImplMsg = "<div style='text-align:center; font-weight:bold; padding:40px;'>This function/view is not implemented.</div>";

    public function __call($name, $arguments)
    {
        switch ($name)
        {
            case "edit":
                return $this->runEdit($arguments[0]);
                break;
            
            default:
                return $this->notImplMsg;
                break;
        };
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
        $objDC->addButton("gogogo");
        $objDC->addButton("foo");
        
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
                $objCurrentModel = $objDC->getDataProvider()->getEmpty();
                
                foreach ($objDC->getFieldList() as $key => $value)
                {
                    $objCurrentModel->setProperty($key, $objDC->processInput($key));
                }

                if ($objDC->isNoReload() == true)
                {
                    return;
                }
                
                foreach ($objCurrentModel as $key => $value)
                {
                    if($objDBModel->getProperty($key) != $value)
                    {
                        $objCurrentModel->setProperty("id", $objDC->getId());                        
                        $objDC->getDataProvider()->save($objCurrentModel);
                    }
                }
                
                $this->reload();
            }
            else if (isset($_POST["saveNclose"]))
            {
                setcookie('BE_PAGE_OFFSET', 0, 0, '/');

                $_SESSION['TL_INFO']    = '';
                $_SESSION['TL_ERROR']   = '';
                $_SESSION['TL_CONFIRM'] = '';

                $this->redirect($this->getReferer());
            }
            else if (isset($_POST["gogogo"]))
            {
                return "<div style='text-align:center; font-weight:bold; padding:40px;'>Run forest run.</div>";
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
