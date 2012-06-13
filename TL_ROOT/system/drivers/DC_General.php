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
     * Id of the item currently in edit view     * 
     * @var int 
     */
    protected $intId = null;

    /**
     * Name of current table
     * @var String 
     */
    protected $strTable = null;

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
     * The provider that shall be used for data retrival.
     * @var Object 
     */
    protected $objDataProvider = null;

    /**
     * The provider that shall be used for view retrival.
     * @var Interface_GeneralView 
     */
    protected $objViewHandler = null;

    // Constructor and co. -----------------------------------------------------

    public function __construct($strTable, array $arrDCA = null, $blnOnloadCallback = true)
    {
        parent::__construct();

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

        // Set JS
        // ToDo: SH: JS change, maybe callback or config ?
        $GLOBALS['TL_JAVASCRIPT'][] = 'system/modules/backboneit_dctableextended/js/dctableextended' . (version_compare(VERSION, '2.10', '<') ? '-2.9' : '') . '.js';

        $this->intId = $this->Input->get('id');
        $this->blnSubmitted = $_POST['FORM_SUBMIT'] == $this->strTable;
        $this->blnAutoSubmitted = $_POST['SUBMIT_TYPE'] == 'auto';
        $this->arrInputs = $_POST['FORM_INPUTS'] ? array_flip($this->Input->post('FORM_INPUTS')) : array();
        $this->arrStates = $this->Session->get('fieldset_states');
        $this->arrStates = (array) $this->arrStates[$this->strTable];



        // Callback
        if ($blnOnloadCallback == true)
        {
            $this->executeCallbacks($this->arrDCA['config']['onload_callback'], $this);
        }
    }

    /**
     * Load the dataprovider and view handler, 
     * if not set try to load the default one.
     */
    protected function loadProviderAndHandler()
    {
        // Load file handler
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
            $this->objViewHandler = new GeneralView_Default();
        }

        // Load data provider
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
                "table" => $this->strTable,
            );
            $this->objDataProvider = new GeneralData_Default($arrConfig);
        }
    }

    // Getter and Setter -------------------------------------------------------

    public function getId()
    {
        return $this->intId;
    }

    public function getTable()
    {
        return $this->strTable;
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

    public function getViewHandler()
    {
        return $this->objViewHandler;
    }

    // Functions ---------------------------------------------------------------
    // Helper ------------------------------------------------------------------

    /**
     * Exectue a callback
     * 
     * @param array $varCallbacks
     * @return array 
     */
    protected function executeCallbacks($varCallbacks)
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

    // Interface funtions ------------------------------------------------------

    public function copy()
    {
        return $this->objViewHandler->copy($this);
    }

    public function copyAll()
    {
        return $this->objViewHandler->copyAll($this);
    }

    public function create()
    {
        return $this->objViewHandler->create($this);
    }

    public function cut()
    {
        return $this->objViewHandler->cut($this);
    }

    public function cutAll()
    {
        return $this->objViewHandler->cutAll($this);
    }

    public function delete()
    {
        return $this->objViewHandler->delete($this);
    }

    public function edit()
    {
        return $this->objViewHandler->edit($this);
    }

    public function move()
    {
        return $this->objViewHandler->move($this);
    }

    public function show()
    {
        return $this->objViewHandler->show($this);
    }

    public function showAll()
    {
        return $this->objViewHandler->showAll($this);
    }

    public function undo()
    {
        return $this->objViewHandler->undo($this);
    }

}
