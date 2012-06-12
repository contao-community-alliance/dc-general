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
abstract class DC_General extends DataContainer implements editable
{
    // Vars --------------------------------------------------------------------

    /**
     * The DCA of this table
     * @var array 
     */
    private $arrDCA;
    private $blnSubmitted;
    private $blnAutoSubmitted;

    /**
     * The form lease
     * @var string 
     */
    private $strLease;

    /**
     * True, if this is a new form
     * @var boolean 
     */
    private $blnIsNewLease;

    /**
     * Field set states
     * @var array 
     */
    private $arrStates;

    /**
     * Set: fields submitted
     * @var array 
     */
    private $arrInputs;
    private $blnCreateNewVersion = false;
    private $blnUploadable       = false;

    /**
     * Map: fields possible for editing -> field dca
     * @var array 
     */
    private $arrFields;

    /**
     * Map: field -> widget
     * @var array 
     */
    private $arrWidgets = array();

    /**
     * Set: fields processed
     * @var array 
     */
    private $arrProcessed = array();
    private $arrButtons = array();
    private $varWidgetID;
    private static $arrDates = array(
        'date' => true,
        'time' => true,
        'datim' => true
    );

    // Constructor -------------------------------------------------------------

    public function __construct($strTable, array $arrDCA = null, $blnOnloadCallback = true)
    {
        parent::__construct();

        // Basic vars Init
        $this->strTable = $strTable;
        $this->arrDCA = $arrDCA ? $arrDCA : $GLOBALS['TL_DCA'][$strTable];

        // Check whether the table is defined
        if (!strlen($this->strTable) || !count($this->arrDCA))
        {
            $this->log('Could not load data container configuration for "' . $strTable . '"', 'DC_Table __construct()', TL_ERROR);
            trigger_error('Could not load data container configuration', E_USER_ERROR);
        }

        // Import
        $this->import('Encryption'); // ToDo: What is this ? - Warum entfernt ?
        $this->import('BackendUser', 'User');

        // Set JS
        $GLOBALS['TL_JAVASCRIPT'][] = 'system/modules/backboneit_dctableextended/js/dctableextended' . (version_compare(VERSION, '2.10', '<') ? '-2.9' : '') . '.js';

        $this->getLease();
        $this->blnSubmitted = $_POST['FORM_SUBMIT'] == $this->strTable;
        $this->blnAutoSubmitted = $_POST['SUBMIT_TYPE'] == 'auto';
        $this->arrInputs = $_POST['FORM_INPUTS'] ? array_flip($this->Input->post('FORM_INPUTS')) : array();
        $this->arrStates = $this->Session->get('fieldset_states');
        $this->arrStates = (array) $this->arrStates[$this->strTable];
        $this->intId = $this->Input->get('id');

        if ($blnOnloadCallback)
        {
            $this->executeCallbacks($this->arrDCA['config']['onload_callback'], $this);
        }
    }

    // Magical functions -------------------------------------------------------

    public function __get($strKey)
    {
        switch ($strKey)
        {
            case 'submitted':
                return $this->blnSubmitted;
                break;

            case 'autoSubmitted':
                return $this->blnAutoSubmitted;
                break;

            case 'createNewVersion':
                return $this->blnCreateNewVersion;
                break;

            case 'dca':
                return $this->arrDCA;
                break;

            case 'wid':
                return $this->varWidgetID;
                break;

            default:
                return parent::__get($strKey);
                break;
        }
    }

    // Getter and Setter -------------------------------------------------------

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
     * Check if we have editable fields in this dca.
     * 
     * @return int count of all editable fields. 
     */
    public function hasEditableFields()
    {
        return count($this->arrFields) != 0;
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
     * Add a new Error
     * 
     * @param string $strMessage Message
     * @param string $strField field name
     */
    public function addError($strMessage, $strField = null)
    {
        $this->noReload = true;

        if (strlen($strField) != 0)
        {
            $objWidget = $this->getWidget($strField);
            $objWidget->addError($strMessage);
        }
        else
        {
            $_SESSION['TL_ERROR'][] = $strMessage;
        }
    }

    /**
     * Check if a error occurred
     * 
     * @return type 
     */
    public function hasErrors()
    {
        return $this->noReload;
    }

    /**
     * Check if a new reccore
     * 
     * @return boolean 
     */
    public function isNewLease()
    {
        return $this->blnIsNewLease;
    }

    /**
     * Get current lease or create a new one
     * 
     * @return string 
     */
    public function getLease()
    {
        if (isset($this->strLease))
        {
            return $this->strLease;
        }

        if (isset($_GET['lease']))
        {
            return $this->strLease = $this->Input->get('lease');
        }

        $this->blnIsNewLease = true;

        return $this->strLease = md5(mt_rand());
    }

    /**
     * Return the DCA config information
     * 
     * @return type 
     */
    public function &getDCAReference()
    {
        return $this->arrDCA;
    }

    /**
     *
     * 
     * @return type 
     */
    public function getSessionRecordKey()
    {
        return $this->strTable . '$' . $this->getLease();
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
     * Return the name of the current palette
	 
     * @return string
     */
    abstract public function getPalette();

    /**
     * Get buttons definition
     * 
     * @return array 
     */
    public function getButtonsDefinition()
    {
        return is_array($this->arrDCA['buttons']) ? $this->arrDCA['buttons'] : array();
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
     * Get the value from a special field
     * 
     * @param string $strField name of field
     * @return mix value  
     */
    public function getValue($strField)
    {
        $this->processInput($strField);
        return $this->objActiveRecord->$strField;
    }

    /**
     * 
     * 
     * @return \stdClass 
     */
    public function getDefaultRecord()
    {
        $objRecord = new stdClass();
        foreach ($this->arrDCA['fields'] as $strField => $arrConfig)
        {
            isset($arrConfig['default']) && $objRecord->$strField = $arrConfig['default'];
        }
        return $objRecord;
    }

    /**
     *
     * @param type $intID 
     */
    protected function setWidgetID($intID)
    {
        if (preg_match('/^[0-9]+$/', $intID))
        {
            $this->varWidgetID = intval($intID);
        }
        else
        {
            $this->varWidgetID = 'b' . str_replace('=', '_', base64_encode($intID));
        }
    }

    /**
     * Set the values of current row back to another
     * version.
     * 
     * @param type $strVersion
     * @return type 
     */
    abstract protected function setVersion($strTable, $intID, $strVersion);

    /**
     * Return a list with all versions for this row
     * 
     * @return array 
     */
    abstract protected function getVersions($strTable, $intID);


    // Save / Load / Lookup functions ------------------------------------------

    /**
     * Reset the fallback field 
     * 
     * Documentation: 
     *      Evaluation - fallback => If true the field can only be assigned once per table.
     */
    abstract protected function resetFallback($strField, $strTable);

    /**
     * Check if the value is unique in table
     * 
     * Documentation: 
     *      Evaluation - unique => If true the field value cannot be saved if it exists already.
     */
    abstract protected function isUniqueValue($strTable, $strField, $varNew);

    /**
     * Store the value of a give field in table 
     */
    abstract protected function storeValue($strField, $strTable, $intID, $varNew);

    /**
     * Load the current record.
     * 
     * @param string $strTable Tablename
     * @param string $intID current record ud
     * @param boolean $blnDontUseCache use cache or not
     * @return mixed 
     */
    abstract protected function loadActiveRecord($strTable, $intID, $blnDontUseCache = false);

    /**
     * Update the timestamp of current record
     * 
     * @param string $strTable Tablename
     * @param string $intID current record ud
     */
    abstract protected function updateTimestamp($strTable, $intID);

    // Default functions -------------------------------------------------------

    /**
     * Load / Add the default buttons 
     */
    protected function loadDefaultButtons()
    {
        $this->arrDCA['buttons']['save'] = array('MemoryExtendedButtons', 'save');
        $this->arrDCA['buttons']['saveNclose'] = array('MemoryExtendedButtons', 'saveAndClose');
    }

    /**
     * Load a list with all editable field
     * 
     * @param boolean $blnUserSelection
     * @return boolean 
     */
    protected function loadEditableFields($blnUserSelection = false)
    {
        $this->arrFields = array_flip(array_keys(array_filter($this->arrDCA['fields'], create_function('$arr', 'return !$arr[\'exclude\'];'))));

        if (!$blnUserSelection)
            return true;

        if (!$this->Input->get('fields'))
            return false;

        $arrSession = $this->Session->getData();
        if ($this->Input->post('FORM_SUBMIT') == $this->strTable . '_all')
        {
            $arrSession['CURRENT'][$this->strTable] = deserialize($this->Input->post('all_fields'));
            $this->Session->setData($arrSession);
            $this->reload(); // OH: i think its better
        }

        if (!is_array($arrSession['CURRENT'][$this->strTable]))
            return false;

        $this->arrFields = array_intersect_key($this->arrFields, array_flip($arrSession['CURRENT'][$this->strTable]));

        return true;
    }

    // Core functions ----------------------------------------------------------

    /**
     * Execute a callback
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

    /**
     * Parse|Check|Validate each field and save it.
     * 
     * @param string $strField Name of current field
     * @return void 
     */
    protected function processInput($strField)
    {
        // Check if we have allready processed this field
        if (isset($this->arrProcessed[$strField]))
        {
            return;
        }

        $this->arrProcessed[$strField] = true;
        $this->strField = $strField;
        $this->strInputName = $strField . '--' . $this->varWidgetID;

        // Return if no submit, field is not editable or not in input
        if (!$this->blnSubmitted || !isset($this->arrInputs[$this->strInputName]) || !$this->isEditableField($strField))
        {
            return;
        }

        // Build widget
        $objWidget = $this->getWidget($strField);
        if (!($objWidget instanceof Widget))
        {
            return;
        }

        // Validate
        $objWidget->validate();

        // Check 
        if ($objWidget->hasErrors())
        {
            $this->noReload = true;
            return;
        }

        if (!$objWidget->submitInput())
        {
            return;
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
            if (is_array($arrConfig['save_callback']))
            {
                foreach ($arrConfig['save_callback'] as $arrCallback)
                {
                    $this->import($arrCallback[0]);
                    $varNew = $this->$arrCallback[0]->$arrCallback[1]($varNew, $this);
                }
            }
        }
        catch (Exception $e)
        {
            $this->noReload = true;
            $objWidget->addError($e->getMessage());
            return;
        }

        // Check on value empty
        if ($varNew == '' && $arrConfig['eval']['doNotSaveEmpty'])
        {
            return;
        }

        // Check on value has not changed
        if (deserialize($this->objActiveRecord->$strField) == $varNew && !$arrConfig['eval']['alwaysSave'])
        {
            return;
        }

        if ($varNew != '')
        {
            if ($arrConfig['eval']['encrypt'])
            {
                $varNew = $this->Encryption->encrypt(is_array($varNew) ? serialize($varNew) : $varNew);
            }
            else if ($arrConfig['eval']['unique'] && !$this->isUniqueValue($varNew))
            {
                $this->noReload = true;
                $objWidget->addError(sprintf($GLOBALS['TL_LANG']['ERR']['unique'], $objWidget->label));
                return;

                // OH: completly correct would be "if" instead of "elseif",
                // but this is a very rare case, where only one value is stored in the field
                // and a new value must differ from the existing value
                // lets treat fallback and unique as exclusive
            }
            elseif ($arrConfig['eval']['fallback'])
            {
                $this->resetFallback($this->strField, $this->strTable);
            }
        }

        if (!$this->storeValue($this->strField, $this->strTable, $this->intId, $varNew))
        {
            return;
        }
        else if (!$arrConfig['eval']['submitOnChange'] && $this->objActiveRecord->$strField != $varNew)
        {
            $this->blnCreateNewVersion = true;
        }

        $this->objActiveRecord->$strField = $varNew;
    }

    /**
     * Create a widget 
     * 
     * @param string $strField
     * @return Widget
     * @throws Exception if widget class is unknown
     */
    public function getWidget($strField)
    {
        if (isset($this->arrWidgets[$strField]))
            return $this->arrWidgets[$strField];

        if (!$this->isEditableField($strField))
            return;

        $arrConfig = $this->getFieldDefinition($strField);
        if (!$arrConfig)
            return;

        $this->strField = $strField;
        $this->strInputName = $strField . '--' . $this->varWidgetID;
        $this->varValue = deserialize(/* $arrConfig['eval']['encrypt'] ? $this->Encryption->decrypt($this->objActiveRecord->$strField) : */$this->objActiveRecord->$strField);

        if (is_array($arrConfig['load_callback']))
        {
            foreach ($arrConfig['load_callback'] as $arrCallback)
            {
                if (is_array($arrCallback))
                {
                    $this->import($arrCallback[0]);
                    $this->varValue = $this->$arrCallback[0]->$arrCallback[1]($this->varValue, $this);
                }
            }
            // TODO: OH: remove this for clearance! source: DC_Table 1666
            // the value should be only "marked up" for editing in browser via load_callback
            // API should expose "true" internal (DB) value
            // $this->objActiveRecord->$strField = $this->varValue;
        }

        $arrConfig['eval']['xlabel'] = $this->getXLabel($arrConfig);
        if (is_array($arrConfig['input_field_callback']))
        {
            $this->import($arrConfig['input_field_callback'][0]);
            $objWidget = $this->{$arrConfig['input_field_callback'][0]}->{$arrConfig['input_field_callback'][1]}($this, $arrConfig['eval']['xlabel']);
            return $this->arrWidgets[$strField] = isset($objWidget) ? $objWidget : '';
        }

        $strClass = $GLOBALS['BE_FFL'][$arrConfig['inputType']];
        if (!$this->classFileExists($strClass))
        {
            throw new Exception("[DCA Config Error] No widget class found for input-type [{$arrConfig['inputType']}].");
        }

        // FIXME TEMPORARY WORKAROUND! To be fixed in the core: Controller::prepareForWidget(..)
        if (isset(self::$arrDates[$arrConfig['eval']['rgxp']])
                && !$arrConfig['eval']['mandatory']
                && is_numeric($this->varValue) && $this->varValue == 0)
            $this->varValue = '';

        // OH: why not $required = $mandatory always? source: DataContainer 226
        $arrConfig['eval']['required'] = $this->varValue == '' && $arrConfig['eval']['mandatory'] ? true : false;
        // OH: the whole prepareForWidget(..) thing is an only mess
        // widgets should parse the configuration by themselfs, depending on what they need
        $arrPrepared                   = $this->prepareForWidget($arrConfig, $this->strInputName, $this->varValue, $this->strField, $this->strTable);
        //$arrConfig['options'] = $arrPrepared['options'];

        $objWidget = new $strClass($arrPrepared);
        // OH: what is this? source: DataContainer 232
        $objWidget->currentRecord = $this->intId;

        if ($objWidget instanceof uploadable)
            $this->blnUploadable = true;

        // OH: xlabel, wizard: two ways to rome? wizards are the better way I think
        $objWidget->wizard = implode('', $this->executeCallbacks($arrConfig['wizard'], $this));

        return $this->arrWidgets[$strField] = $objWidget;
    }

    /**
     * Check if this DCA is editable. If not redirect to error page
     * and write a log entry.
     * 
     * @return void 
     */
    public function checkEditable()
    {
        if ($this->isEditable())
        {
            return;
        }

        $this->log('Table ' . $this->strTable . ' is not editable', 'DC_Table edit()', TL_ERROR);
        $this->redirect('contao/main.php?act=error');
    }

    /**
     * Check if versioning in enabled and a submit was send.
     * 
     * @return void 
     */
    protected function checkVersion()
    {
        // Check if versioning is enabled
        if (!$this->arrDCA['config']['enableVersioning'])
        {
            return;
        }

        // Check if the submit come from version
        if (!$this->Input->post('FORM_SUBMIT') == 'tl_version')
        {
            return;
        }

        // Check input vars
        $strVersion = $this->Input->post('version');
        if (!strlen($strVersion))
        {
            return;
        }

        // Load/Save values
        $this->setVersion($this->strTable, $this->intId, $strVersion);

        // Reload
        $this->reload();
    }

    /**
     * Run through each button callback 
     */
    protected function checkButtonSubmit()
    {
        if (!$this->blnAutoSubmitted)
        {
            foreach ($this->getButtonsDefinition() as $strButtonKey => $arrCallback)
            {
                if (isset($_POST[$strButtonKey]))
                {
                    $this->import($arrCallback[0]);
                    $this->{$arrCallback[0]}->{$arrCallback[1]}($this);
                }
            }
        }
    }

    // Helper functions --------------------------------------------------------

    /**
     * Get special lables
     * 
     * @param array $arrConfig
     * @return string 
     */
    protected function getXLabel($arrConfig)
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
     * Load for each button the language tag
     * 
     * @return array 
     */
    protected function getButtonLabels()
    {
        $arrButtons = array();

        foreach (array_keys($this->getButtonsDefinition()) as $strButton)
        {
            if (isset($GLOBALS['TL_LANG'][$this->strTable][$strButton]))
            {
                $strLabel = $GLOBALS['TL_LANG'][$this->strTable][$strButton];
            }
            else if (isset($GLOBALS['TL_LANG']['MSC'][$strButton]))
            {
                $strLabel = $GLOBALS['TL_LANG']['MSC'][$strButton];
            }
            else
            {
                $strLabel = $strButton;
            }

            $arrButtons[$strButton] = $strLabel;
        }

        return $arrButtons;
    }

    /**
     * Function for preloading the tiny mce
     * 
     * @return type 
     */
    protected function preloadTinyMce()
    {
        if (!$this->getSubpalettesDefinition())
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
            $strID = 'ctrl_' . $strField . '--' . $this->varWidgetID;

            $GLOBALS['TL_RTE'][$strFile][$strID] = array(
                'id' => $strID,
                'file' => $strFile,
                'type' => $strType
            );
        }
    }

    // Show functions ----------------------------------------------------------

    /**
     * Generate the view for edit
     * 
     * @param int $intID record id
     * @param string $strSelector selectore for subpalltes
     * @return string 
     */
    public function generateEdit($intID = null, $strSelector = null)
    {
        $this->checkEditable();

        $intID && $this->intId = $intID;

        $this->setWidgetID($this->intId);

        $this->checkVersion(); //version switched?

        $this->loadEditableFields();

        if (!$this->hasEditableFields())
        {
            return $this->redirect($this->getReferer());
        }

        $this->loadActiveRecord($this->strTable, $this->intId);
        $this->createInitialVersion($this->strTable, $this->intId);
        $this->blnCreateNewVersion = false; // just in case...

        $objPaletteBuilder = new PaletteBuilder($this);

        if ($intID && $strSelector)
        {
            return $objPaletteBuilder->generateAjaxPalette(
                            $strSelector, $strSelector . '--' . $this->varWidgetID, $this->getTemplate('be_tableextended_field')
            );
        }

        $this->loadDefaultButtons();
        $this->blnSubmitted && !$this->noReload && $this->executeCallbacks($this->arrDCA['config']['onsubmit_callback'], $this);

        if ($this->blnSubmitted && !$this->noReload)
        {
            // Save the current version
            if ($this->blnCreateNewVersion && !$this->blnAutoSubmitted)
            {
                $this->createNewVersion($this->strTable, $this->intId);
                $this->executeCallbacks($this->arrDCA['config']['onversion_callback'], $this->strTable, $this->intId, $this);
                $this->log(sprintf('A new version of %s ID %s has been created', $this->strTable, $this->intId), 'DC_Table edit()', TL_GENERAL);
            }

            // Set the current timestamp (-> DO NOT CHANGE THE ORDER version - timestamp)
            $this->updateTimestamp($this->strTable, $this->intId);

            if (!$this->blnAutoSubmitted)
            {
                foreach ($this->getButtonsDefinition() as $strButtonKey => $arrCallback)
                {
                    if (isset($_POST[$strButtonKey]))
                    {
                        $this->import($arrCallback[0]);
                        $this->{$arrCallback[0]}->{$arrCallback[1]}($this);
                    }
                }
            }

            $this->reload();
        }

        version_compare(VERSION, '2.10', '<') || $this->preloadTinyMce();

        $objTemplate = new BackendTemplate('be_tableextended_edit');

        $objTemplate->setData(array(
            'fieldsets' => $objPaletteBuilder->generateFieldsets($this->getTemplate('be_tableextended_field'), $this->arrStates),
            'oldBE' => $GLOBALS['TL_CONFIG']['oldBeTheme'],
            'versions' => $this->getVersions($this->strTable, $this->intId),
            'subHeadline' => sprintf($GLOBALS['TL_LANG']['MSC']['editRecord'], $this->intId ? 'ID ' . $this->intId : ''),
            'table' => $this->strTable,
            'lease' => $this->strLease,
            'enctype' => $this->blnUploadable ? 'multipart/form-data' : 'application/x-www-form-urlencoded',
            'onsubmit' => implode(' ', $this->onsubmit),
            'error' => $this->noReload,
            'buttons' => $this->getButtonLabels()
        ));

        version_compare(VERSION, '2.10', '<') && $GLOBALS['TL_JAVASCRIPT'] = array_unique($GLOBALS['TL_JAVASCRIPT']);

        return $objTemplate->parse();
    }

}
