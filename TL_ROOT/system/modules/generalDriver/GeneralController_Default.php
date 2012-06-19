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

    protected function edit(DC_General $objDC)
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

    public function showAll(DC_General $objDC)
    {
        $objDC->setButtonId('tl_buttons');

        $this->listView($objDC);
    }

    protected function listView(DC_General $objDC)
    {
        $arrDCA = $objDC->getDCA();

        $objDataProvider = ($arrDCA['list']['sorting']['mode'] == 6) ? $objDC->getParentDataProvider() : $objDC->getDataProvider();

        $arrFilterIds = $arrDCA['list']['sorting']['root'];

        // TODO implement panel filter from session
        $arrFilter = $objDC->getFilter();
        if (is_array($arrFilterIds) && count($arrFilterIds) > 0)
        {
            if (is_null($arrFilter))
            {
                $arrFilter = array();
            }

            $arrFilter['id'] = array_map('intval', $arrFilterIds);
        }

        $mixedOrderBy = $arrDCA['list']['sorting']['fields'];

        // TODO implement panel sorting from session
        if (!is_null($objDC->getSorting()))
        {
            $mixedOrderBy = $objDC->getSorting();
        }

        if (is_array($mixedOrderBy) && strlen($mixedOrderBy[0]))
        {
            foreach ($mixedOrderBy as $key => $strField)
            {
                if ($arrDCA['fields'][$strField]['eval']['findInSet'])
                {
                    if (is_array($arrDCA['fields'][$strField]['options_callback']))
                    {
                        $strClass = $arrDCA['fields'][$strField]['options_callback'][0];
                        $strMethod = $arrDCA['fields'][$strField]['options_callback'][1];

                        $this->import($strClass);
                        $keys = $this->$strClass->$strMethod($this);
                    }
                    else
                    {
                        $keys = $arrDCA['fields'][$strField]['options'];
                    }

                    if (array_is_assoc($keys))
                    {
                        $keys = array_keys($keys);
                    }

                    $mixedOrderBy[$key] = array(
                        'field' => $strField,
                        'keys' => $keys,
                        'action' => 'findInSet'
                    );
                }
            }
        }

        // Set sort order
        if ($arrDCA['list']['sorting']['mode'] == 1 && ($arrDCA['list']['sorting']['flag'] % 2) == 0)
        {
            $mixedOrderBy['sortOrder'] = " DESC";
        }

        // Set Limit
        // TODO implement panel limit from session
        $arrLimit = array(0, 0);
        if (!is_null($objDC->getLimit()))
        {
            $arrLimit = explode(',', $objDC->getLimit());
        }

        // Load record from data provider
        $objCollection = $objDataProvider->fetchAll(false, $arrLimit[0], $arrLimit[1], $arrFilter, $mixedOrderBy);
        
        // Rename each pid to its label and resort the result (sort by parent table)
        if ($arrDCA['list']['sorting']['mode'] == 3 && $objDataProvider->fieldExists('pid'))
        {
            $showFields = $arrDCA['list']['label']['fields'];

            foreach ($objCollection as $objModel)
            {
                $objFieldModel = $objDC->getParentDataProvider()->fetch($objModel->getProperty('id'));
                $objModel->setProperty('pid', $objFieldModel->getProperty($showFields[0]));
            }
            
            $objCollection->sort(array($this, 'sortCollectionPid'));
        }

        $objDC->setCurrentCollecion($objCollection);
    }
    
    public function sortCollectionPid(InterfaceGeneralModel $a, InterfaceGeneralModel $b)
    {
        if ($a->getProperty('pid') == $b->getProperty('pid')) {
            return 0;
        }
        
        return ($a->getProperty('pid') < $b->getProperty('pid')) ? -1 : 1;
    }

}

?>
