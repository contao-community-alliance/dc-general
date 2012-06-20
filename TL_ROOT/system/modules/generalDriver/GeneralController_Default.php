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
            if ($objDBModel->getProperty($key) != $varNewValue)
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

    public function edit(DC_General $objDC)
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
        if ($objDBModel == null)
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
                    $_SESSION['TL_INFO'] = '';
                    $_SESSION['TL_ERROR'] = '';
                    $_SESSION['TL_CONFIRM'] = '';
                    $this->redirect($this->getReferer());
                }
            }
        }
    }

    public function showAll(DC_General $objDC)
    {
        $arrDCA = $objDC->getDCA();
        
        $objDC->setButtonId('tl_buttons');
        
        // Get the IDs of all root records (list view or parent view)
        if (is_array($arrDCA['list']['sorting']['root']))
        {
            $objDC->setRootIds(array_unique($arrDCA['list']['sorting']['root']));
        }

        if ($arrDCA['list']['sorting']['mode'] == 4)
        {
            // TODO implemetn parentView
            $this->parentView($objDC);
        }
        else
        {
            $this->listView($objDC);
        }
        
        $this->panel($objDC);
    }

    protected function listView(DC_General $objDC)
    {
        $arrDCA = $objDC->getDCA();

        if ($arrDCA['list']['sorting']['mode'] == 6)
        {
            $objDataProvider = $objDC->getParentDataProvider();

            $this->loadDataContainer($objDC->getParentTable());
            $objTmpDC = new DC_General($objDC->getParentTable());

            $arrCurrentDCA = $objTmpDC->getDCA();
        }
        else
        {
            $objDataProvider = $objDC->getDataProvider();
            $arrCurrentDCA = $arrDCA;
        }

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

        // TODO implement panel firstSort from session
        if (is_null($objDC->getFirstSorting()))
        {
            $objDC->setFirstSorting(preg_replace('/\s+.*$/i', '', $mixedOrderBy[0]));
        }

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
            $objDC->setFirstSorting('pid');
            $showFields = $arrDCA['list']['label']['fields'];

            foreach ($objCollection as $objModel)
            {
                $objFieldModel = $objDC->getParentDataProvider()->fetch($objModel->getProperty('id'));
                $objModel->setProperty('pid', $objFieldModel->getProperty($showFields[0]));
            }

            $objCollection->sort(array($this, 'sortCollectionPid'));
        }       

        // Process result and add label and buttons
        $remoteCur = false;
        $groupclass = 'tl_folder_tlist';

        foreach ($objCollection as $objModelRow)
        {
            $args = array();
            // TODO set global current in DC_General
            /* $this->current[] = $objModelRow->getProperty('id'); */
            $showFields = $arrCurrentDCA['list']['label']['fields'];

            if (!is_array($objModelRow->getProperty('view')))
            {
                $arrView = array();
            }
            else
            {
                $arrView = $objModelRow->getProperty('view');
            }

            // Label
            foreach ($showFields as $k => $v)
            {
                // Decrypt the value
                if ($arrDCA['fields'][$v]['eval']['encrypt'])
                {
                    $objModelRow->setProperty($v, deserialize($objModelRow->getProperty($v)));

                    $this->import('Encryption');
                    $objModelRow->setProperty($v, $this->Encryption->decrypt($objModelRow->getProperty($v)));
                }

                if (strpos($v, ':') !== false)
                {
                    // TODO case handling
                    /*
                      list($strKey, $strTable) = explode(':', $v);
                      list($strTable, $strField) = explode('.', $strTable);

                      $objRef = $this->Database->prepare("SELECT " . $strField . " FROM " . $strTable . " WHERE id=?")
                      ->limit(1)
                      ->execute($row[$strKey]);

                      $args[$k] = $objRef->numRows ? $objRef->$strField : '';
                     */
                }
                elseif (in_array($arrDCA['fields'][$v]['flag'], array(5, 6, 7, 8, 9, 10)))
                {
                    if ($arrDCA['fields'][$v]['eval']['rgxp'] == 'date')
                    {
                        $args[$k] = $this->parseDate($GLOBALS['TL_CONFIG']['dateFormat'], $objModelRow->getProperty($v));
                    }
                    elseif ($arrDCA['fields'][$v]['eval']['rgxp'] == 'time')
                    {
                        $args[$k] = $this->parseDate($GLOBALS['TL_CONFIG']['timeFormat'], $objModelRow->getProperty($v));
                    }
                    else
                    {
                        $args[$k] = $this->parseDate($GLOBALS['TL_CONFIG']['datimFormat'], $objModelRow->getProperty($v));
                    }
                }
                elseif ($arrDCA['fields'][$v]['inputType'] == 'checkbox' && !$arrDCA['fields'][$v]['eval']['multiple'])
                {
                    $args[$k] = strlen($objModelRow->getProperty($v)) ? $arrCurrentDCA['fields'][$v]['label'][0] : '';
                }
                else
                {
                    $row = deserialize($objModelRow->getProperty($v));

                    if (is_array($row))
                    {
                        $args_k = array();

                        foreach ($row as $option)
                        {
                            $args_k[] = strlen($arrCurrentDCA['fields'][$v]['reference'][$option]) ? $arrCurrentDCA['fields'][$v]['reference'][$option] : $option;
                        }

                        $args[$k] = implode(', ', $args_k);
                    }
                    elseif (isset($arrCurrentDCA['fields'][$v]['reference'][$objModelRow->getProperty($v)]))
                    {
                        $args[$k] = is_array($arrCurrentDCA['fields'][$v]['reference'][$objModelRow->getProperty($v)]) ? $arrCurrentDCA['fields'][$v]['reference'][$objModelRow->getProperty($v)][0] : $arrCurrentDCA['fields'][$v]['reference'][$objModelRow->getProperty($v)];
                    }
                    elseif (array_is_assoc($arrCurrentDCA['fields'][$v]['options']) && isset($arrCurrentDCA['fields'][$v]['options'][$objModelRow->getProperty($v)]))
                    {
                        $args[$k] = $arrCurrentDCA['fields'][$v]['options'][$objModelRow->getProperty($v)];
                    }
                    else
                    {
                        $args[$k] = $objModelRow->getProperty($v);
                    }
                }
            }

            // Shorten the label if it is too long
            $label = vsprintf((strlen($arrDCA['list']['label']['format']) ? $arrDCA['list']['label']['format'] : '%s'), $args);

            if ($arrDCA['list']['label']['maxCharacters'] > 0 && $arrDCA['list']['label']['maxCharacters'] < strlen(strip_tags($label)))
            {
                $this->import('String');
                $label = trim($this->String->substrHtml($label, $arrDCA['list']['label']['maxCharacters'])) . ' …';
            }

            // Remove empty brackets (), [], {}, <> and empty tags from the label
            $label = preg_replace('/\( *\) ?|\[ *\] ?|\{ *\} ?|< *> ?/i', '', $label);
            $label = preg_replace('/<[^>]+>\s*<\/[^>]+>/i', '', $label);

            // Build sorting groups
            if ($arrDCA['list']['sorting']['mode'] > 0)
            {
                $current = $objModelRow->getProperty($objDC->getFirstSorting());
                $orderBy = $arrDCA['list']['sorting']['fields'];
                $sortingMode = (count($orderBy) == 1 && $objDC->getFirstSorting() == $orderBy[0] && strlen($arrDCA['list']['sorting']['flag']) && !strlen($arrDCA['fields'][$objDC->getFirstSorting()]['flag'])) ? $arrDCA['list']['sorting']['flag'] : $arrDCA['fields'][$objDC->getFirstSorting()]['flag'];

                $remoteNew = $objDC->formatCurrentValue($objDC->getFirstSorting(), $current, $sortingMode);

                // Add the group header
                if (!$arrDCA['list']['sorting']['disableGrouping'] && ($remoteNew != $remoteCur || $remoteCur === false))
                {
                    $arrView['group'] = array(
                        'class' => $groupclass,
                        'value' => $objDC->formatGroupHeader($objDC->getFirstSorting(), $remoteNew, $sortingMode, $objModelRow)
                    );

                    $groupclass = 'tl_folder_list';
                    $remoteCur = $remoteNew;
                }
            }

            // Call label callback ($objModelRow, $label, $this)
            if (is_array($arrDCA['list']['label']['label_callback']))
            {
                $strClass = $arrDCA['list']['label']['label_callback'][0];
                $strMethod = $arrDCA['list']['label']['label_callback'][1];

                $this->import($strClass);
                $arrView['lable'] = $this->$strClass->$strMethod($objModelRow, $label, $this);
            }
            else
            {
                $arrView['lable'] = $label;
            }

            $objModelRow->setProperty('view', $arrView);
        }

        $objDC->setCurrentCollecion($objCollection);
    }

    /**
     * Build the sort panel and write it to DC_General
     */
    protected function panel(DC_General $objDC)
    {
        $arrDCA = $objDC->getDCA();
        
        $arrPanelView = array();

//        $filter = $this->filterMenu();
//        $search = $this->searchMenu();
        $limit = $this->limitMenu($objDC);
//        $sort = $this->sortMenu();

        /*if (!strlen($filter) && !strlen($search) && !strlen($limit) && !strlen($sort))
        {
            return '';
        }*/

        if (!strlen($arrDCA['list']['sorting']['panelLayout']))
        {
            return '';
        }

        if ($this->Input->post('FORM_SUBMIT') == 'tl_filters')
        {
            $this->reload();
        }

        $panelLayout = $arrDCA['list']['sorting']['panelLayout'];
        $arrPanels = trimsplit(';', $panelLayout);
        $intLast = count($arrPanels) - 1;

        for ($i = 0; $i < count($arrPanels); $i++)
        {
            $arrSubPanels = trimsplit(',', $arrPanels[$i]);

            foreach ($arrSubPanels as $strSubPanel)
            {
                if (is_array($$strSubPanel) && count($$strSubPanel) > 0)
                {
                    $arrPanelView[$strSubPanel] = $$strSubPanel;
                }                
            }
        }
        
        if(count($arrPanelView) > 0)
        {
            $objDC->setPanelView($arrPanelView);
        }
    }

    /**
     * Return a select menu to limit results
     * @param boolean
     * @return string
     */
    protected function limitMenu(DC_General $objDC, $blnOptional = false)
    {
        $arrDCA = $objDC->getDCA();
        $arrPanelView = array();
        
        $session = $this->Session->getData();
        $filter = ($arrDCA['list']['sorting']['mode'] == 4) ? $objDC->getTable() . '_' . CURRENT_ID : $objDC->getTable();

        // Set limit from user input
        if ($this->Input->post('FORM_SUBMIT') == 'tl_filters' || $this->Input->post('FORM_SUBMIT') == 'tl_filters_limit')
        {
            if ($this->Input->post('tl_limit') != 'tl_limit')
            {
                $session['filter'][$filter]['limit'] = $this->Input->post('tl_limit');
            }
            else
            {
                unset($session['filter'][$filter]['limit']);
            }

            $this->Session->setData($session);

            if ($this->Input->post('FORM_SUBMIT') == 'tl_filters_limit')
            {
                $this->reload();
            }
        }

        // Set limit from table configuration
        else
        {
            $this->limit = strlen($session['filter'][$filter]['limit']) ? (($session['filter'][$filter]['limit'] == 'all') ? null : $session['filter'][$filter]['limit']) : '0,' . $GLOBALS['TL_CONFIG']['resultsPerPage'];
            
            // TODO change with own data count request            
            $total = $objDC->getCurrentCollecion()->length();
            $blnIsMaxResultsPerPage = false;

            // Overall limit
            if ($total > $GLOBALS['TL_CONFIG']['maxResultsPerPage'] && (is_null($this->limit) || preg_replace('/^.*,/i', '', $this->limit) == $GLOBALS['TL_CONFIG']['maxResultsPerPage']))
            {
                if (is_null($this->limit))
                {
                    $this->limit = '0,' . $GLOBALS['TL_CONFIG']['maxResultsPerPage'];
                }

                $blnIsMaxResultsPerPage = true;
                $GLOBALS['TL_CONFIG']['resultsPerPage'] = $GLOBALS['TL_CONFIG']['maxResultsPerPage'];
                $session['filter'][$filter]['limit'] = $GLOBALS['TL_CONFIG']['maxResultsPerPage'];
            }

            // Build options
            if ($total > 0)
            {
                $arrPanelView['option'][0] = array();
                $options_total = ceil($total / $GLOBALS['TL_CONFIG']['resultsPerPage']);

                // Reset limit if other parameters have decreased the number of results
                if (!is_null($this->limit) && ($this->limit == '' || preg_replace('/,.*$/i', '', $this->limit) > $total))
                {
                    $this->limit = '0,' . $GLOBALS['TL_CONFIG']['resultsPerPage'];
                }
                
                // Build options
                for ($i = 0; $i < $options_total; $i++)
                {
                    $this_limit = ($i * $GLOBALS['TL_CONFIG']['resultsPerPage']) . ',' . $GLOBALS['TL_CONFIG']['resultsPerPage'];
                    $upper_limit = ($i * $GLOBALS['TL_CONFIG']['resultsPerPage'] + $GLOBALS['TL_CONFIG']['resultsPerPage']);

                    if ($upper_limit > $total)
                    {
                        $upper_limit = $total;
                    }

                    $arrPanelView['option'][] = array(
                        'value' => $this_limit,
                        'select' => $this->optionSelected($this->limit, $this_limit),
                        'content' => ($i * $GLOBALS['TL_CONFIG']['resultsPerPage'] + 1) . ' - ' . $upper_limit
                    );                    
                }

                if (!$blnIsMaxResultsPerPage)
                {
                    $arrPanelView['option'][] = array(
                        'value' => 'all',
                        'select' => $this->optionSelected($this->limit, null),
                        'content' => $GLOBALS['TL_LANG']['MSC']['filterAll']
                    );
                }
            }

            // Return if there is only one page
            if ($blnOptional && ($total < 1 || $options_total < 2))
            {
                return array();
            }

            $arrPanelView['select'] = array(
                'class' => (($session['filter'][$filter]['limit'] != 'all' && $total > $GLOBALS['TL_CONFIG']['resultsPerPage']) ? ' active' : '')
            );
            
            $arrPanelView['option'][0] = array(
                'value' => 'tl_limit',
                'select' => '',
                'content' => $GLOBALS['TL_LANG']['MSC']['filterRecords']
            );
        }

        return $arrPanelView;
    }

    public function sortCollectionPid(InterfaceGeneralModel $a, InterfaceGeneralModel $b)
    {
        if ($a->getProperty('pid') == $b->getProperty('pid'))
        {
            return 0;
        }

        return ($a->getProperty('pid') < $b->getProperty('pid')) ? -1 : 1;
    }

}

?>
