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
class GeneralControllerDefault extends Controller implements InterfaceGeneralController
{

    protected $notImplMsg = "<div style='text-align:center; font-weight:bold; padding:40px;'>The function/view &quot;%s&quot; is not implemented.</div>";

    /**
     *
     * @var Session
     */
    protected $objSession = null;

    /**
     *
     * @var DC_General
     */
    protected $dc;

    /**
     *
     * @var array
     */
    protected $dca;

    /**
     * Field for the function sortCollection
     * 
     * @var string $arrColSort
     */
    protected $arrColSort;

    public function __construct()
    {
        parent::__construct();

        $this->objSession = Session::getInstance();
    }

    public function __call($name, $arguments)
    {
        switch ($name)
        {
            default:
                return sprintf($this->notImplMsg, $name);
                break;
        };
    }

    // Core Functions ----------------------------------------------------------

    /**
     * Perform low level saving of the current model in a DC.
     * NOTE: the model will get populated with the new values within this function.
     * Therefore the current submitted data will be stored within the model but only on
     * success also be saved into the DB.
     * 
     * @param DC_General $objDC the DC that adapts the save operation.
     * 
     * @return bool|InterfaceGeneralModel Model if the save operation was successful, false otherwise.
     */
    protected function doSave(DC_General $objDC)
    {
        $objDBModel = $objDC->getCurrentModel();
        $arrDCA     = $objDC->getDCA();

        // Check if table is closed
        if ($arrDCA['config']['closed'] == true)
        {
            // TODO show alarm message
            $this->redirect($this->getReferer());
        }

        // process input and update changed properties.
        foreach (array_keys($objDC->getFieldList()) as $key)
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

        // Callback
        $objDC->getCallbackClass()->onsubmitCallback();

        // Refresh timestamp
        if ($objDC->getDataProvider()->fieldExists("tstamp") == true)
        {
            $objDBModel->setProperty("tstamp", time());
        }

        // everything went ok, now save the new record 
        $objDC->getDataProvider()->save($objDBModel);

        // Check if versioning is enabled
        if (isset($arrDCA['config']['enableVersioning']) && $arrDCA['config']['enableVersioning'] == true)
        {
            // Compare version and current record
            $mixCurrentVersion = $objDC->getDataProvider()->getActiveVersion($objDBModel->getID());
            if ($mixCurrentVersion != null)
            {
                $mixCurrentVersion = $objDC->getDataProvider()->getVersion($objDBModel->getID(), $mixCurrentVersion);

                if ($objDC->getDataProvider()->sameModels($objDBModel, $mixCurrentVersion) == false)
                {
                    // TODO: FE|BE switch
                    $this->import('BackendUser', 'User');
                    $objDC->getDataProvider()->saveVersion($objDBModel, $this->User->username);
                }
            }
            else
            {
                // TODO: FE|BE switch
                $this->import('BackendUser', 'User');
                $objDC->getDataProvider()->saveVersion($objDBModel, $this->User->username);
            }
        }

        // Return the current model
        return $objDBModel;
    }

    /**
     * Create function 
     *  
     * @param DC_General $objDC 
     */
    public function create(DC_General $objDC)
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
        $objDBModel = $objDC->getDataProvider()->getEmptyModel();
        $objDC->setCurrentModel($objDBModel);

        // Check submit
        if ($objDC->isSubmitted() == true)
        {
            if (isset($_POST["save"]))
            {
                // process input and update changed properties.
                if (($objModell = $this->doSave($objDC)) !== false)
                {
                    // Callback
                    $objDC->getCallbackClass()->oncreateCallback($objDBModel->getID(), $objDBModel->getPropertiesAsArray());
                    // Log
                    $this->log('A new entry in table "' . $objDC->getTable() . '" has been created (ID: ' . $objModell->getID() . ')', 'DC_General - Controller - create()', TL_GENERAL);
                    // Redirect
                    $this->redirect($this->addToUrl("id=" . $objDBModel->getID() . "&amp;act=edit"));
                }
            }
            else if (isset($_POST["saveNclose"]))
            {
                // process input and update changed properties.
                if (($objModell = $this->doSave($objDC)) !== false)
                {
                    setcookie('BE_PAGE_OFFSET', 0, 0, '/');

                    $_SESSION['TL_INFO']    = '';
                    $_SESSION['TL_ERROR']   = '';
                    $_SESSION['TL_CONFIRM'] = '';

                    // Callback
                    $objDC->getCallbackClass()->oncreateCallback($objDBModel->getID(), $objDBModel->getPropertiesAsArray());
                    // Log
                    $this->log('A new entry in table "' . $objDC->getTable() . '" has been created (ID: ' . $objModell->getID() . ')', 'DC_General - Controller - create()', TL_GENERAL);
                    // Redirect
                    $this->redirect($this->getReferer());
                }
            }
        }
    }

    public function delete(DC_General $objDC)
    {
        $arrDCA      = $objDC->getDCA;
        $intRecordID = $this->Input->get("id");

        // Check if is it allowed to delete a record
        if ($arrDCA['config']['notDeletable'])
        {
            $this->log('Table "' . $objDC->getTable() . '" is not deletable', 'DC_General - Controller - delete()', TL_ERROR);
            $this->redirect('contao/main.php?act=error');
        }

        // Check if we have a id
        if (strlen($intRecordID) == 0)
        {
            $this->reload();
        }

        // Callback
        $objDC->setCurrentModel($objDC->getDataProvider()->fetch($objDC->getDataProvider()->getEmptyConfig()->setId($intRecordID)));
        $objDC->getCallbackClass()->ondeleteCallback();

        // Delete record
        $objDC->getDataProvider()->delete($intRecordID);

        // Add a log entry unless we are deleting from tl_log itself
        if ($this->dc->getTable() != 'tl_log')
        {
            $this->log('DELETE FROM ' . $objDC->getTable() . ' WHERE id=' . $intRecordID, 'DC_General - Controller - delete()', TL_GENERAL);
        }

        $this->redirect($this->getReferer());
    }

    public function edit(DC_General $objDC)
    {
        // Check if table is editable
        if (!$objDC->isEditable())
        {
            $this->log('Table ' . $objDC->getTable() . ' is not editable', 'DC_General - Controller - edit()', TL_ERROR);
            $this->redirect('contao/main.php?act=error');
        }
        
        // Check if DP is multilanguage
        if(is_a($objDC->getDataProvider(), "InterfaceGeneralDataML"))
        {
            return $this->editML($objDC);
        }
        else
        {
            return $this->editSL($objDC);
        }
    }
    
    protected function editSL(DC_General $objDC)
    {
        $objDataProvider = $objDC->getDataProvider();

        // Load an older Version
        if (strlen($this->Input->post("version")) != 0 && $objDC->isVersionSubmit())
        {
            // Load record from version 
            $objVersionModel = $objDataProvider->getVersion($objDC->getId(), $this->Input->post("version"));

            // Redirect if there is no record with the given ID
            if ($objVersionModel == null)
            {
                $this->log('Could not load record ID ' . $objDC->getId() . ' of table "' . $objDC->getTable() . '"', 'DC_General - Controller - edit()', TL_ERROR);
                $this->redirect('contao/main.php?act=error');
            }

            $objDataProvider->save($objVersionModel);
            $objDataProvider->setVersionActive($objDC->getId(), $this->Input->post("version"));

            // Callback onrestoreCallback
            $arrData       = $objVersionModel->getPropertiesAsArray();
            $arrData["id"] = $objVersionModel->getID();

            $objDC->getCallbackClass()->onrestoreCallback($objDC->getId(), $objDC->getTable(), $arrData, $this->Input->post("version"));

            $this->log(sprintf('Version %s of record ID %s (table %s) has been restored', $this->Input->post('version'), $objDC->getId(), $objDC->getTable()), 'DC_General - Controller - edit()', TL_GENERAL);

            // Reload page with new recored
            $this->reload();
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
        $objDBModel = $objDataProvider->fetch($objDC->getDataProvider()->getEmptyConfig()->setId($objDC->getId()));
        if ($objDBModel == null)
        {
            $objDBModel = $objDataProvider->getEmptyModel();
        }
        $objDC->setCurrentModel($objDBModel);

        // Check if we have a auto submit
        if ($objDC->isAutoSubmitted())
        {
            // process input and update changed properties.
            foreach (array_keys($objDC->getFieldList()) as $key)
            {
                $varNewValue = $objDC->processInput($key);
                if ($objDBModel->getProperty($key) != $varNewValue)
                {
                    $objDBModel->setProperty($key, $varNewValue);
                }
            }

            $objDC->setCurrentModel($objDBModel);
        }

        // Check submit
        if ($objDC->isSubmitted() == true)
        {
            if (isset($_POST["save"]))
            {
                // process input and update changed properties.
                if ($this->doSave($objDC) !== false)
                {
                    $this->reload();
                }
            }
            else if (isset($_POST["saveNclose"]))
            {
                // process input and update changed properties.
                if ($this->doSave($objDC) !== false)
                {
                    setcookie('BE_PAGE_OFFSET', 0, 0, '/');

                    $_SESSION['TL_INFO']    = '';
                    $_SESSION['TL_ERROR']   = '';
                    $_SESSION['TL_CONFIRM'] = '';

                    $this->redirect($this->getReferer());
                }
            }

            // Maybe Callbacks ?
        }
    }

    protected function editML(DC_General $objDC)
    {
        $objDataProvider = $objDC->getDataProvider();
        $strCurrentLanguage = "de";
        $intID = $objDC->getId();
        $objLanguagesSupported = $objDC->getDataProvider()->getLanguages($intID);
        
        if($objLanguagesSupported == null)
        {
            return $this->editSL($objDC);
        }
               
        // Get/Check the new language
        if (strlen($this->Input->post("language")) != 0 && $_POST['FORM_SUBMIT'] == 'language_switch')
        {
            if (key_exists($this->Input->post("language"), $objLanguagesSupported))
            {
                $strCurrentLanguage = $this->Input->post("language");

                // Save current language in session
                $arrSession = $this->Session->get("dc_general");

                if (!is_array($arrSession))
                {
                    $arrSession = array();
                }

                $arrSession["ml_support"][$objDC->getTable()][$intID] = $strCurrentLanguage;
                $this->Session->set("dc_general", $arrSession);
            }
            else
            {
                $this->log('Language "' . $this->Input->post("language") . '" for able ' . $objDC->getTable() . ' are not exsists', 'DC_General - Controller - edit()', TL_ERROR);
                $this->redirect('contao/main.php?act=error');
            }
        }
        else
        {
            // Load current language from session, if set
            $arrSession = $this->Session->get("dc_general");
            if (is_array($arrSession) && isset($arrSession["ml_support"][$objDC->getTable()][$intID]))
            {
                $strCurrentLanguage = $arrSession["ml_support"][$objDC->getTable()][$intID];
            }
        }

        $objDataProvider->setCurrentLanguage($strCurrentLanguage);
        
        // Load an older Version
        if (strlen($this->Input->post("version")) != 0 && $objDC->isVersionSubmit())
        {
            // Load record from version 
            $objVersionModel = $objDataProvider->getVersion($objDC->getId(), $this->Input->post("version"));

            // Redirect if there is no record with the given ID
            if ($objVersionModel == null)
            {
                $this->log('Could not load record ID ' . $objDC->getId() . ' of table "' . $objDC->getTable() . '"', 'DC_General - Controller - edit()', TL_ERROR);
                $this->redirect('contao/main.php?act=error');
            }

            $objDataProvider->save($objVersionModel);
            $objDataProvider->setVersionActive($objDC->getId(), $this->Input->post("version"));

            // Callback onrestoreCallback
            $arrData       = $objVersionModel->getPropertiesAsArray();
            $arrData["id"] = $objVersionModel->getID();

            $objDC->getCallbackClass()->onrestoreCallback($objDC->getId(), $objDC->getTable(), $arrData, $this->Input->post("version"));

            $this->log(sprintf('Version %s of record ID %s (table %s) has been restored', $this->Input->post('version'), $objDC->getId(), $objDC->getTable()), 'DC_General - Controller - edit()', TL_GENERAL);

            // Reload page with new recored
            $this->reload();
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
        $objDBModel = $objDataProvider->fetch($objDC->getDataProvider()->getEmptyConfig()->setId($objDC->getId()));
        if ($objDBModel == null)
        {
            $objDBModel = $objDataProvider->getEmptyModel();
        }
        $objDBModel->setProperty("currentLanguage", $strCurrentLanguage);
        $objDC->setCurrentModel($objDBModel);

        // Check if we have a auto submit
        if ($objDC->isAutoSubmitted())
        {
            // process input and update changed properties.
            foreach (array_keys($objDC->getFieldList()) as $key)
            {
                $varNewValue = $objDC->processInput($key);
                if ($objDBModel->getProperty($key) != $varNewValue)
                {
                    $objDBModel->setProperty($key, $varNewValue);
                }
            }

            $objDC->setCurrentModel($objDBModel);
        }

        // Check submit
        if ($objDC->isSubmitted() == true)
        {
            if (isset($_POST["save"]))
            {
                // process input and update changed properties.
                if ($this->doSave($objDC) !== false)
                {
                    $this->reload();
                }
            }
            else if (isset($_POST["saveNclose"]))
            {
                // process input and update changed properties.
                if ($this->doSave($objDC) !== false)
                {
                    setcookie('BE_PAGE_OFFSET', 0, 0, '/');

                    $_SESSION['TL_INFO']    = '';
                    $_SESSION['TL_ERROR']   = '';
                    $_SESSION['TL_CONFIRM'] = '';

                    $this->redirect($this->getReferer());
                }
            }

            // Maybe Callbacks ?
        }
    }

    public function show(DC_General $objDC)
    {
        // Load record from data provider
        $objDBModel = $objDC->getDataProvider()->fetch($objDC->getDataProvider()->getEmptyConfig()->setId($objDC->getId()));

        if ($objDBModel == null)
        {
            $this->log('Could not find ID ' . $objDC->getId() . ' in Table ' . $objDC->getTable() . '.', 'DC_General show()', TL_ERROR);
            $this->redirect('contao/main.php?act=error');
        }

        $objDC->setCurrentModel($objDBModel);
    }

    public function showAll(DC_General $objDC)
    {
        $this->dc = $objDC;
        $this->dca = $objDC->getDCA();

        $this->dc->setButtonId('tl_buttons');

        // Custom filter
        if (is_array($this->dca['list']['sorting']['filter']) && !empty($this->dca['list']['sorting']['filter']))
        {
            foreach ($this->dca['list']['sorting']['filter'] as $filter)
            {
                $this->dc->setFilter(array($filter[0] . " = '" . $filter[1] . "'"));
            }
        }

        // Get the IDs of all root records (list view or parent view)
        if (is_array($this->dca['list']['sorting']['root']))
        {
            $this->dc->setRootIds(array_unique($this->dca['list']['sorting']['root']));
        }

        if ($this->Input->get('table') && !is_null($objDC->getParentTable()) && $objDC->getDataProvider()->fieldExists('pid'))
        {
            $objDC->setFilter(array("pid = '" . CURRENT_ID . "'"));
        }

        $this->panel($this->dc);

        if ($this->dca['list']['sorting']['mode'] == 4 && !is_null($this->dc->getParentTable()))
        {
            $this->parentView();
        }
        else
        {
            $this->listView();
        }
    }

    public function generateAjaxPalette(DC_General $objDC, $strMethod, $strSelector)
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

        // Load record from data provider
        $objDBModel = $objDC->getDataProvider()->fetch($objDC->getDataProvider()->getEmptyConfig()->setId($objDC->getId()));
        if ($objDBModel == null)
        {
            $objDBModel = $objDC->getDataProvider()->getEmptyModel();
        }
        $objDC->setCurrentModel($objDBModel);
    }

    // showAll Modis -----------------------------------------------------------

    protected function listView()
    {
        if ($this->dca['list']['sorting']['mode'] == 6)
        {
            $objDataProvider = $this->dc->getDataProvider('parent');

            $this->loadLanguageFile($this->dc->getParentTable());
            $this->loadDataContainer($this->dc->getParentTable());
            $objTmpDC = new DC_General($this->dc->getParentTable());

            $arrCurrentDCA = $objTmpDC->getDCA();
        }
        else
        {
            $objDataProvider = $this->dc->getDataProvider();
            $arrCurrentDCA   = $this->dca;
        }

        // Get limits
        $arrLimit = $this->getLimit();

        // Load record from data provider
        $objConfig = $this->dc->getDataProvider()->getEmptyConfig()
                ->setIdOnly(true)
                ->setStart($arrLimit[0])
                ->setAmount($arrLimit[1])
                ->setFilter($this->getFilter())
                ->setSorting($this->getListViewSorting());

        $objCollection = $objDataProvider->fetchEach($this->dc->getDataProvider()->getEmptyConfig()->setIds($objDataProvider->fetchAll($objConfig))->setSorting($this->getListViewSorting()));

        // Rename each pid to its label and resort the result (sort by parent table)
        if ($this->dca['list']['sorting']['mode'] == 3)
        {
            $this->dc->setFirstSorting('pid');
            $showFields = $this->dca['list']['label']['fields'];

            foreach ($objCollection as $objModel)
            {
                $objFieldModel = $this->dc->getDataProvider('parent')->fetch($this->dc->getDataProvider()->getEmptyConfig()->setId($objModel->getID()));
                $objModel->setProperty('pid', $objFieldModel->getProperty($showFields[0]));
            }

            $this->arrColSort = array(
                'field'   => 'pid',
                'reverse' => false
            );

            $objCollection->sort(array($this, 'sortCollection'));
        }

        // TODO set global current in DC_General
        /* $this->current[] = $objModelRow->getProperty('id'); */
        $showFields = $arrCurrentDCA['list']['label']['fields'];

        if (is_array($showFields))
        {
            // Label
            foreach ($showFields as $v)
            {
                // Decrypt the value
                if ($this->dca['fields'][$v]['eval']['encrypt'])
                {
                    $objModelRow->setProperty($v, deserialize($objModelRow->getProperty($v)));

                    $this->import('Encryption');
                    $objModelRow->setProperty($v, $this->Encryption->decrypt($objModelRow->getProperty($v)));
                }

                if (strpos($v, ':') !== false)
                {
                    list($strKey, $strTable) = explode(':', $v);
                    list($strTable, $strField) = explode('.', $strTable);


                    $objModel = $this->dc->getDataProvider($strTable)->fetch(
                            $this->dc->getDataProvider()->getEmptyConfig()
                                    ->setId($row[$strKey])
                                    ->setFields(array($strField))
                    );

                    $objModelRow->setProperty('%args%', (($objModel->hasProperties()) ? $objModel->getProperty($strField) : ''));
                }
            }
        }

        $this->dc->setCurrentCollecion($objCollection);
    }

    /**
     * Show header of the parent table and list all records of the current table
     * @return string
     */
    protected function parentView()
    {
        // Load language file and data container array of the parent table
        $this->loadLanguageFile($this->dc->getParentTable());
        $this->loadDataContainer($this->dc->getParentTable());

        $objParentDC = new DC_General($this->dc->getParentTable());
        $this->parentDc = $objParentDC->getDCA();

        // Get limits
        $arrLimit = $this->getLimit();

        // Load record from data provider
        $objConfig = $this->dc->getDataProvider()->getEmptyConfig()
                ->setStart($arrLimit[0])
                ->setAmount($arrLimit[1])
                ->setFilter($this->getFilter())
                ->setSorting($this->getParentViewSorting());

        if ($this->foreignKey)
        {
            $objConfig->setFields($this->arrFields);
        }

        $this->dc->setCurrentCollecion($this->dc->getDataProvider()->fetchAll($objConfig));

        if (!is_null($this->dc->getParentTable()))
        {

            // Load record from parent data provider
            $objCollection = $this->dc->getDataProvider('parent')->getEmptyCollection();
            $objCollection->add(
                    $this->dc->getDataProvider('parent')->fetch(
                            $this->dc->getDataProvider()->getEmptyConfig()
                                    ->setId(CURRENT_ID)
                    )
            );

            $this->dc->setCurrentParentCollection($objCollection);

            // List all records of the child table
            if (!$this->Input->get('act') || $this->Input->get('act') == 'paste' || $this->Input->get('act') == 'select')
            {
                $headerFields = $this->dca['list']['sorting']['headerFields'];

                foreach ($headerFields as $v)
                {
                    $_v = deserialize($this->dc->getCurrentParentCollection()->get(0)->getProperty($v));

                    if ($v == 'tstamp')
                    {
                        $objCollection = $this->dc->getDataProvider()->fetchAll(
                                $this->dc->getDataProvider()->getEmptyConfig()
                                        ->setFilter(array("pid = '" . $this->dc->getCurrentParentCollection()->get(0)->getID() . "'"))
                                        ->setFields(array('MAX(tstamp) AS tstamp'))
                        );

                        $objTStampModel = $objCollection->get(0);

                        if (!$objTStampModel->getProperty('tstamp'))
                        {
                            $objTStampModel->setProperty('tstamp', $this->dc->getCurrentParentCollection()->get(0)->getProperty($v));
                        }

                        $this->dc->getCurrentParentCollection()->get(0)->setProperty($v, $this->parseDate($GLOBALS['TL_CONFIG']['datimFormat'], max($this->dc->getCurrentParentCollection()->get(0)->getProperty($v), $objTStampModel->getProperty('tstamp'))));
                    }
                    elseif (isset($this->parentDc['fields'][$v]['foreignKey']))
                    {
                        $arrForeignKey = explode('.', $this->parentDc['fields'][$v]['foreignKey'], 2);

                        $objLabelModel = $this->dc->getDataProvider($arrForeignKey[0])->fetch(
                                $this->dc->getDataProvider()->getEmptyConfig()
                                        ->setId($_v)
                                        ->setFields(array($arrForeignKey[1] . " AS value"))
                        );

                        if ($objLabelModel->hasProperties())
                        {
                            $this->dc->getCurrentParentCollection()->get(0)->setProperty($v, $objLabelModel->getProperty('value'));
                        }
                    }
                }
            }
        }
    }

    // Panel -------------------------------------------------------------------

    /**
     * Build the sort panel and write it to DC_General
     */
    protected function panel()
    {
        $arrPanelView = array();

        $filter = $this->filterMenu();
        $search = $this->searchMenu();
        $limit  = $this->limitMenu();
        $sort   = $this->sortMenu();

        if (!strlen($this->dca['list']['sorting']['panelLayout']) || !is_array($filter) && !is_array($search) && !is_array($limit) && !is_array($sort))
        {
            return;
        }

        if ($this->Input->post('FORM_SUBMIT') == 'tl_filters')
        {
            $this->reload();
        }

        $panelLayout = $this->dca['list']['sorting']['panelLayout'];
        $arrPanels   = trimsplit(';', $panelLayout);

        for ($i = 0; $i < count($arrPanels); $i++)
        {
            $arrSubPanels = trimsplit(',', $arrPanels[$i]);

            foreach ($arrSubPanels as $strSubPanel)
            {
                if (is_array($$strSubPanel) && count($$strSubPanel) > 0)
                {
                    $arrPanelView[$i][$strSubPanel] = $$strSubPanel;
                }
            }

            if (is_array($arrPanelView[$i]))
            {
                $arrPanelView[$i] = array_reverse($arrPanelView[$i]);
            }
        }

        if (count($arrPanelView) > 0)
        {
            $this->dc->setPanelView(array_values($arrPanelView));
        }
    }

    /**
     * Generate the filter panel and return it as HTML string
     * @return string
     */
    protected function filterMenu()
    {
        $this->dc->setButtonId('tl_buttons_a');
        $arrSortingFields = array();
        $arrSession = $this->Session->getData();
        $strFilter  = ($this->dca['list']['sorting']['mode'] == 4) ? $this->dc->getTable() . '_' . CURRENT_ID : $this->dc->getTable();

        // Get sorting fields
        foreach ($this->dca['fields'] as $k => $v)
        {
            if ($v['filter'])
            {
                $arrSortingFields[] = $k;
            }
        }

        // Return if there are no sorting fields
        if (empty($arrSortingFields))
        {
            return array();
        }

        // Set filter
        $arrSession = $this->filterMenuSetFilter($arrSortingFields, $arrSession, $strFilter);

        // Add options
        $arrPanelView = $this->filterMenuAddOptions($arrSortingFields, $arrSession, $strFilter);

        return $arrPanelView;
    }

    /**
     * Return a search form that allows to search results using regular expressions
     * 
     * @return string
     */
    protected function searchMenu()
    {
        $searchFields = array();
        $session      = $this->objSession->getData();
        $arrPanelView = array();

        // Get search fields
        foreach ($this->dca['fields'] as $k => $v)
        {
            if ($v['search'])
            {
                $searchFields[] = $k;
            }
        }

        // Return if there are no search fields
        if (empty($searchFields))
        {
            return array();
        }

        // Store search value in the current session
        if ($this->Input->post('FORM_SUBMIT') == 'tl_filters')
        {
            $session['search'][$this->dc->getTable()]['value'] = '';
            $session['search'][$this->dc->getTable()]['field'] = $this->Input->post('tl_field', true);

            // Make sure the regular expression is valid
            if ($this->Input->postRaw('tl_value') != '')
            {
                try
                {
                    $objConfig = $this->dc->getDataProvider()->getEmptyConfig()
                            ->setAmount(1)
                            ->setFilter(array($this->Input->post('tl_field', true) . " REGEXP '" . $this->Input->postRaw('tl_value') . "'"))
                            ->setSorting($this->getListViewSorting());

                    $this->dc->getDataProvider()->fetchAll($objConfig);

                    $session['search'][$this->dc->getTable()]['value'] = $this->Input->postRaw('tl_value');
                }
                catch (Exception $e)
                {
                    // Do nothing
                }
            }

            $this->objSession->setData($session);
        }

        // Set search value from session
        else if ($session['search'][$this->dc->getTable()]['value'] != '')
        {
            if (substr($GLOBALS['TL_CONFIG']['dbCollation'], -3) == '_ci')
            {
                $this->dc->setFilter(array("LOWER(CAST(" . $session['search'][$this->dc->getTable()]['field'] . " AS CHAR)) REGEXP LOWER('" . $session['search'][$this->dc->getTable()]['value'] . "')"));
            }
            else
            {
                $this->dc->setFilter(array("CAST(" . $session['search'][$this->dc->getTable()]['field'] . " AS CHAR) REGEXP '" . $session['search'][$this->dc->getTable()]['value'] . "'"));
            }
        }

        $arrOptions = array();

        foreach ($searchFields as $field)
        {
            $mixedOptionsLabel = strlen($this->dca['fields'][$field]['label'][0]) ? $this->dca['fields'][$field]['label'][0] : $GLOBALS['TL_LANG']['MSC'][$field];

            $arrOptions[utf8_romanize($mixedOptionsLabel) . '_' . $field] = array(
                'value'   => specialchars($field),
                'select'  => (($field == $session['search'][$this->dc->getTable()]['field']) ? ' selected="selected"' : ''),
                'content' => $mixedOptionsLabel
            );
        }

        // Sort by option values
        uksort($arrOptions, 'strcasecmp');
        $arrPanelView['option'] = $arrOptions;

        $active = strlen($session['search'][$this->dc->getTable()]['value']) ? true : false;

        $arrPanelView['select'] = array(
            'class' => 'tl_select' . ($active ? ' active' : '')
        );

        $arrPanelView['input'] = array(
            'class' => 'tl_text' . (($active) ? ' active' : ''),
            'value' => specialchars($session['search'][$this->dc->getTable()]['value'])
        );

        return $arrPanelView;
    }

    /**
     * Return a select menu to limit results
     * @param boolean
     * @return string
     */
    protected function limitMenu($blnOptional = false)
    {
        $arrPanelView = array();

        $session = $this->objSession->getData();

        $filter = ($this->dca['list']['sorting']['mode'] == 4) ? $this->dc->getTable() . '_' . CURRENT_ID : $this->dc->getTable();

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

            $this->objSession->setData($session);

            if ($this->Input->post('FORM_SUBMIT') == 'tl_filters_limit')
            {
                $this->reload();
            }
        }

        // Set limit from table configuration
        else
        {
            if (strlen($session['filter'][$filter]['limit']))
            {
                $this->dc->setLimit((($session['filter'][$filter]['limit'] == 'all') ? null : $session['filter'][$filter]['limit']));
            }
            else
            {
                $this->dc->setLimit('0,' . $GLOBALS['TL_CONFIG']['resultsPerPage']);
            }

            $intCount               = $this->dc->getDataProvider()->getCount($this->dc->getDataProvider()->getEmptyConfig()->setFilter($this->getFilter()));
            $blnIsMaxResultsPerPage = false;

            // Overall limit
            if ($intCount > $GLOBALS['TL_CONFIG']['maxResultsPerPage'] && (is_null($this->dc->getLimit()) || preg_replace('/^.*,/i', '', $this->dc->getLimit()) == $GLOBALS['TL_CONFIG']['maxResultsPerPage']))
            {
                if (is_null($this->dc->getLimit()))
                {
                    $this->dc->setLimit('0,' . $GLOBALS['TL_CONFIG']['maxResultsPerPage']);
                }

                $blnIsMaxResultsPerPage                 = true;
                $GLOBALS['TL_CONFIG']['resultsPerPage'] = $GLOBALS['TL_CONFIG']['maxResultsPerPage'];
                $session['filter'][$filter]['limit']    = $GLOBALS['TL_CONFIG']['maxResultsPerPage'];
            }

            // Build options
            if ($intCount > 0)
            {
                $arrPanelView['option'][0] = array();
                $options_total = ceil($intCount / $GLOBALS['TL_CONFIG']['resultsPerPage']);

                // Reset limit if other parameters have decreased the number of results
                if (!is_null($this->dc->getLimit()) && ($this->dc->getLimit() == '' || preg_replace('/,.*$/i', '', $this->dc->getLimit()) > $intCount))
                {
                    $this->dc->setLimit('0,' . $GLOBALS['TL_CONFIG']['resultsPerPage']);
                }

                // Build options
                for ($i = 0; $i < $options_total; $i++)
                {
                    $this_limit  = ($i * $GLOBALS['TL_CONFIG']['resultsPerPage']) . ',' . $GLOBALS['TL_CONFIG']['resultsPerPage'];
                    $upper_limit = ($i * $GLOBALS['TL_CONFIG']['resultsPerPage'] + $GLOBALS['TL_CONFIG']['resultsPerPage']);

                    if ($upper_limit > $intCount)
                    {
                        $upper_limit = $intCount;
                    }

                    $arrPanelView['option'][] = array(
                        'value'   => $this_limit,
                        'select'  => $this->optionSelected($this->dc->getLimit(), $this_limit),
                        'content' => ($i * $GLOBALS['TL_CONFIG']['resultsPerPage'] + 1) . ' - ' . $upper_limit
                    );
                }

                if (!$blnIsMaxResultsPerPage)
                {
                    $arrPanelView['option'][] = array(
                        'value'   => 'all',
                        'select'  => $this->optionSelected($this->dc->getLimit(), null),
                        'content' => $GLOBALS['TL_LANG']['MSC']['filterAll']
                    );
                }
            }

            // Return if there is only one page
            if ($blnOptional && ($intCount < 1 || $options_total < 2))
            {
                return array();
            }

            $arrPanelView['select'] = array(
                'class' => (($session['filter'][$filter]['limit'] != 'all' && $intCount > $GLOBALS['TL_CONFIG']['resultsPerPage']) ? ' active' : '')
            );

            $arrPanelView['option'][0] = array(
                'value'   => 'tl_limit',
                'select'  => '',
                'content' => $GLOBALS['TL_LANG']['MSC']['filterRecords']
            );
        }

        return $arrPanelView;
    }

    /**
     * Return a select menu that allows to sort results by a particular field
     * @return string
     */
    protected function sortMenu()
    {
        $arrPanelView = array();

        if ($this->dca['list']['sorting']['mode'] != 2 && $this->dca['list']['sorting']['mode'] != 4)
        {
            return array();
        }

        $sortingFields = array();

        // Get sorting fields
        foreach ($this->dca['fields'] as $k => $v)
        {
            if ($v['sorting'])
            {
                $sortingFields[] = $k;
            }
        }

        // Return if there are no sorting fields
        if (empty($sortingFields))
        {
            return array();
        }

        $this->dc->setButtonId('tl_buttons_a');
        $session      = $this->objSession->getData();
        $orderBy      = $this->dca['list']['sorting']['fields'];
        $firstOrderBy = preg_replace('/\s+.*$/i', '', $orderBy[0]);

        // Add PID to order fields
        if ($this->dca['list']['sorting']['mode'] == 3 && $this->dc->getDataProvider()->fieldExists('pid'))
        {
            array_unshift($orderBy, 'pid');
        }

        // Set sorting from user input
        if ($this->Input->post('FORM_SUBMIT') == 'tl_filters')
        {
            $session['sorting'][$this->dc->getTable()] = in_array($this->dca['fields'][$this->Input->post('tl_sort')]['flag'], array(2, 4, 6, 8, 10, 12)) ? $this->Input->post('tl_sort') . ' DESC' : $this->Input->post('tl_sort');
            $this->objSession->setData($session);
        }

        // Overwrite the "orderBy" value with the session value
        elseif (strlen($session['sorting'][$this->dc->getTable()]))
        {
            $overwrite = preg_quote(preg_replace('/\s+.*$/i', '', $session['sorting'][$this->dc->getTable()]), '/');
            $orderBy   = array_diff($orderBy, preg_grep('/^' . $overwrite . '/i', $orderBy));

            array_unshift($orderBy, $session['sorting'][$this->dc->getTable()]);

            $this->dc->setFirstSorting($overwrite);
            $this->dc->setSorting($orderBy);
        }

        $arrOptions = array();

        foreach ($sortingFields as $field)
        {
            $mixedOptionsLabel = strlen($this->dca['fields'][$field]['label'][0]) ? $this->dca['fields'][$field]['label'][0] : $GLOBALS['TL_LANG']['MSC'][$field];

            if (is_array($mixedOptionsLabel))
            {
                $mixedOptionsLabel = $mixedOptionsLabel[0];
            }

            $arrOptions[$mixedOptionsLabel] = array(
                'value'   => specialchars($field),
                'select'  => ((!strlen($session['sorting'][$this->dc->getTable()]) && $field == $firstOrderBy || $field == str_replace(' DESC', '', $session['sorting'][$this->dc->getTable()])) ? ' selected="selected"' : ''),
                'content' => $mixedOptionsLabel
            );
        }

        // Sort by option values
        uksort($arrOptions, 'strcasecmp');
        $arrPanelView['option'] = $arrOptions;

        return $arrPanelView;
    }

    // Helper ------------------------------------------------------------------

    /**
     * Get filter for the data provider
     * 
     * @return array();
     */
    protected function getFilter()
    {
        $arrFilterIds = $this->dca['list']['sorting']['root'];

        // TODO implement panel filter from session
        $arrFilter = $this->dc->getFilter();
        if (is_array($arrFilterIds) && !empty($arrFilterIds))
        {
            if (is_null($arrFilter))
            {
                $arrFilter = array();
            }

            $arrFilter['id'] = array_map('intval', $arrFilterIds);
        }

        return $arrFilter;
    }

    /**
     * Get limit for the data provider
     * 
     * @return array 
     */
    protected function getLimit()
    {
        $arrLimit = array(0, 0);
        if (!is_null($this->dc->getLimit()))
        {
            $arrLimit = explode(',', $this->dc->getLimit());
        }

        return $arrLimit;
    }

    /**
     * Get sorting for the list view data provider
     * 
     * @return mixed 
     */
    protected function getListViewSorting()
    {
        $mixedOrderBy = $this->dca['list']['sorting']['fields'];

        if (is_null($this->dc->getFirstSorting()))
        {
            $this->dc->setFirstSorting(preg_replace('/\s+.*$/i', '', $mixedOrderBy[0]));
        }

        // Check if current sorting is set
        if (!is_null($this->dc->getSorting()))
        {
            $mixedOrderBy = $this->dc->getSorting();
        }

        if (is_array($mixedOrderBy) && $mixedOrderBy[0] != '')
        {
            foreach ($mixedOrderBy as $key => $strField)
            {
                if ($this->dca['fields'][$strField]['eval']['findInSet'])
                {
                    $arrOptionsCallback = $this->dc->getCallbackClass()->optionsCallback($strField);

                    if (!is_null($arrOptionsCallback))
                    {
                        $keys = $arrOptionsCallback;
                    }
                    else
                    {
                        $keys = $this->dca['fields'][$strField]['options'];
                    }

                    if ($this->dca['fields'][$v]['eval']['isAssociative'] || array_is_assoc($keys))
                    {
                        $keys = array_keys($keys);
                    }

                    $mixedOrderBy[$key] = array(
                        'field'  => $strField,
                        'keys'   => $keys,
                        'action' => 'findInSet'
                    );
                }
                else
                {
                    $mixedOrderBy[$key] = $strField;
                }
            }
        }

        // Set sort order
        if ($this->dca['list']['sorting']['mode'] == 1 && ($this->dca['list']['sorting']['flag'] % 2) == 0)
        {
            $mixedOrderBy['sortOrder'] = " DESC";
        }

        return $mixedOrderBy;
    }

    /**
     * Get sorting for the parent view data provider
     * 
     * @return mixed 
     */
    protected function getParentViewSorting()
    {
        $mixedOrderBy = array();
        $firstOrderBy = array();

        // Check if current sorting is set
        if (!is_null($this->dc->getSorting()))
        {
            $mixedOrderBy = $this->dc->getSorting();
        }

        if (is_array($mixedOrderBy) && $mixedOrderBy[0] != '')
        {
            $firstOrderBy = preg_replace('/\s+.*$/i', '', $mixedOrderBy[0]);

            // Order by the foreign key
            if (isset($this->dca['fields'][$firstOrderBy]['foreignKey']))
            {
                $key = explode('.', $this->dca['fields'][$firstOrderBy]['foreignKey'], 2);

                $this->foreignKey = true;

                // TODO remove sql
                $this->arrFields = array(
                    '*',
                    "(SELECT " . $key[1] . " FROM " . $key[0] . " WHERE " . $this->dc->getTable() . "." . $firstOrderBy . "=" . $key[0] . ".id) AS foreignKey"
                );

                $mixedOrderBy[0] = 'foreignKey';
            }
        }
        elseif (is_array($GLOBALS['TL_DCA'][$this->dc->getTable()]['list']['sorting']['fields']))
        {
            $mixedOrderBy = $GLOBALS['TL_DCA'][$this->dc->getTable()]['list']['sorting']['fields'];
            $firstOrderBy = preg_replace('/\s+.*$/i', '', $mixedOrderBy[0]);
        }

        if (is_array($mixedOrderBy) && $mixedOrderBy[0] != '')
        {
            foreach ($mixedOrderBy as $key => $strField)
            {
                $mixedOrderBy[$key] = $strField;
            }
        }

        $this->dc->setFirstSorting($firstOrderBy);

        return $mixedOrderBy;
    }

    /**
     * Set filter from user input and table configuration for filter menu
     * 
     * @param array $arrSortingFields
     * @param array $arrSession
     * @param string $strFilter
     * @return array 
     */
    protected function filterMenuSetFilter($arrSortingFields, $arrSession, $strFilter)
    {
        // Set filter from user input
        if ($this->Input->post('FORM_SUBMIT') == 'tl_filters')
        {
            foreach ($arrSortingFields as $field)
            {
                if ($this->Input->post($field, true) != 'tl_' . $field)
                {
                    $arrSession['filter'][$strFilter][$field] = $this->Input->post($field, true);
                }
                else
                {
                    unset($arrSession['filter'][$strFilter][$field]);
                }
            }

            $this->Session->setData($arrSession);
        }

        // Set filter from table configuration
        else
        {
            foreach ($arrSortingFields as $field)
            {
                if (isset($arrSession['filter'][$strFilter][$field]))
                {

                    // Sort by day
                    if (in_array($this->dca['fields'][$field]['flag'], array(5, 6)))
                    {
                        if ($arrSession['filter'][$strFilter][$field] == '')
                        {
                            $this->dc->setFilter(array($field . " = ''"));
                        }
                        else
                        {
                            $objDate = new Date($arrSession['filter'][$strFilter][$field]);
                            $this->dc->setFilter(array($field . " BETWEEN '" . $objDate->dayBegin . "' AND '" . $objDate->dayEnd . "'"));
                        }
                    }

                    // Sort by month
                    elseif (in_array($this->dca['fields'][$field]['flag'], array(7, 8)))
                    {
                        if ($arrSession['filter'][$strFilter][$field] == '')
                        {
                            $this->dc->setFilter(array($field . " = ''"));
                        }
                        else
                        {
                            $objDate = new Date($arrSession['filter'][$strFilter][$field]);
                            $this->dc->setFilter(array($field . " BETWEEN '" . $objDate->monthBegin . "' AND '" . $objDate->monthEnd . "'"));
                        }
                    }

                    // Sort by year
                    elseif (in_array($this->dca['fields'][$field]['flag'], array(9, 10)))
                    {
                        if ($arrSession['filter'][$strFilter][$field] == '')
                        {
                            $this->dc->setFilter(array($field . " = ''"));
                        }
                        else
                        {
                            $objDate = new Date($arrSession['filter'][$strFilter][$field]);
                            $this->dc->setFilter(array($field . " BETWEEN '" . $objDate->yearBegin . "' AND '" . $objDate->yearEnd . "'"));
                        }
                    }

                    // Manual filter
                    elseif ($this->dca['fields'][$field]['eval']['multiple'])
                    {
                        // TODO fiond in set
                        // CSV lists (see #2890)
                        /* if (isset($this->dca['fields'][$field]['eval']['csv']))
                          {
                          $this->procedure[] = $this->Database->findInSet('?', $field, true);
                          $this->values[] = $session['filter'][$filter][$field];
                          }
                          else
                          {
                          $this->procedure[] = $field . ' LIKE ?';
                          $this->values[] = '%"' . $session['filter'][$filter][$field] . '"%';
                          } */
                    }

                    // Other sort algorithm
                    else
                    {
                        $this->dc->setFilter(array($field . " = '" . $arrSession['filter'][$strFilter][$field] . "'"));
                    }
                }
            }
        }

        return $arrSession;
    }

    /**
     * Add sorting options to filter menu
     * 
     * @param array $arrSortingFields
     * @param array $arrSession
     * @param string $strFilter
     * @return array 
     */
    protected function filterMenuAddOptions($arrSortingFields, $arrSession, $strFilter)
    {
        $arrPanelView = array();

        // Add sorting options
        foreach ($arrSortingFields as $cnt => $field)
        {
            $arrProcedure = array();

            if ($this->dca['list']['sorting']['mode'] == 4)
            {

                $arrProcedure[] = "pid = '" . CURRENT_ID . "'";
            }

            if (!is_null($this->dc->getRootIds()) && is_array($this->dc->getRootIds()))
            {
                $arrProcedure[] = "id IN(" . implode(',', array_map('intval', $this->dc->getRootIds())) . ")";
            }

            $objCollection = $this->dc->getDataProvider()->fetchAll($this->dc->getDataProvider()->getEmptyConfig()->setFields(array("DISTINCT(" . $field . ")"))->setFilter($arrProcedure));

            // Begin select menu            
            $arrPanelView[$field] = array(
                'select' => array(
                    'name'   => $field,
                    'id'     => $field,
                    'class'  => 'tl_select' . (isset($arrSession['filter'][$strFilter][$field]) ? ' active' : '')
                ),
                'option' => array(
                    array(
                        'value'   => 'tl_' . $field,
                        'content' => (is_array($this->dca['fields'][$field]['label']) ? $this->dca['fields'][$field]['label'][0] : $this->dca['fields'][$field]['label'])
                    ),
                    array(
                        'value'   => 'tl_' . $field,
                        'content' => '---'
                    )
                )
            );

            if ($objCollection->length() > 0)
            {
                $options = array();

                foreach ($objCollection as $intIndex => $objModel)
                {
                    $options[$intIndex] = $objModel->getProperty($field);
                }

                // Sort by day
                if (in_array($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['flag'], array(5, 6)))
                {
                    $this->arrColSort = array(
                        'field'   => $field,
                        'reverse' => ($this->dca['fields'][$field]['flag'] == 6) ? true : false
                    );

                    $objCollection->sort(array($this, 'sortCollection'));

                    foreach ($objCollection as $intIndex => $objModel)
                    {
                        if ($objModel->getProperty($field) == '')
                        {
                            $options[$objModel->getProperty($field)] = '-';
                        }
                        else
                        {
                            $options[$objModel->getProperty($field)] = $this->parseDate($GLOBALS['TL_CONFIG']['dateFormat'], $objModel->getProperty($field));
                        }

                        unset($options[$intIndex]);
                    }
                }

                // Sort by month
                elseif (in_array($this->dca['fields'][$field]['flag'], array(7, 8)))
                {
                    $this->arrColSort = array(
                        'field'   => $field,
                        'reverse' => ($this->dca['fields'][$field]['flag'] == 8) ? true : false
                    );

                    $objCollection->sort(array($this, 'sortCollection'));

                    foreach ($objCollection as $intIndex => $objModel)
                    {
                        if ($objModel->getProperty($field) == '')
                        {
                            $options[$objModel->getProperty($field)] = '-';
                        }
                        else
                        {
                            $options[$objModel->getProperty($field)] = date('Y-m', $objModel->getProperty($field));
                            $intMonth                                = (date('m', $objModel->getProperty($field)) - 1);

                            if (isset($GLOBALS['TL_LANG']['MONTHS'][$intMonth]))
                            {
                                $options[$objModel->getProperty($field)] = $GLOBALS['TL_LANG']['MONTHS'][$intMonth] . ' ' . date('Y', $objModel->getProperty($field));
                            }
                        }

                        unset($options[$intIndex]);
                    }
                }

                // Sort by year
                elseif (in_array($this->dca['fields'][$field]['flag'], array(9, 10)))
                {
                    $this->arrColSort = array(
                        'field'   => $field,
                        'reverse' => ($this->dca['fields'][$field]['flag'] == 10) ? true : false
                    );

                    $objCollection->sort(array($this, 'sortCollection'));

                    foreach ($objCollection as $intIndex => $objModel)
                    {
                        if ($objModel->getProperty($field) == '')
                        {
                            $options[$objModel->getProperty($field)] = '-';
                        }
                        else
                        {
                            $options[$objModel->getProperty($field)] = date('Y', $objModel->getProperty($field));
                        }

                        unset($options[$intIndex]);
                    }
                }

                // Manual filter
                if ($this->dca['fields'][$field]['eval']['multiple'])
                {
                    $moptions = array();

                    foreach ($objCollection as $objModel)
                    {
                        if (isset($this->dca['fields'][$field]['eval']['csv']))
                        {
                            $doptions = trimsplit($this->dca['fields'][$field]['eval']['csv'], $objModel->getProperty($field));
                        }
                        else
                        {
                            $doptions = deserialize($objModel->getProperty($field));
                        }

                        if (is_array($doptions))
                        {
                            $moptions = array_merge($moptions, $doptions);
                        }
                    }

                    $options = $moptions;
                }

                $options            = array_unique($options);
                $arrOptionsCallback = array();

                // Load options callback
                if (is_array($this->dca['fields'][$field]['options_callback']) && !$this->dca['fields'][$field]['reference'])
                {
                    $arrOptionsCallback = $this->dc->getCallbackClass()->optionsCallback($field);

                    // Sort options according to the keys of the callback array
                    if (!is_null($arrOptionsCallback))
                    {
                        $options = array_intersect(array_keys($arrOptionsCallback), $options);
                    }
                }

                $arrOptions = array();
                $arrSortOptions = array();
                $blnDate = in_array($this->dca['fields'][$field]['flag'], array(5, 6, 7, 8, 9, 10));

                // Options                
                foreach ($options as $kk => $vv)
                {
                    $value = $blnDate ? $kk : $vv;

                    // Replace the ID with the foreign key
                    if (isset($this->dca['fields'][$field]['foreignKey']))
                    {
                        $key = explode('.', $this->dca['fields'][$field]['foreignKey'], 2);

                        $objModel = $this->dc->getDataProvider($key[0])->fetch(
                                $this->dc->getDataProvider($key[0])->getEmptyConfig()
                                        ->setId($vv)
                                        ->setFields(array($key[1] . ' AS value'))
                        );

                        if ($objModel->hasProperties())
                        {
                            $vv = $objModel->getProperty('value');
                        }
                    }

                    // Replace boolean checkbox value with "yes" and "no"
                    elseif ($this->dca['fields'][$field]['eval']['isBoolean'] || ($this->dca['fields'][$field]['inputType'] == 'checkbox' && !$this->dca['fields'][$field]['eval']['multiple']))
                    {
                        $vv = ($vv != '') ? $GLOBALS['TL_LANG']['MSC']['yes'] : $GLOBALS['TL_LANG']['MSC']['no'];
                    }

                    // Options callback
                    elseif (!is_null($arrOptionsCallback))
                    {
                        $vv = $arrOptionsCallback[$vv];
                    }

                    // Get the name of the parent record
                    elseif ($field == 'pid')
                    {
                        // Load language file and data container array of the parent table
                        $this->loadLanguageFile($this->dc->getParentTable());
                        $this->loadDataContainer($this->dc->getParentTable());

                        $objParentDC  = new DC_General($this->dc->getParentTable());
                        $arrParentDca = $objParentDC->getDCA();

                        $showFields = $arrParentDca['list']['label']['fields'];

                        if (!$showFields[0])
                        {
                            $showFields[0] = 'id';
                        }

                        $objModel = $this->dc->getDataProvider('parent')->fetch(
                                $this->dc->getDataProvider('parent')->getEmptyConfig()
                                        ->setId($vv)
                                        ->setFields(array($showFields[0]))
                        );

                        if ($objModel->hasProperties())
                        {
                            $vv = $objModel->getProperty($showFields[0]);
                        }
                    }

                    $strOptionsLabel = '';

                    // Use reference array
                    if (isset($this->dca['fields'][$field]['reference']))
                    {
                        $strOptionsLabel = is_array($this->dca['fields'][$field]['reference'][$vv]) ? $this->dca['fields'][$field]['reference'][$vv][0] : $this->dca['fields'][$field]['reference'][$vv];
                    }

                    // Associative array
                    elseif ($this->dca['fields'][$field]['eval']['isAssociative'] || array_is_assoc($this->dca['fields'][$field]['options']))
                    {
                        $strOptionsLabel = $this->dca['fields'][$field]['options'][$vv];
                    }

                    // No empty options allowed
                    if (!strlen($strOptionsLabel))
                    {
                        $strOptionsLabel = strlen($vv) ? $vv : '-';
                    }

                    $arrOptions[utf8_romanize($strOptionsLabel)] = array(
                        'value'   => specialchars($value),
                        'select'  => ((isset($arrSession['filter'][$strFilter][$field]) && $value == $arrSession['filter'][$strFilter][$field]) ? ' selected="selected"' : ''),
                        'content' => $strOptionsLabel
                    );

                    $arrSortOptions[] = utf8_romanize($strOptionsLabel);
                }

                // Sort by option values
                if (!$blnDate)
                {
                    natcasesort($arrSortOptions);

                    if (in_array($this->dca['fields'][$field]['flag'], array(2, 4, 12)))
                    {
                        $arrSortOptions = array_reverse($arrSortOptions, true);
                    }
                }

                foreach ($arrSortOptions as $value)
                {
                    $arrPanelView[$field]['option'][] = $arrOptions[$value];
                }
            }
            // Force a line-break after six elements
            if ((($cnt + 1) % 6) == 0)
            {
                $arrPanelView[] = 'new';
            }
        }

        return $arrPanelView;
    }

    public function sortCollection(InterfaceGeneralModel $a, InterfaceGeneralModel $b)
    {
        if ($a->getProperty($this->arrColSort['field']) == $b->getProperty($this->arrColSort['field']))
        {
            return 0;
        }

        if (!$this->arrColSort['reverse'])
        {
            return ($a->getProperty($this->arrColSort['field']) < $b->getProperty($this->arrColSort['field'])) ? -1 : 1;
        }
        else
        {
            return ($a->getProperty($this->arrColSort['field']) < $b->getProperty($this->arrColSort['field'])) ? 1 : -1;
        }
    }

}

?>
