<?php

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
class DC_General extends DataContainer implements editable, listable
{
    // Vars --------------------------------------------------------------------

    /**
     * Id of the item currently in edit view 
     * @var int 
     */
    protected $intId = null;

    /**
     * List with all ids for the views
     * @var type 
     */
    protected $arrRootIds = null;

    /**
     * Container for panel information
     * @var array
     */
    protected $arrPanelView = null;

    /**
     * Name of current table
     * @var String 
     */
    protected $strTable = null;

    /**
     * Name of the parent table
     * @var String 
     */
    protected $strParentTable = null;

    /**
     * Name of the child table
     * @var String 
     */
    protected $strChildTable = null;

    /**
     * Name of current field
     * @var String 
     */
    protected $strField = null;

    /**
     * Parameter to sort the collection
     * @var array
     */
    protected $arrSorting = null;

    /**
     * Value for the first sorting
     * @var string
     */
    protected $strFirstSorting = null;

    /**
     * Parameter to filter the collection
     * @var array
     */
    protected $arrFilter = null;

    /**
     * Value vor the limit in the view
     * @var string
     */
    protected $strLimit = null;

    /**
     * DCA configuration
     * @var array 
     */
    protected $arrDCA = null;

    /**
     * State of dca
     * @var boolean 
     */
    protected $blnSubmitted = false;

    /**
     * State of auto submit
     * @var boolean 
     */
    protected $blnAutoSubmitted = false;

    /**
     * State of versionsubmit
     * @var boolean 
     */
    protected $blnVersionSubmit = false;

    /**
     * Flag to show if the site can be reloaded
     * @var boolean 
     */
    protected $blnNoReload = false;

    /**
     * True if we have a widget which is uploadable
     * @var boolean 
     */
    protected $blnUploadable = false;

    /**
     * Input values
     * @var array
     */
    protected $arrInputs = array();

    /**
     * Fieldstate information
     * @var array
     */
    protected $arrStates = array();

    /**
     * A list with all field for this dca
     * @var array 
     */
    protected $arrFields = array();

    /**
     * A list with all widgets
     * @var array 
     */
    protected $arrWidgets = array();

    /**
     * List with all procesed widgets from submit.
     * @var array 
     */
    protected $arrProcessedWidgets = array();

    /**
     * The iltimate id for widgets
     * @var type 
     */
    protected $mixWidgetID = null;

    /**
     * Current collection
     * @var InterfaceGeneralCollection 
     */
    protected $objCurrentCollecion = null;

    /**
     * Current model
     * @var InterfaceGeneralModel 
     */
    protected $objCurrentModel = null;

    /**
     * The provider that shall be used for data retrival.
     * @var InterfaceGeneralData 
     */
    protected $objDataProvider = null;

    /**
     * The provider that shall be used for data retrival.
     * @var InterfaceGeneralData
     */
    protected $objParentDataProvider = null;

    /**
     * The provider that shall be used for data retrival.
     * @var InterfaceGeneralData
     */
    protected $objChildDataProvider = null;

    /**
     * ID of the button container
     * @param string
     */
    protected $strButtonId = null;

    /**
     * The provider that shall be used for view retrival.
     * @var InterfaceGeneralView 
     */
    protected $objViewHandler = null;

    /**
     * The controller that shall be used .
     * @var InterfaceGeneralController 
     */
    protected $objController = null;

    /**
     * The class with all Callbacks
     * @var InterfaceGeneralCallback 
     */
    protected $objCallbackClass = null;

    /**
     * Lookup for special regex
     * @var array 
     */
    private static $arrDates = array(
        'date' => true,
        'time' => true,
        'datim' => true
    );

    // Constructor and co. -----------------------------------------------------

    public function __construct($strTable, array $arrDCA = null, $blnOnloadCallback = true)
    {
        parent::__construct();
        
        // Callback
        $strTable = $this->oncreateCallback($strTable);

        // Basic vars Init
        $this->strTable = $strTable;
        $this->arrDCA = ($arrDCA != null) ? $arrDCA : $GLOBALS['TL_DCA'][$this->strTable];

        // Check whether the table is defined
        if (!strlen($this->strTable) || !count($this->arrDCA))
        {
            $this->log('Could not load data container configuration for "' . $strTable . '"', 'DC_Table __construct()', TL_ERROR);
            trigger_error('Could not load data container configuration', E_USER_ERROR);
        }

        // Import
        $this->import('Encryption');
        // ToDo: SH: Switch FE|BE user =?
        $this->import('BackendUser', 'User');

        // Set vars
        $this->intId = $this->Input->get('id');
        $this->blnSubmitted = $_POST['FORM_SUBMIT'] == $this->strTable;
        $this->blnAutoSubmitted = $_POST['SUBMIT_TYPE'] == 'auto';
        $this->blnVersionSubmit = $_POST['FORM_SUBMIT'] == 'tl_version';
        $this->arrInputs = $_POST['FORM_INPUTS'] ? array_flip($this->Input->post('FORM_INPUTS')) : array();
        $this->arrStates = $this->Session->get('fieldset_states');
        $this->arrStates = (array) $this->arrStates[$this->strTable];

        // Load
        $this->loadProviderAndHandler();

        // Callback
        if ($blnOnloadCallback == true)
        {
            $this->objCallbackClass->onloadCallback($strTable);
        }
    }

    /**
     * Call the oncreate callback 
     * 
     * @param string $strTable
     * @return string name of current table
     */
    protected function oncreateCallback($strTable)
    {
        $strCurrentTable = $strTable;

        if (array_key_exists('oncreate_callback', $GLOBALS['TL_DCA'][$strTable]['config']) && is_array($GLOBALS['TL_DCA'][$strTable]['config']['oncreate_callback']))
        {
            foreach ($GLOBALS['TL_DCA'][$strTable]['config']['oncreate_callback'] as $callback)
            {
                $this->import($callback[0]);
                $strCurrentTable = $this->$callback[0]->$callback[1]($strTable, $this);

                if ($strCurrentTable != null)
                {
                    $strTable = $strCurrentTable;
                }
            }
        }
        
        return $strTable;
    }

    /**
     * Load the dataprovider and view handler, 
     * if not set try to load the default one.
     */
    protected function loadProviderAndHandler()
    {
        
        // Load controller, view and provider.
        $this->loadController();
        $this->loadView();
        $this->loadDataProvider();
        $this->loadCallbackClass();
    }

    /**
     * Load the datacontroller, 
     * if not set try to load the default one.
     */
    protected function loadController()
    {
        // Load controller
        if (isset($this->arrDCA['dca_config']['controller']) && isset($this->arrDCA['dca_config']['controller_config']))
        {
            $arrConfig = $this->arrDCA['dca_config']['controller_config'];
            $this->objController = new $this->arrDCA['dca_config']['controller']();
        }
        else if (isset($this->arrDCA['dca_config']['controller']) && !isset($this->arrDCA['dca_config']['controller_config']))
        {
            $arrConfig = array();
            $this->objController = new $this->arrDCA['dca_config']['controller']();
        }
        else
        {
            $arrConfig = array();
            $this->objController = new GeneralControllerDefault();
        }
    }

    /**
     * Load the dataview handler, 
     * if not set try to load the default one.
     */
    protected function loadView()
    {
        // Load view
        if (isset($this->arrDCA['dca_config']['view']) && isset($this->arrDCA['dca_config']['view_config']))
        {
            $arrConfig = $this->arrDCA['dca_config']['view_config'];
            $this->objViewHandler = new $this->arrDCA['dca_config']['view']();
        }
        else if (isset($this->arrDCA['dca_config']['view']) && !isset($this->arrDCA['dca_config']['view_config']))
        {
            $arrConfig = array();
            $this->objViewHandler = new $this->arrDCA['dca_config']['view']();
        }
        else
        {
            $arrConfig = array();
            $this->objViewHandler = new GeneralViewDefault();
        }
    }

    /**
     * Load the dataprovider, 
     * if not set try to load the default one.
     */
    protected function loadDataProvider()
    {
        if (isset($this->arrDCA['dca_config']['data']) && isset($this->arrDCA['dca_config']['data_config']))
        {
            $arrConfig = $this->arrDCA['dca_config']['data_config'];
            $this->objDataProvider = new $this->arrDCA['dca_config']['data']($arrConfig);
        }
        else if (isset($this->arrDCA['dca_config']['data']) && !isset($this->arrDCA['dca_config']['data_config']))
        {
            $arrConfig = array();
            $this->objDataProvider = new $this->arrDCA['dca_config']['data']($arrConfig);
        }
        else
        {
            $arrConfig = array(
                "source" => $this->strTable,
            );
            
            $this->objDataProvider = new GeneralDataDefault($arrConfig);
        }

        // Load parent data provider
        if (isset($this->arrDCA['dca_config']['data_parent']) && isset($this->arrDCA['dca_config']['data_parent_config']))
        {
            if (isset($this->arrDCA['dca_config']['data_parent_config']['source']))
            {
                $this->strParentTable = $this->arrDCA['dca_config']['data_parent_config']['source'];
            }

            $arrConfig = $this->arrDCA['dca_config']['data_parent_config'];
            $this->objParentDataProvider = new $this->arrDCA['dca_config']['data_parent']($arrConfig);
        }
        else if (isset($this->arrDCA['dca_config']['data_parent']) && !isset($this->arrDCA['dca_config']['data_parent_config']))
        {
            $arrConfig = array();
            $this->objParentDataProvider = new $this->arrDCA['dca_config']['data_parent']($arrConfig);
        }

        // Load child data provider
        if (isset($this->arrDCA['dca_config']['data_child']) && isset($this->arrDCA['dca_config']['data_child_config']))
        {
            if (isset($this->arrDCA['dca_config']['data_child_config']['source']))
            {
                $this->strChildTable = $this->arrDCA['dca_config']['data_child_config']['source'];
            }

            $arrConfig = $this->arrDCA['dca_config']['data_child_config'];
            $this->objChildDataProvider = new $this->arrDCA['dca_config']['data_child']($arrConfig);
        }
        else if (isset($this->arrDCA['dca_config']['data_child']) && !isset($this->arrDCA['dca_config']['data_child_config']))
        {
            $arrConfig = array();
            $this->objChildDataProvider = new $this->arrDCA['dca_config']['data_child']($arrConfig);
        }
    }

    /**
     * Load the main callback class 
     */
    protected function loadCallbackClass()
    {
        // Load callback class
        if (isset($this->arrDCA['dca_config']['callback']))
        {
            $this->objCallbackClass = new $this->arrDCA['dca_config']['callback']();
        }
        else
        {
            $this->objCallbackClass = new GeneralCallbackDefault();
        }        
        
        $this->objCallbackClass->setDC($this);
    }

    /**
     * Load a list with all editable field
     * 
     * @param boolean $blnUserSelection
     * @return boolean 
     */
    public function loadEditableFields()
    {
        $this->arrFields = array_flip(array_keys(array_filter($this->arrDCA['fields'], create_function('$arr', 'return !$arr[\'exclude\'];'))));
    }

    // Getter and Setter -------------------------------------------------------

    public function getId()
    {
        return $this->intId;
    }

    public function getRootIds()
    {
        return $this->arrRootIds;
    }

    public function getTable()
    {
        return $this->strTable;
    }

    public function getParentTable()
    {
        return $this->strParentTable;
    }

    public function getChildTable()
    {
        return $this->strChildTable;
    }

    public function getSorting()
    {
        return $this->arrSorting;
    }

    public function getFirstSorting()
    {
        return $this->strFirstSorting;
    }

    public function getFilter()
    {
        return $this->arrFilter;
    }

    public function getLimit()
    {
        return $this->strLimit;
    }

    public function getDCA()
    {
        return $this->arrDCA;
    }

    public function isSubmitted()
    {
        return $this->blnSubmitted;
    }

    public function isAutoSubmitted()
    {
        return $this->blnAutoSubmitted;
    }

    public function isVersionSubmit()
    {
        return $this->blnVersionSubmit;
    }

    public function isNoReload()
    {
        return $this->blnNoReload;
    }

    public function getInputs()
    {
        return $this->arrInputs;
    }

    public function getStates()
    {
        return $this->arrStates;
    }

    public function getDataProvider()
    {
        return $this->objDataProvider;
    }

    public function getParentDataProvider()
    {
        return $this->objParentDataProvider;
    }

    public function getChildDataProvider()
    {
        return $this->objChildDataProvider;
    }

    public function getViewHandler()
    {
        return $this->objViewHandler;
    }
    
    /**
     * et the callback class for this dc
     * @return InterfaceGeneralCallback 
     */
    public function getCallbackClass()
    {
        return $this->objCallbackClass;
    }

    public function getButtonId()
    {
        return $this->strButtonId;
    }

    public function getObjController()
    {
        return $this->objController;
    }

    public function setRootIds($arrRootIds)
    {
        $this->arrRootIds = $arrRootIds;
    }

    public function setPanelView($arrPanelView)
    {
        $this->arrPanelView = $arrPanelView;
    }

    public function getPanelView()
    {
        return $this->arrPanelView;
    }

    public function setParentTable($strParentTable)
    {
        $this->strParentTable = $strParentTable;
    }

    public function setChildTable($strChildTable)
    {
        $this->strChildTable = $strChildTable;
    }

    public function setSorting($arrSorting)
    {
        $this->arrSorting = $arrSorting;
    }

    public function setFirstSorting($strFirstSorting)
    {
        $this->strFirstSorting = $strFirstSorting;
    }

    public function setFilter($arrFilter)
    {
        if (is_array($this->arrFilter))
        {
            $this->arrFilter = array_merge($this->arrFilter, $arrField);
        }
        $this->arrFilter = $arrFilter;
    }

    public function setLimit($strLimit)
    {
        $this->strLimit = $strLimit;
    }

    public function setDataProvider($objDataProvider)
    {
        $this->objDataProvider = $objDataProvider;
    }

    public function setParentDataProvider($objParentDataProvider)
    {
        $this->objParentDataProvider = $objParentDataProvider;
    }

    public function setChildDataProvider($objChildDataProvider)
    {
        $this->objChildDataProvider = $objChildDataProvider;
    }

    public function setViewHandler($objViewHandler)
    {
        $this->objViewHandler = $objViewHandler;
    }

    public function setButtonId($strButtonId)
    {
        $this->strButtonId = $strButtonId;
    }

    public function setControllerHandler($objController)
    {
        $this->objController = $objController;
    }

    /**
     *
     * @return InterfaceGeneralCollection 
     */
    public function getCurrentCollecion()
    {
        return $this->objCurrentCollecion;
    }

    /**
     *
     * @param InterfaceGeneralCollection $objCurrentCollecion 
     */
    public function setCurrentCollecion(InterfaceGeneralCollection $objCurrentCollecion)
    {
        $this->objCurrentCollecion = $objCurrentCollecion;
    }

    /**
     *
     * @return InterfaceGeneralModel 
     */
    public function getCurrentModel()
    {
        return $this->objCurrentModel;
    }

    /**
     *
     * @param InterfaceGeneralModel $objCurrentModel 
     */
    public function setCurrentModel(InterfaceGeneralModel $objCurrentModel)
    {
        $this->objCurrentModel = $objCurrentModel;
    }

    /**
     * Check if this DCA is editable
     * 
     * @return boolean 
     */
    public function isEditable()
    {
        return !$this->arrDCA['config']['notEditable'];
    }

    /**
     * Check if the field is edtiable
     * 
     * @param string $strField
     * @return boolean 
     */
    public function isEditableField($strField)
    {
        return isset($this->arrFields[$strField]);
    }

    /**
     * Check if we have editable fields
     * 
     * @return boolean 
     */
    public function hasEditableFields()
    {
        return count($this->arrFields) != 0 ? true : false;
    }

    /**
     * True if we have a ubloadable widget
     * 
     * @return boolean 
     */
    public function isUploadable()
    {
        return $this->blnUploadable;
    }

    /**
     * Get subpalettes definition
     * 
     * @return array 
     */
    public function getSubpalettesDefinition()
    {
        return is_array($this->arrDCA['subpalettes']) ? $this->arrDCA['subpalettes'] : array();
    }

    /**
     * Get palettes definition
     * 
     * @return array 
     */
    public function getPalettesDefinition()
    {
        return is_array($this->arrDCA['palettes']) ? $this->arrDCA['palettes'] : array();
    }

    /**
     * Get field definition
     * 
     * @return array 
     */
    public function getFieldDefinition($strField)
    {
        return is_array($this->arrDCA['fields'][$strField]) ? $this->arrDCA['fields'][$strField] : null;
    }

    /**
     * Return a list with all fields
     * 
     * @return array 
     */
    public function getFieldList()
    {
        return is_array($this->arrDCA['fields']) ? $this->arrDCA['fields'] : array();
    }

    /**
     * Return a list with all buttons
     * 
     * @return array 
     */
    public function getButtonsDefinition()
    {
        return is_array($this->arrDCA['buttons']) ? $this->arrDCA['buttons'] : array();
    }

    /**
     * Load for a button the language tag
     * 
     * @return array 
     */
    public function getButtonLabel($strButton)
    {
        if (isset($GLOBALS['TL_LANG'][$this->strTable][$strButton]))
        {
            return $GLOBALS['TL_LANG'][$this->strTable][$strButton];
        }
        else if (isset($GLOBALS['TL_LANG']['MSC'][$strButton]))
        {
            return $GLOBALS['TL_LANG']['MSC'][$strButton];
        }
        else
        {
            return $strButton;
        }
    }

    /**
     * Load for each button the language tag
     * 
     * @return array 
     */
    public function getButtonLabels()
    {
        $arrButtons = array();

        foreach (array_keys($this->getButtonsDefinition()) as $strButton)
        {
            $arrButtons[$strButton] = $this->getButtonLabel($strButton);
        }

        return $arrButtons;
    }

    /**
     * Add a Button to the dca
     * 
     * @param String $strButton 
     */
    public function addButton($strButton)
    {
        $this->arrDCA['buttons'][$strButton] = $strButton;
    }

    /**
     * Remove a button from dca
     * 
     * @param Stirng $strButton 
     */
    public function removeButton($strButton)
    {
        if (key_exists($strButton, $this->arrDCA['buttons']))
        {
            unset($this->arrDCA['buttons'][$strButton]);
        }
    }

    /**
     * Set/Create a widget id
     * 
     * @param int $intID 
     */
    public function setWidgetID($intID)
    {
        if (preg_match('/^[0-9]+$/', $intID))
        {
            $this->mixWidgetID = intval($intID);
        }
        else
        {
            $this->mixWidgetID = 'b' . str_replace('=', '_', base64_encode($intID));
        }
    }

    // Functions ---------------------------------------------------------------

    /**
     * Get/Create a widget 
     * 
     * @param string $strField
     * @return Widget
     */
    public function getWidget($strField)
    {
        // Load from chache
        if (isset($this->arrWidgets[$strField]))
        {
            return $this->arrWidgets[$strField];
        }

        // Check if editable
        if (!$this->isEditableField($strField))
        {
            return "";
        }

        // Get config and check it
        $arrConfig = $this->getFieldDefinition($strField);
        if (count($arrConfig) == 0)
        {
            return "";
        }

        $strInputName = $strField . '_' . $this->mixWidgetID;

        /* $arrConfig['eval']['encrypt'] ? $this->Encryption->decrypt($this->objActiveRecord->$strField) : */
        $varValue = deserialize($this->objCurrentModel->getProperty($strField));

        // Load Callback
        if (is_array($arrConfig['load_callback']))
        {
            foreach ($arrConfig['load_callback'] as $arrCallback)
            {
                if (is_array($arrCallback))
                {
                    $this->import($arrCallback[0]);
                    $varValue = $this->$arrCallback[0]->$arrCallback[1]($varValue, $this);
                }
            }
        }

        $arrConfig['eval']['xlabel'] = $this->getXLabel($arrConfig);
        if (is_array($arrConfig['input_field_callback']))
        {
            $this->import($arrConfig['input_field_callback'][0]);
            $objWidget = $this->{$arrConfig['input_field_callback'][0]}->{$arrConfig['input_field_callback'][1]}($this, $arrConfig['eval']['xlabel']);
            return $this->arrWidgets[$strField] = isset($objWidget) ? $objWidget : '';
        }

        // ToDo: switch for BE / FE handling
        $strClass = $GLOBALS['BE_FFL'][$arrConfig['inputType']];
        if (!$this->classFileExists($strClass))
        {
            return $this->arrWidgets[$strField] = "";
        }

        // FIXME TEMPORARY WORKAROUND! To be fixed in the core: Controller::prepareForWidget(..)
        if (isset(self::$arrDates[$arrConfig['eval']['rgxp']])
                && !$arrConfig['eval']['mandatory']
                && is_numeric($varValue) && $varValue == 0)
        {
            $varValue = '';
        }

        // OH: why not $required = $mandatory always? source: DataContainer 226
        $arrConfig['eval']['required'] = $varValue == '' && $arrConfig['eval']['mandatory'] ? true : false;
        // OH: the whole prepareForWidget(..) thing is an only mess
        // widgets should parse the configuration by themselfs, depending on what they need
        $arrPrepared                   = $this->prepareForWidget($arrConfig, $strInputName, $varValue, $strField, $this->strTable);

        //$arrConfig['options'] = $arrPrepared['options'];

        $objWidget = new $strClass($arrPrepared);
        // OH: what is this? source: DataContainer 232
        $objWidget->currentRecord = $this->intId;

        if ($objWidget instanceof uploadable)
        {
            $this->blnUploadable = true;
        }

        // OH: xlabel, wizard: two ways to rome? wizards are the better way I think
        $objWidget->wizard = implode('',$this->objCallbackClass->executeCallbacks($arrConfig['wizard'], $this));

        return $this->arrWidgets[$strField] = $objWidget;
    }

    /**
     * Parse|Check|Validate each field and save it.
     * 
     * @param string $strField Name of current field
     * @return void 
     */
    public function processInput($strField)
    {
        // Check if we have allready processed this field
        if (isset($this->arrProcessed[$strField]) && $this->arrProcessed[$strField] === true)
        {
            return null;
        }
        else if (isset($this->arrProcessed[$strField]) && $this->arrProcessed[$strField] !== true)
        {
            return $this->arrProcessed[$strField];
        }

        $this->arrProcessed[$strField] = true;
        $strInputName = $strField . '_' . $this->mixWidgetID;

        // Return if no submit, field is not editable or not in input
        if ($this->blnSubmitted == false || !isset($this->arrInputs[$strInputName]) || $this->isEditableField($strField) == false)
        {
            return null;
        }

        // Build widget
        $objWidget = $this->getWidget($strField);
        if (!($objWidget instanceof Widget))
        {
            return null;
        }

        // Validate
        $objWidget->validate();

        // Check 
        if ($objWidget->hasErrors())
        {
            $this->blnNoReload = true;
            return null;
        }

        if (!$objWidget->submitInput())
        {
            return $this->arrProcessed[$strField] = $this->objCurrentModel->getProperty($strField);
        }

        // Get value and config
        $varNew    = $objWidget->value;
        $arrConfig = $this->getFieldDefinition($strField);

        // If array sort
        if (is_array($varNew))
        {
            ksort($varNew);
        }
        // if field has regex from type date, formate the value to date
        else if ($varNew != '' && isset(self::$arrDates[$arrConfig['eval']['rgxp']]))
        { // OH: this should be a widget feature
            $objDate = new Date($varNew, $GLOBALS['TL_CONFIG'][$arrConfig['eval']['rgxp'] . 'Format']);
            $varNew  = $objDate->tstamp;
        }

        //Handle multi-select fields in "override all" mode
        // OH: this should be a widget feature
        if (($arrConfig['inputType'] == 'checkbox' || $arrConfig['inputType'] == 'checkboxWizard') && $arrConfig['eval']['multiple'] && $this->Input->get('act') == 'overrideAll')
        {
            if ($arrNew == null || !is_array($arrNew))
            {
                $arrNew = array();
            }

            switch ($this->Input->post($objWidget->name . '_update'))
            {
                case 'add':
                    $varNew = array_values(array_unique(array_merge(deserialize($this->objActiveRecord->$strField, true), $arrNew)));
                    break;

                case 'remove':
                    $varNew = array_values(array_diff(deserialize($this->objActiveRecord->$strField, true), $arrNew));
                    break;

                case 'replace':
                    $varNew = $arrNew;
                    break;
            }

            if (!$varNew)
            {
                $varNew = '';
            }
        }

        // Call the save callbacks
        try
        {
            $varNew = $this->objCallbackClass->saveCallback($arrConfig, $varNew);
        }
        catch (Exception $e)
        {
            $this->noReload = true;
            $objWidget->addError($e->getMessage());
            return null;
        }

        // Check on value empty
        if ($varNew == '' && $arrConfig['eval']['doNotSaveEmpty'])
        {
            $this->noReload = true;
            $objWidget->addError($GLOBALS['TL_LANG']['ERR']['mdtryNoLabel']);
            return null;
        }

        if ($varNew != '')
        {
            if ($arrConfig['eval']['encrypt'])
            {
                $varNew = $this->Encryption->encrypt(is_array($varNew) ? serialize($varNew) : $varNew);
            }
            else if ($arrConfig['eval']['unique'] && !$this->objDataProvider->isUniqueValue($strField, $varNew))
            {
                $this->noReload = true;
                $objWidget->addError(sprintf($GLOBALS['TL_LANG']['ERR']['unique'], $objWidget->label));
                return null;
            }
            else if ($arrConfig['eval']['fallback'])
            {
                $this->objDataProvider->resetFallback($strField);
            }
        }

        $this->arrProcessed[$strField] = $varNew;

        return $varNew;
    }

    /**
     * Generate the help msg for each field.
     * 
     * @return String 
     */
    public function generateHelpText($strField)
    {
        $return = $GLOBALS['TL_DCA'][$this->strTable]['fields'][$strField]['label'][1];

        if (!$GLOBALS['TL_CONFIG']['showHelp'] || $GLOBALS['TL_DCA'][$this->strTable]['fields'][$strField]['inputType'] == 'password' || !strlen($return))
        {
            return '';
        }

        return '<p class="tl_help' . (!$GLOBALS['TL_CONFIG']['oldBeTheme'] ? ' tl_tip' : '') . '">' . $return . '</p>';
    }

    // Helper ------------------------------------------------------------------

    /**
     * Return the formatted group header as string
     * 
     * @param string
     * @param mixed
     * @param integer
     * @return string
     */
    public function formatCurrentValue($field, $value, $mode)
    {
        if ($this->arrDCA['fields'][$field]['inputType'] == 'checkbox' && !$this->arrDCA['fields'][$field]['eval']['multiple'])
        {
            $remoteNew = ($value != '') ? ucfirst($GLOBALS['TL_LANG']['MSC']['yes']) : ucfirst($GLOBALS['TL_LANG']['MSC']['no']);
        }
        elseif (isset($this->arrDCA['fields'][$field]['foreignKey']))
        {
            // TODO: case handling
            /*
              if($objParentModel->hasProperties())
              {
              $remoteNew = $objParentModel->getProperty('value');
              }
             */
        }
        elseif (in_array($mode, array(1, 2)))
        {
            $remoteNew = ($value != '') ? ucfirst(utf8_substr($value, 0, 1)) : '-';
        }
        elseif (in_array($mode, array(3, 4)))
        {
            if (!isset($this->arrDCA['fields'][$field]['length']))
            {
                $this->arrDCA['fields'][$field]['length'] = 2;
            }

            $remoteNew = ($value != '') ? ucfirst(utf8_substr($value, 0, $this->arrDCA['fields'][$field]['length'])) : '-';
        }
        elseif (in_array($mode, array(5, 6)))
        {
            $remoteNew = ($value != '') ? $this->parseDate($GLOBALS['TL_CONFIG']['dateFormat'], $value) : '-';
        }
        elseif (in_array($mode, array(7, 8)))
        {
            $remoteNew = ($value != '') ? date('Y-m', $value) : '-';
            $intMonth  = ($value != '') ? (date('m', $value) - 1) : '-';

            if (isset($GLOBALS['TL_LANG']['MONTHS'][$intMonth]))
            {
                $remoteNew = ($value != '') ? $GLOBALS['TL_LANG']['MONTHS'][$intMonth] . ' ' . date('Y', $value) : '-';
            }
        }
        elseif (in_array($mode, array(9, 10)))
        {
            $remoteNew = ($value != '') ? date('Y', $value) : '-';
        }
        else
        {
            if ($this->arrDCA['fields'][$field]['inputType'] == 'checkbox' && !$this->arrDCA['fields'][$field]['eval']['multiple'])
            {
                $remoteNew = ($value != '') ? $field : '';
            }
            elseif (is_array($this->arrDCA['fields'][$field]['reference']))
            {
                $remoteNew = $this->arrDCA['fields'][$field]['reference'][$value];
            }
            elseif (array_is_assoc($this->arrDCA['fields'][$field]['options']))
            {
                $remoteNew = $this->arrDCA['fields'][$field]['options'][$value];
            }
            else
            {
                $remoteNew = $value;
            }

            if (is_array($remoteNew))
            {
                $remoteNew = $remoteNew[0];
            }

            if (empty($remoteNew))
            {
                $remoteNew = '-';
            }
        }

        return $remoteNew;
    }

    /**
     * Return the formatted group header as string
     * @param string
     * @param mixed
     * @param integer
     * @param InterfaceGeneralModel
     * @return string
     */
    public function formatGroupHeader($field, $value, $mode, InterfaceGeneralModel $objModelRow)
    {
        $group  = '';
        static $lookup = array();

        if (array_is_assoc($this->arrDCA['fields'][$field]['options']))
        {
            $group = $this->arrDCA['fields'][$field]['options'][$value];
        }
        else if (is_array($this->arrDCA['fields'][$field]['options_callback']))
        {
            if (!isset($lookup[$field]))
            {
                $lookup[$field] = $this->objCallbackClass->optionsCallback();
            }

            $group = $lookup[$field][$value];
        }
        else
        {
            $group = is_array($this->arrDCA['fields'][$field]['reference'][$value]) ? $this->arrDCA['fields'][$field]['reference'][$value][0] : $this->arrDCA['fields'][$field]['reference'][$value];
        }

        if (empty($group))
        {
            $group = is_array($this->arrDCA[$value]) ? $this->arrDCA[$value][0] : $this->arrDCA[$value];
        }

        if (empty($group))
        {
            $group = $value;

            if ($this->arrDCA['fields'][$field]['eval']['isBoolean'] && $value != '-')
            {
                $group = is_array($this->arrDCA['fields'][$field]['label']) ? $this->arrDCA['fields'][$field]['label'][0] : $this->arrDCA['fields'][$field]['label'];
            }
        }
        
        $group = $this->objCallbackClass->groupCallback($group, $mode, $field, $objModelRow->getPropertiesAsArray());

        return $group;
    }

    /**
     * Get special lables
     * 
     * @param array $arrConfig
     * @return string 
     */
    public function getXLabel($arrConfig)
    {
        $strXLabel = '';

        // Toggle line wrap (textarea)
        if ($arrConfig['inputType'] == 'textarea' && !strlen($arrConfig['eval']['rte']))
        {
            $strXLabel .= ' ' . $this->generateImage(
                            'wrap.gif', $GLOBALS['TL_LANG']['MSC']['wordWrap'], sprintf(
                                    'title="%s" class="toggleWrap" onclick="Backend.toggleWrap(\'ctrl_%s\');"', specialchars($GLOBALS['TL_LANG']['MSC']['wordWrap']), $this->strInputName
                            )
            );
        }

        // Add the help wizard
        if ($arrConfig['eval']['helpwizard'])
        {
            $strXLabel .= sprintf(
                    ' <a href="contao/help.php?table=%s&amp;field=%s" title="%s" onclick="Backend.openWindow(this, 600, 500); return false;">%s</a>', $this->strTable, $this->strField, specialchars($GLOBALS['TL_LANG']['MSC']['helpWizard']), $this->generateImage(
                            'about.gif', $GLOBALS['TL_LANG']['MSC']['helpWizard'], 'style="vertical-align:text-bottom;"'
                    )
            );
        }

        // Add the popup file manager
        if ($arrConfig['inputType'] == 'fileTree' && $this->strTable . '.' . $this->strField != 'tl_theme.templates')
        {
            $strXLabel .= sprintf(
                    ' <a href="contao/files.php" title="%s" onclick="Backend.getScrollOffset(); Backend.openWindow(this, 750, 500); return false;">%s</a>', specialchars($GLOBALS['TL_LANG']['MSC']['fileManager']), $this->generateImage(
                            'filemanager.gif', $GLOBALS['TL_LANG']['MSC']['fileManager'], 'style="vertical-align:text-bottom;"'
                    )
            );
        }
        // Add table import wizard
        else if ($arrConfig['inputType'] == 'tableWizard')
        {
            $strXLabel .= sprintf(
                    ' <a href="%s" title="%s" onclick="Backend.getScrollOffset();">%s</a> %s%s', ampersand($this->addToUrl('key=table')), specialchars($GLOBALS['TL_LANG'][$this->strTable]['importTable'][1]), $this->generateImage(
                            'tablewizard.gif', $GLOBALS['TL_LANG'][$this->strTable]['importTable'][0], 'style="vertical-align:text-bottom;"'
                    ), $this->generateImage(
                            'demagnify.gif', $GLOBALS['TL_LANG']['tl_content']['shrink'][0], 'title="' . specialchars($GLOBALS['TL_LANG']['tl_content']['shrink'][1]) . '" style="vertical-align:text-bottom; cursor:pointer;" onclick="Backend.tableWizardResize(0.9);"'
                    ), $this->generateImage(
                            'magnify.gif', $GLOBALS['TL_LANG']['tl_content']['expand'][0], 'title="' . specialchars($GLOBALS['TL_LANG']['tl_content']['expand'][1]) . '" style="vertical-align:text-bottom; cursor:pointer;" onclick="Backend.tableWizardResize(1.1);"'
                    )
            );
        }
        // Add list import wizard
        else if ($arrConfig['inputType'] == 'listWizard')
        {
            $strXLabel .= sprintf(
                    ' <a href="%s" title="%s" onclick="Backend.getScrollOffset();">%s</a>', ampersand($this->addToUrl('key=list')), specialchars($GLOBALS['TL_LANG'][$this->strTable]['importList'][1]), $this->generateImage(
                            'tablewizard.gif', $GLOBALS['TL_LANG'][$this->strTable]['importList'][0], 'style="vertical-align:text-bottom;"'
                    )
            );
        }

        return $strXLabel;
    }

    /**
     * Function for preloading the tiny mce
     * 
     * @return type 
     */
    public function preloadTinyMce()
    {
        if (count($this->getSubpalettesDefinition()) == 0)
        {
            return;
        }

        foreach (array_keys($this->arrFields) as $strField)
        {
            $arrConfig = $this->getFieldDefinition($strField);

            if (!isset($arrConfig['eval']['rte']))
            {
                continue;
            }

            if (strncmp($arrConfig['eval']['rte'], 'tiny', 4) !== 0)
            {
                continue;
            }

            list($strFile, $strType) = explode('|', $arrConfig['eval']['rte']);

            $strID = 'ctrl_' . $strField . '_' . $this->mixWidgetID;

            $GLOBALS['TL_RTE'][$strFile][$strID] = array(
                'id' => $strID,
                'file' => $strFile,
                'type' => $strType
            );
        }
    }

    // Interface funtions ------------------------------------------------------

    public function __call($name, $arguments)
    {
        $strReturn = call_user_func_array(array($this->objController, $name), $arguments);
        if ($strReturn != null && $strReturn != "")
        {
            return $strReturn;
        }

        return call_user_func_array(array($this->objViewHandler, $name), $arguments);
    }

    public function copy()
    {
        $strReturn = $this->objController->copy($this);
        if ($strReturn != null && $strReturn != "")
        {
            return $strReturn;
        }

        return $this->objViewHandler->copy($this);
    }

    public function create()
    {
        $strReturn = $this->objController->create($this);
        if ($strReturn != null && $strReturn != "")
        {
            return $strReturn;
        }

        return $this->objViewHandler->create($this);
    }

    public function cut()
    {
        $strReturn = $this->objController->cut($this);
        if ($strReturn != null && $strReturn != "")
        {
            return $strReturn;
        }

        return $this->objViewHandler->cut($this);
    }

    public function delete()
    {
        $strReturn = $this->objController->delete($this);
        if ($strReturn != null && $strReturn != "")
        {
            return $strReturn;
        }

        return $this->objViewHandler->delete($this);
    }

    public function edit()
    {
        $strReturn = $this->objController->edit($this);
        if ($strReturn != null && $strReturn != "")
        {
            return $strReturn;
        }

        return $this->objViewHandler->edit($this);
    }

    public function move()
    {
        $strReturn = $this->objController->move($this);
        if ($strReturn != null && $strReturn != "")
        {
            return $strReturn;
        }

        return $this->objViewHandler->move($this);
    }

    public function show()
    {
        $strReturn = $this->objController->show($this);
        if ($strReturn != null && $strReturn != "")
        {
            return $strReturn;
        }

        return $this->objViewHandler->show($this);
    }

    public function showAll()
    {
        $strReturn = $this->objController->showAll($this);
        if ($strReturn != null && $strReturn != "")
        {
            return $strReturn;
        }

        return $this->objViewHandler->showAll($this);
    }

    public function undo()
    {
        $strReturn = $this->objController->undo($this);
        if ($strReturn != null && $strReturn != "")
        {
            return $strReturn;
        }

        return $this->objViewHandler->undo($this);
    }

}
