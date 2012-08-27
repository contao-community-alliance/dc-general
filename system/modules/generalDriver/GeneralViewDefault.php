<?php

if (!defined('TL_ROOT'))
    die('You can not access this file directly!');

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
 * @see InterfaceGeneralView
 * @copyright  MEN AT WORK 2012
 * @package    generalDriver
 * @license    GNU/LGPL
 * @filesource
 */
class GeneralViewDefault extends Controller implements InterfaceGeneralView
{
    /* /////////////////////////////////////////////////////////////////////////
     * -------------------------------------------------------------------------
     * Vars
     * -------------------------------------------------------------------------
     * ////////////////////////////////////////////////////////////////////// */

    // Palettes/View vars ---------------------------

    protected $arrSelectors = array();
    protected $arrAjaxPalettes = array();
    protected $arrRootPalette = array();
    protected $arrDCA = array();
    // Multilanguage vars ---------------------------
    protected $strCurrentLanguage;
    protected $blnMLSupport = false;
    // Overall Vars ---------------------------------
    protected $notImplMsg   = "<div style='text-align:center; font-weight:bold; padding:40px;'>This function/view is not implemented.</div>";
    // Objects --------------------------------------

    /**
     * Driver class
     * @var DC_General 
     */
    protected $objDC = null;

    /**
     * The current working model
     * @var InterfaceGeneralModel
     */
    protected $objCurrentModel = null;

    /**
     * A list with all supported languages
     * @var InterfaceGeneralCollection
     */
    protected $objLanguagesSupported = null;

    /* /////////////////////////////////////////////////////////////////////////
     * -------------------------------------------------------------------------
     * Magic function
     * -------------------------------------------------------------------------
     * ////////////////////////////////////////////////////////////////////// */

    /**
     * Initialize the object
     */
    public function __construct()
    {
        parent::__construct();
    }

    /* /////////////////////////////////////////////////////////////////////////
     * -------------------------------------------------------------------------
     *  Getter & Setter
     * -------------------------------------------------------------------------
     * ////////////////////////////////////////////////////////////////////// */

    public function getDC()
    {
        return $this->objDC;
    }

    public function setDC($objDC)
    {
        $this->objDC  = $objDC;
        $this->arrDCA = $objDC->getDCA();
    }

    public function isSelector($strSelector)
    {
        return isset($this->arrSelectors[$strSelector]);
    }

    public function getSelectors()
    {
        return $this->arrSelectors;
    }

    public function isEmptyPalette()
    {
        return !count($this->arrRootPalette);
    }

    /* /////////////////////////////////////////////////////////////////////////
     * -------------------------------------------------------------------------
     *  Core Support functions
     * -------------------------------------------------------------------------
     * ////////////////////////////////////////////////////////////////////// */

    /**
     * Check if the dataprovider is multilanguage.
     * Save the current language and language array.
     * 
     * @return void
     */
    protected function checkLanguage()
    {
        $objDataProvider = $this->objDC->getDataProvider();

        // Check if DP is multilanguage
        if (is_a($this->objDC->getDataProvider(), "InterfaceGeneralDataML"))
        {
            $this->blnMLSupport          = true;
            $this->objLanguagesSupported = $objDataProvider->getLanguages($this->objDC->getId());
            $this->strCurrentLanguage    = $objDataProvider->getCurrentLanguage();
        }
        else
        {
            $this->blnMLSupport          = false;
            $this->objLanguagesSupported = null;
            $this->strCurrentLanguage    = null;
        }
    }

    /**
     * Load the current model from driver
     */
    protected function loadCurrentModel()
    {
        $this->objCurrentModel = $this->objDC->getCurrentModel();
    }

    /**
     * Load the dca from driver
     */
    protected function loadCurrentDCA()
    {
        $this->arrDCA = $this->objDC->getDCA();
    }

    /* /////////////////////////////////////////////////////////////////////////
     * -------------------------------------------------------------------------
     *  Core function
     * -------------------------------------------------------------------------
     * ////////////////////////////////////////////////////////////////////// */

    /**
     * @todo All
     * @return Stirng
     */
    public function copy()
    {
        return $this->notImplMsg;
    }

    /**
     * @todo All
     * @return type
     */
    public function copyAll()
    {
        return $this->notImplMsg;
    }

    /**
     * @see edit()
     * @return stirng
     */
    public function create()
    {
        return $this->edit();
    }

    /**
     * @todo All
     * @return type
     */
    public function cut()
    {
        return $this->notImplMsg;
    }

    /**
     * @todo All
     * @return type
     */
    public function cutAll()
    {
        return $this->notImplMsg;
    }

    /**
     * @todo All
     * @return type
     */
    public function delete()
    {
        return $this->notImplMsg;
    }

    /**
     * @todo All
     * @return type
     */
    public function move()
    {
        return $this->notImplMsg;
    }

    /**
     * @todo All
     * @return type
     */
    public function undo()
    {
        return $this->notImplMsg;
    }

    /**
     * Generate the view for edit
     * 
     * @return string
     */
    public function edit()
    {
        // Load basic informations
        $this->checkLanguage();
        $this->loadCurrentModel();

        // Get all selectors
        $this->arrStack[] = $this->objDC->getSubpalettesDefinition();
        $this->calculateSelectors($this->arrStack[0]);
        $this->parseRootPalette();

        $objTemplate = new BackendTemplate('dcbe_general_edit');
        $objTemplate->setData(array(
            'fieldsets' => $this->generateFieldsets('dcbe_general_field', array()),
            'oldBE'            => $GLOBALS['TL_CONFIG']['oldBeTheme'],
            'versions'         => $this->objDC->getDataProvider()->getVersions($this->objDC->getId()),
            'language'         => $this->objLanguagesSupported,
            'subHeadline'      => sprintf($GLOBALS['TL_LANG']['MSC']['editRecord'], $this->objDC->getId() ? 'ID ' . $this->objDC->getId() : ''),
            'languageHeadline' => strlen($this->strCurrentLanguage) != 0 ? $GLOBALS['TL_LANG']['MSC']['language'] . " " . $languages[$this->strCurrentLanguage] : '',
            'table'            => $this->objDC->getTable(),
            'enctype'          => $this->objDC->isUploadable() ? 'multipart/form-data' : 'application/x-www-form-urlencoded',
            //'onsubmit' => implode(' ', $this->onsubmit),
            'error'            => $this->noReload,
            'buttons'          => $this->objDC->getButtonLabels(),
            'noReload'         => $this->objDC->isNoReload()
        ));

        // Set JS
        $GLOBALS['TL_JAVASCRIPT'][] = 'system/modules/generalDriver/html/js/generalDriver.js';

        return $objTemplate->parse();

        // Old stuff and so -----------
//        if ($intID && $strSelector)
//        {
//            return $objPaletteBuilder->generateAjaxPalette(
//                            $strSelector, $strSelector . '--' . $this->varWidgetID, $this->getTemplate('be_tableextended_field')
//            );
//        }
    }

    /**
     * Show Informations about a data set
     *
     * @return String 
     */
    public function show()
    {
        // Load basic informations
        $this->checkLanguage();
        $this->loadCurrentModel();
        $this->loadCurrentDCA();

        // Init
        $fields = array();
        $arrFieldValues = array();
        $arrFieldLabels = array();
        $allowedFields = array('pid', 'sorting', 'tstamp');

        foreach ($this->objCurrentModel as $key => $value)
        {
            $fields[] = $key;
        }

        // Get allowed fieds from dca
        if (is_array($this->arrDCA['fields']))
        {
            $allowedFields = array_unique(array_merge($allowedFields, array_keys($this->arrDCA['fields'])));
        }

        $fields = array_intersect($allowedFields, $fields);

        // Show all allowed fields
        foreach ($fields as $strFieldName)
        {
            $arrFieldConfig = $this->arrDCA['fields'][$strFieldName];

            if (!in_array($strFieldName, $allowedFields)
                    || $arrFieldConfig['inputType'] == 'password'
                    || $arrFieldConfig['eval']['doNotShow']
                    || $arrFieldConfig['eval']['hideInput'])
            {
                continue;
            }

            // Special treatment for table tl_undo
            if ($this->objDC->getTable() == 'tl_undo' && $strFieldName == 'data')
            {
                continue;
            }

            // Make it human readable
            $arrFieldValues[$strFieldName] = $this->objDC->getReadableFieldValue($strFieldName, deserialize($this->objCurrentModel->getProperty($strFieldName)));

            // Label
            if (count($arrFieldConfig['label']))
            {
                $arrFieldLabels[$strFieldName] = is_array($arrFieldConfig['label']) ? $arrFieldConfig['label'][0] : $arrFieldConfig['label'];
            }
            else
            {
                $arrFieldLabels[$strFieldName] = is_array($GLOBALS['TL_LANG']['MSC'][$strFieldName]) ? $GLOBALS['TL_LANG']['MSC'][$strFieldName][0] : $GLOBALS['TL_LANG']['MSC'][$strFieldName];
            }

            if (!strlen($arrFieldLabels[$strFieldName]))
            {
                $arrFieldLabels[$strFieldName] = $strFieldName;
            }
        }

        // Create new template
        $objTemplate            = new BackendTemplate("dcbe_general_show");
        $objTemplate->headline  = sprintf($GLOBALS['TL_LANG']['MSC']['showRecord'], ($this->objDC->getId() ? 'ID ' . $this->objDC->getId() : ''));
        $objTemplate->arrFields = $arrFieldValues;
        $objTemplate->arrLabels = $arrFieldLabels;
        $objTemplate->language  = $this->objLanguagesSupported;

        return $objTemplate->parse();
    }

    public function showAll()
    {
        // Load basic information
        $this->loadCurrentModel();
        $this->loadCurrentDCA();

        $strReturn = '';

        // Switch mode
        switch ($this->arrDCA['list']['sorting']['mode'])
        {
            case 1:
            case 2:
            case 3:
                $strReturn = $this->listView();
                break;

            case 4:
                $strReturn = $this->parentView();
                break;

            case 5:
            case 6:
                $strReturn = $this->treeView($this->arrDCA['list']['sorting']['mode']);
                break;

            default:
                return $this->notImplMsg;
                break;
        }

        // Add panels
        switch ($this->arrDCA['list']['sorting']['mode'])
        {
            case 1:
            case 2:
            case 3:
            case 4:
                $strReturn = $this->panel() . $strReturn;
        }

        // Return all
        return $strReturn;
    }

    /* /////////////////////////////////////////////////////////////////////////
     * -------------------------------------------------------------------------
     * AJAX Calls
     * -------------------------------------------------------------------------
     * ////////////////////////////////////////////////////////////////////// */

    public function ajaxTreeView($intID, $intLevel)
    {
        // Init some Vars
        switch ($this->arrDCA['list']['sorting']['mode'])
        {
            case 5:
                $treeClass = 'tree';
                break;

            case 6:
                $treeClass = 'tree_xtnd';
                break;
        }

        $strHTML = $this->generateTreeView($this->objDC->getCurrentCollecion(), $this->arrDCA['list']['sorting']['mode'], $treeClass);

        // Return :P
        return $strHTML;
    }

    public function generateAjaxPalette($strMethod, $strSelector)
    {
        $objPaletteBuilder = new PaletteBuilder($this->objDC);

        return $objPaletteBuilder->generateAjaxPalette(
                        $strSelector, $strSelector . '_' . $objDcGeneral->getWidgetID(), 'dcbe_general_field'
        );
    }

    /* /////////////////////////////////////////////////////////////////////////
     * -------------------------------------------------------------------------
     * Sub Views
     * Helper functions for the main views
     * -------------------------------------------------------------------------
     * ////////////////////////////////////////////////////////////////////// */

    /**
     * Generate list view from current collection
     * 
     * @return string 
     */
    protected function listView()
    {
        $arrReturn = array();

        // Add display buttons
        if (!$this->arrDCA['config']['closed'] || !empty($this->arrDCA['list']['global_operations']))
        {
            $arrReturn[] = $this->displayButtons($this->objDC->getButtonId());
        }

        // Set label
        $this->setListViewLabel();

        // Generate buttons
        foreach ($this->objDC->getCurrentCollecion() as $objModelRow)
        {
            $objModelRow->setProperty('%buttons%', $this->generateButtons($objModelRow, $this->objDC->getTable(), $this->objDC->getRootIds()));
        }

        // Add template
        $objTemplate               = new BackendTemplate('dcbe_general_listView');
        $objTemplate->collection   = $this->objDC->getCurrentCollecion();
        $objTemplate->select       = $this->objDC->isSelectSubmit();
        $objTemplate->action       = ampersand($this->Environment->request, true);
        $objTemplate->mode         = $this->arrDCA['list']['sorting']['mode'];
        $objTemplate->tableHead    = $this->getTableHead();
        $objTemplate->notDeletable = $this->arrDCA['config']['notDeletable'];
        $objTemplate->notEditable  = $this->arrDCA['config']['notEditable'];
        $arrReturn[]               = $objTemplate->parse();

        return implode('', $arrReturn);
    }

    protected function parentView()
    {
        $arrReturn = array();

        $this->parentView = array(
            'sorting' => $this->arrDCA['list']['sorting']['fields'][0] == 'sorting'
        );

        $arrReturn[] = $this->displayButtons('tl_buttons');

        if (is_null($this->objDC->getParentTable()) || $this->objDC->getCurrentParentCollection()->length() == 0)
        {
            return implode('', $arrReturn);
        }

        // Load language file and data container array of the parent table
        $this->loadLanguageFile($this->objDC->getParentTable());
        $this->loadDataContainer($this->objDC->getParentTable());

        $objParentDC     = new DC_General($this->objDC->getParentTable());
        $this->parentDca = $objParentDC->getDCA();

        // Add template
        $objTemplate             = new BackendTemplate('dcbe_general_parentView');
        $objTemplate->collection = $this->objDC->getCurrentCollecion();
        $objTemplate->select     = $this->objDC->isSelectSubmit();
        $objTemplate->action     = ampersand($this->Environment->request, true);
        $objTemplate->mode       = $this->arrDCA['list']['sorting']['mode'];
        $objTemplate->table      = $this->objDC->getTable();
        $objTemplate->tableHead  = $this->parentView['headerGroup'];
        $objTemplate->header     = $this->getParentViewFormattedHeaderFields();

        $this->setRecords();

        $objTemplate->editHeader = array(
            'content' => $this->generateImage('edit.gif', $GLOBALS['TL_LANG'][$this->objDC->getTable()]['editheader'][0]),
            'href'    => preg_replace('/&(amp;)?table=[^& ]*/i', (strlen($this->objDC->getParentTable()) ? '&amp;table=' . $this->objDC->getParentTable() : ''), $this->addToUrl('act=edit')),
            'title'   => specialchars($GLOBALS['TL_LANG'][$this->objDC->getTable()]['editheader'][1])
        );

        $objTemplate->pasteNew = array(
            'content' => $this->generateImage('new.gif', $GLOBALS['TL_LANG'][$this->objDC->getTable()]['pasteafter'][0]),
            'href'    => $this->addToUrl('act=create&amp;mode=2&amp;pid=' . $this->objDC->getCurrentParentCollection()->get(0)->getID() . '&amp;id=' . $this->intId),
            'title'   => specialchars($GLOBALS['TL_LANG'][$this->objDC->getTable()]['pastenew'][0])
        );

        $objTemplate->pasteAfter = array(
            'content' => $this->generateImage('pasteafter.gif', $GLOBALS['TL_LANG'][$this->objDC->getTable()]['pasteafter'][0], 'class="blink"'),
            'href'    => $this->addToUrl('act=' . $arrClipboard['mode'] . '&amp;mode=2&amp;pid=' . $this->objDC->getCurrentParentCollection()->get(0)->getID() . (!$blnMultiboard ? '&amp;id=' . $arrClipboard['id'] : '')),
            'title'   => specialchars($GLOBALS['TL_LANG'][$this->objDC->getTable()]['pasteafter'][0])
        );

        $objTemplate->notDeletable      = $this->arrDCA['config']['notDeletable'];
        $objTemplate->notEditable       = $this->arrDCA['config']['notEditable'];
        $objTemplate->notEditableParent = $this->parentDca['config']['notEditable'];
        $arrReturn[]                    = $objTemplate->parse();

        return implode('', $arrReturn);
    }

    protected function treeView($intMode = 5)
    {
        // Init some Vars
        switch ($intMode)
        {
            case 5:
                $treeClass = 'tree';
                break;

            case 6:
                $treeClass = 'tree_xtnd';
                break;
        }

        // Label + Icon
        $strLabelText = (strlen($this->arrDCA['config']['label']) == 0 ) ? 'DC General Tree View Ultimate' : $this->arrDCA['config']['label'];
        $strLabelIcon = strlen($this->arrDCA['list']['sorting']['icon']) ? $this->arrDCA['list']['sorting']['icon'] : 'pagemounts.gif';

        // Create treeview
        $strHTML = $this->generateTreeView($this->objDC->getCurrentCollecion(), $intMode, $treeClass);

        // Build template
        $objTemplate                   = new BackendTemplate('dcbe_general_treeview');
        $objTemplate->treeClass        = 'tl_' . $treeClass;
        $objTemplate->strLabelIcon     = $this->generateImage($strLabelIcon);
        $objTemplate->strLabelText     = $strLabelText;
        $objTemplate->strHTML          = $strHTML;
        $objTemplate->intMode          = $intMode;
        $objTemplate->strGlobalsButton = $this->displayButtons($this->objDC->getButtonId());

        // Return :P
        return $objTemplate->parse();
    }

    protected function generateTreeView($objCollection, $intMode, $treeClass)
    {
        $strHTML = '';

        foreach ($objCollection as $objModel)
        {
            $objModel->setProperty('dc_gen_buttons', $this->generateButtons($objModel, $this->objDC->getTable()));

            $strToggleID = $this->objDC->getTable() . '_' . $treeClass . '_' . $objModel->getID();

            $objEntryTemplate              = new BackendTemplate('dcbe_general_treeview_entry');
            $objEntryTemplate->objModel    = $objModel;
            $objEntryTemplate->intMode     = $intMode;
            $objEntryTemplate->strToggleID = $strToggleID;

            $strHTML .= $objEntryTemplate->parse();
            $strHTML .= "\n";

            if ($objModel->getProperty('dc_gen_tv_children') == true && $objModel->getProperty('dc_gen_tv_open') == true)
            {
                $objChildTemplate                 = new BackendTemplate('dcbe_general_treeview_child');
                $objChildTemplate->objParentModel = $objModel;
                $objChildTemplate->strToggleID    = $strToggleID;
                $objChildTemplate->strHTML        = $this->generateTreeView($objModel->getProperty('dc_gen_children_collection'), $intMode, $treeClass);
                $objChildTemplate->strTable       = $this->objDC->getTable();

                $strHTML .= $objChildTemplate->parse();
                $strHTML .= "\n";
            }
        }

        return $strHTML;
    }

    /* /////////////////////////////////////////////////////////////////////////
     * -------------------------------------------------------------------------
     * Palette Helper Functions
     * -------------------------------------------------------------------------
     * ////////////////////////////////////////////////////////////////////// */

    /**
     * Get all selectors from dca
     * 
     * @param array $arrSubpalettes
     * @return void
     */
    protected function calculateSelectors(array $arrSubpalettes = null)
    {
        // Check if we have a array
        if (!is_array($arrSubpalettes))
        {
            return;
        }

        foreach ($arrSubpalettes as $strField => $varSubpalette)
        {
            $this->arrSelectors[$strField] = $this->objDC->isEditableField($strField);

            if (!is_array($varSubpalette))
            {
                continue;
            }

            foreach ($varSubpalette as $arrNested)
            {
                if (is_array($arrNested))
                {
                    $this->calculateSelectors($arrNested['subpalettes']);
                }
            }
        }
    }

    protected function parseRootPalette()
    {
        foreach (trimsplit(';', $this->selectRootPalette()) as $strPalette)
        {
            if ($strPalette[0] == '{')
            {
                list($strLegend, $strPalette) = explode(',', $strPalette, 2);
            }

            $arrPalette = $this->parsePalette($strPalette, array());

            if ($arrPalette)
            {
                $this->arrRootPalette[] = array(
                    'legend'  => $strLegend,
                    'palette' => $arrPalette
                );
            }
        }

        // Callback
        $this->arrRootPalette = $this->objDC->getCallbackClass()->parseRootPaletteCallback($this->arrRootPalette);
    }

    protected function selectRootPalette()
    {
        $arrPalettes  = $this->objDC->getPalettesDefinition();
        $arrSelectors = $arrPalettes['__selector__'];

        if (!is_array($arrSelectors))
        {
            return $arrPalettes['default'];
        }

        $arrKeys = array();

        foreach ($arrSelectors as $strSelector)
        {
            $varValue = $this->objCurrentModel->getProperty($strSelector);

            if (!strlen($varValue))
            {
                continue;
            }

            $arrDef    = $this->objDC->getFieldDefinition($strSelector);
            $arrKeys[] = $arrDef['inputType'] == 'checkbox' && !$arrDef['eval']['multiple'] ? $strSelector : $varValue;
        }

        // Build possible palette names from the selector values
        if (!$arrKeys)
        {
            return $arrPalettes['default'];
        }

        // Get an existing palette
        foreach (self::combiner($arrKeys) as $strKey)
        {
            if (is_string($arrPalettes[$strKey]))
            {
                return $arrPalettes[$strKey];
            }
        }

        // ToDo: ??? why exit on this place 

        return $arrPalettes['default'];
    }

    protected function parsePalette($strPalette, array $arrPalette)
    {
        if (!$strPalette)
        {
            return $arrPalette;
        }

        foreach (trimsplit(',', $strPalette) as $strField)
        {
            if (!$strField)
            {
                continue;
            }

            $varValue      = $this->objCurrentModel->getProperty($strField);
            $varSubpalette = $this->getSubpalette($strField, $varValue);

            if (is_array($varSubpalette))
            {
                $arrSubpalettes = $varSubpalette['subpalettes'];
                $varSubpalette  = $varSubpalette['palette'];
            }

            array_push($this->arrStack, is_array($arrSubpalettes) ? $arrSubpalettes : array());

            if ($this->objDC->isEditableField($strField))
            {
                $arrPalette[]  = $strField;
                $arrSubpalette = $this->parsePalette($varSubpalette, array());
                if ($arrSubpalette)
                {
                    $arrPalette[] = $arrSubpalette;
                    if ($this->isSelector($strField))
                    {
                        $this->arrAjaxPalettes[$strField] = $arrSubpalette;
                    }
                }
            }
            else
            { // selector field not editable, inline editable fields of active subpalette
                $arrPalette = $this->parsePalette($varSubpalette, $arrPalette);
            }

            array_pop($this->arrStack);
        }

        return $arrPalette;
    }

    protected function getSubpalette($strField, $varValue)
    {
        if ($this->arrAjaxPalettes[$strField])
        {
            throw new Exception("[DCA Config Error] Recursive subpalette detected. Involved field: [$strField]");
        }

        for ($i = count($this->arrStack) - 1; $i > -1; $i--)
        {
            if (isset($this->arrStack[$i][$strField]))
            {
                if (is_array($this->arrStack[$i][$strField]))
                {
                    return $this->arrStack[$i][$strField][$varValue];
                }
                else
                { // old style
                    return $varValue ? $this->arrStack[$i][$strField] : null;
                }
            }
            elseif (isset($this->arrStack[$i][$strField . '_' . $varValue]))
            {
                return $this->arrStack[$i][$strField . '_' . $varValue];
            }
        }
    }

    public function generateFieldsets($strFieldTemplate, array $arrStates)
    {
        $arrRootPalette = $this->arrRootPalette;

        foreach ($arrRootPalette as &$arrFieldset)
        {
            $strClass = 'tl_box';

            if ($strLegend = &$arrFieldset['legend'])
            {
                $arrClasses = explode(':', substr($strLegend, 1, -1));
                $strLegend  = array_shift($arrClasses);
                $arrClasses = array_flip($arrClasses);
                if (isset($arrStates[$strLegend]))
                {
                    if ($arrStates[$strLegend])
                    {
                        unset($arrClasses['hide']);
                    }
                    else
                    {
                        $arrClasses['collapsed'] = true;
                    }
                }
                $strClass .= ' ' . implode(' ', array_keys($arrClasses));
                $arrFieldset['label']    = isset($GLOBALS['TL_LANG'][$this->objDC->getTable()][$strLegend]) ? $GLOBALS['TL_LANG'][$this->objDC->getTable()][$strLegend] : $strLegend;
            }

            $arrFieldset['class']   = $strClass;
            $arrFieldset['palette'] = $this->generatePalette($arrFieldset['palette'], $strFieldTemplate);
        }

        return $arrRootPalette;
    }

    protected function generatePalette(array $arrPalette, $strFieldTemplate)
    {
        ob_start();

        foreach ($arrPalette as $varField)
        {
            if (is_array($varField))
            {
                /* $strName => this is the input name from the last loop */
                echo '<div id="sub_' . $strName . '">', $this->generatePalette($varField, $strFieldTemplate), '</div>';
            }
            else
            {
                $objWidget = $this->objDC->getWidget($varField);

                if (!$objWidget instanceof Widget)
                {
                    echo $objWidget;
                    continue;
                }

                $arrConfig = $this->objDC->getFieldDefinition($varField);

                $strClass = $arrConfig['eval']['tl_class'];

                // this should be correctly specified in DCAs
//				if($arrConfig['inputType'] == 'checkbox'
//				&& !$arrConfig['eval']['multiple']
//				&& strpos($strClass, 'w50') !== false
//				&& strpos($strClass, 'cbx') === false)
//					$strClass .= ' cbx';

                if ($arrConfig['eval']['submitOnChange'] && $this->isSelector($varField))
                {
                    $objWidget->onclick  = '';
                    $objWidget->onchange = '';
                    $strClass .= ' selector';
                }

                $strName       = specialchars($objWidget->name);
                $blnUpdate     = $arrConfig['update'];
                $strDatepicker = '';

                if ($arrConfig['eval']['datepicker'])
                {
                    if (version_compare(VERSION, '2.10', '>='))
                    {
                        $strDatepicker = $this->buildDatePicker($objWidget);
                    }
                    else
                    {
                        $strDatepicker = sprintf($arrConfig['eval']['datepicker'], json_encode('ctrl_' . $objWidget->id));
                    }
                }

                $objTemplateFoo                = new BackendTemplate($strFieldTemplate);
                $objTemplateFoo->strName       = $strName;
                $objTemplateFoo->strClass      = $strClass;
                $objTemplateFoo->objWidget     = $objWidget;
                $objTemplateFoo->strDatepicker = $strDatepicker;
                $objTemplateFoo->blnUpdate     = $blnUpdate;
                $objTemplateFoo->strHelp       = $this->objDC->generateHelpText($varField);

                echo $objTemplateFoo->parse();

                if (strncmp($arrConfig['eval']['rte'], 'tiny', 4) === 0 && (version_compare(VERSION, '2.10', '>=') || $this->Input->post('isAjax')))
                {
                    echo '<script>tinyMCE.execCommand("mceAddControl", false, "ctrl_' . $strName . '");</script>';
                }
            }
        }

        return ob_get_clean();
    }

    protected function buildDatePicker($objWidget)
    {
        $strFormat = $GLOBALS['TL_CONFIG'][$objWidget->rgxp . 'Format'];

        $arrConfig = array(
            'allowEmpty'        => true,
            'toggleElements'    => '#toggle_' . $objWidget->id,
            'pickerClass'       => 'datepicker_dashboard',
            'format'            => $strFormat,
            'inputOutputFormat' => $strFormat,
            'positionOffset'    => array(
                'x'          => 130,
                'y'          => -185
            ),
            'startDay'   => $GLOBALS['TL_LANG']['MSC']['weekOffset'],
            'days'       => array_values($GLOBALS['TL_LANG']['DAYS']),
            'dayShort'   => $GLOBALS['TL_LANG']['MSC']['dayShortLength'],
            'months'     => array_values($GLOBALS['TL_LANG']['MONTHS']),
            'monthShort' => $GLOBALS['TL_LANG']['MSC']['monthShortLength']
        );

        switch ($objWidget->rgxp)
        {
            case 'datim':
                $arrConfig['timePicker'] = true;
                break;

            case 'time':
                $arrConfig['timePickerOnly'] = true;
                break;
        }

        return 'new DatePicker(' . json_encode('#ctrl_' . $objWidget->id) . ', ' . json_encode($arrConfig) . ');';
    }

    public static function combiner($names)
    {
        $return = array('');

        for ($i = 0; $i < count($names); $i++)
        {
            $buffer = array();

            foreach ($return as $k => $v)
            {
                $buffer[] = ($k % 2 == 0) ? $v : $v . $names[$i];
                $buffer[] = ($k % 2 == 0) ? $v . $names[$i] : $v;
            }

            $return = $buffer;
        }

        return array_filter($return);
    }

    /* /////////////////////////////////////////////////////////////////////////
     * -------------------------------------------------------------------------
     * parentView helper functions
     * -------------------------------------------------------------------------
     * ////////////////////////////////////////////////////////////////////// */

    protected function getParentViewFormattedHeaderFields()
    {
        $add = array();
        $headerFields = $this->arrDCA['list']['sorting']['headerFields'];

        foreach ($headerFields as $v)
        {
            $_v = deserialize($this->objDC->getCurrentParentCollection()->get(0)->getProperty($v));

            if ($v != 'tstamp' || !isset($this->parentDca['fields'][$v]['foreignKey']))
            {
                if (is_array($_v))
                {
                    $_v = implode(', ', $_v);
                }
                elseif ($this->parentDca['fields'][$v]['inputType'] == 'checkbox' && !$this->parentDca['fields'][$v]['eval']['multiple'])
                {
                    $_v = strlen($_v) ? $GLOBALS['TL_LANG']['MSC']['yes'] : $GLOBALS['TL_LANG']['MSC']['no'];
                }
                elseif ($_v && $this->parentDca['fields'][$v]['eval']['rgxp'] == 'date')
                {
                    $_v = $this->parseDate($GLOBALS['TL_CONFIG']['dateFormat'], $_v);
                }
                elseif ($_v && $this->parentDca['fields'][$v]['eval']['rgxp'] == 'time')
                {
                    $_v = $this->parseDate($GLOBALS['TL_CONFIG']['timeFormat'], $_v);
                }
                elseif ($_v && $this->parentDca['fields'][$v]['eval']['rgxp'] == 'datim')
                {
                    $_v = $this->parseDate($GLOBALS['TL_CONFIG']['datimFormat'], $_v);
                }
                elseif (is_array($this->parentDca['fields'][$v]['reference'][$_v]))
                {
                    $_v = $this->parentDca['fields'][$v]['reference'][$_v][0];
                }
                elseif (isset($this->parentDca['fields'][$v]['reference'][$_v]))
                {
                    $_v = $this->parentDca['fields'][$v]['reference'][$_v];
                }
                elseif ($this->parentDca['fields'][$v]['eval']['isAssociative'] || array_is_assoc($this->parentDca['fields'][$v]['options']))
                {
                    $_v = $this->parentDca['fields'][$v]['options'][$_v];
                }
            }

            // Add the sorting field
            if ($_v != '')
            {
                $key       = isset($GLOBALS['TL_LANG'][$this->objDC->getParentTable()][$v][0]) ? $GLOBALS['TL_LANG'][$this->objDC->getParentTable()][$v][0] : $v;
                $add[$key] = $_v;
            }
        }

        // Trigger the header_callback
        $arrHeaderCallback = $this->objDC->getCallbackClass()->headerCallback($add);

        if (!is_null($arrHeaderCallback))
        {
            $add = $arrHeaderCallback;
        }

        $arrHeader = array();
        // Set header data

        foreach ($add as $k => $v)
        {
            if (is_array($v))
            {
                $v = $v[0];
            }

            $arrHeader[$k] = $v;
        }

        return $arrHeader;
    }

    protected function setRecords()
    {
        if (is_array($this->arrDCA['list']['sorting']['child_record_callback']))
        {
            $strGroup = '';

            for ($i = 0; $i < $this->objDC->getCurrentCollecion()->length(); $i++)
            {
                $objModel = $this->objDC->getCurrentCollecion()->get($i);

                // TODO set current
//                $this->current[] = $objModel->getID();                
                // Decrypt encrypted value
                foreach ($objModel as $k => $v)
                {
                    if ($GLOBALS['TL_DCA'][$table]['fields'][$k]['eval']['encrypt'])
                    {
                        $v = deserialize($v);

                        $this->import('Encryption');
                        $objModel->setProperty($k, $this->Encryption->decrypt($v));
                    }
                }

                // Add the group header
                if (!$this->arrDCA['list']['sorting']['disableGrouping'] && $this->objDC->getFirstSorting() != 'sorting')
                {
                    $sortingMode = (count($orderBy) == 1 && $this->objDC->getFirstSorting() == $orderBy[0] && $this->arrDCA['list']['sorting']['flag'] != '' && $this->arrDCA['fields'][$this->objDC->getFirstSorting()]['flag'] == '') ? $this->arrDCA['list']['sorting']['flag'] : $this->arrDCA['fields'][$this->objDC->getFirstSorting()]['flag'];
                    $remoteNew   = $this->objDC->formatCurrentValue($this->objDC->getFirstSorting(), $objModel->getProperty($this->objDC->getFirstSorting()), $sortingMode);
                    $group       = $this->objDC->formatGroupHeader($this->objDC->getFirstSorting(), $remoteNew, $sortingMode, $objModel);

                    if ($group != $strGroup)
                    {
                        $strGroup = $group;
                        $objModel->setProperty('%header%', $group);
                    }
                }

                $objModel->setProperty('%class%', ($this->arrDCA['list']['sorting']['child_record_class'] != '') ? ' ' . $this->arrDCA['list']['sorting']['child_record_class'] : '');

                // Regular buttons
                if (!$this->objDC->isSelectSubmit())
                {
                    $strPrevious = ((!is_null($this->objDC->getCurrentCollecion()->get($i - 1))) ? $this->objDC->getCurrentCollecion()->get($i - 1)->getID() : null);
                    $strNext     = ((!is_null($this->objDC->getCurrentCollecion()->get($i + 1))) ? $this->objDC->getCurrentCollecion()->get($i + 1)->getID() : null);

                    $buttons = $this->generateButtons($objModel, $this->objDC->getTable(), $this->objDC->getRootIds(), false, null, $strPrevious, $strNext);

                    // Sortable table
                    if ($this->parentView['sorting'])
                    {
                        $buttons .= $this->generateParentViewButtons($objModel);
                    }

                    $objModel->setProperty('%buttons%', $buttons);
                }

                $objModel->setProperty('%content%', $this->objDC->getCallbackClass()->childRecordCallback($objModel->getPropertiesAsArray()));
            }
        }
    }

    protected function generateParentViewButtons($objModel)
    {
        $arrReturn = array();
        $blnClipboard  = $blnMultiboard = false;

        $imagePasteAfter = $this->generateImage('pasteafter.gif', sprintf($GLOBALS['TL_LANG'][$this->objDC->getTable()]['pasteafter'][1], $objModel->getID()), 'class="blink"');
        $imagePasteNew   = $this->generateImage('new.gif', sprintf($GLOBALS['TL_LANG'][$this->objDC->getTable()]['pastenew'][1], $objModel->getID()));

        // Create new button
        if (!$this->arrDCA['config']['closed'])
        {
            $arrReturn[] = ' <a href="' . $this->addToUrl('act=create&amp;mode=1&amp;pid=' . $objModel->getID() . '&amp;id=' . $this->objDC->getCurrentParentCollection()->get(0)->getID()) . '" title="' . specialchars(sprintf($GLOBALS['TL_LANG'][$this->objDC->getTable()]['pastenew'][1], $row[$i]['id'])) . '">' . $imagePasteNew . '</a>';
        }

        // TODO clipboard
        // Prevent circular references
        if ($blnClipboard && $arrClipboard['mode'] == 'cut' && $objModel->getID() == $arrClipboard['id'] || $blnMultiboard && $arrClipboard['mode'] == 'cutAll' && in_array($row[$i]['id'], $arrClipboard['id']))
        {
            $arrReturn[] = ' ' . $this->generateImage('pasteafter_.gif', '', 'class="blink"');
        }

        // TODO clipboard
        // Copy/move multiple
        elseif ($blnMultiboard)
        {
            $arrReturn[] = ' <a href="' . $this->addToUrl('act=' . $arrClipboard['mode'] . '&amp;mode=1&amp;pid=' . $row[$i]['id']) . '" title="' . specialchars(sprintf($GLOBALS['TL_LANG'][$this->objDC->getTable()]['pasteafter'][1], $row[$i]['id'])) . '" onclick="Backend.getScrollOffset()">' . $imagePasteAfter . '</a>';
        }

        // TODO clipboard
        // Paste buttons
        elseif ($blnClipboard)
        {
            $arrReturn[] = ' <a href="' . $this->addToUrl('act=' . $arrClipboard['mode'] . '&amp;mode=1&amp;pid=' . $row[$i]['id'] . '&amp;id=' . $arrClipboard['id']) . '" title="' . specialchars(sprintf($GLOBALS['TL_LANG'][$this->objDC->getTable()]['pasteafter'][1], $row[$i]['id'])) . '" onclick="Backend.getScrollOffset()">' . $imagePasteAfter . '</a>';
        }

        return implode('', $arrReturn);
    }

    /* /////////////////////////////////////////////////////////////////////////
     * -------------------------------------------------------------------------
     * listView helper functions
     * -------------------------------------------------------------------------
     * ////////////////////////////////////////////////////////////////////// */

    protected function getTableHead()
    {
        $arrTableHead = array();

        // Generate the table header if the "show columns" option is active
        if ($this->arrDCA['list']['label']['showColumns'])
        {
            foreach ($this->arrDCA['list']['label']['fields'] as $f)
            {
                $arrTableHead[] = array(
                    'class'   => 'tl_folder_tlist col_' . $f . (($f == $this->objDC->getFirstSorting()) ? ' ordered_by' : ''),
                    'content' => $this->arrDCA['fields'][$f]['label'][0]
                );
            }

            $arrTableHead[] = array(
                'class'   => 'tl_folder_tlist tl_right_nowrap',
                'content' => '&nbsp;'
            );
        }

        return $arrTableHead;
    }

    /**
     * Set label for list view
     */
    protected function setListViewLabel()
    {
        // Automatically add the "order by" field as last column if we do not have group headers
        if ($this->arrDCA['list']['label']['showColumns'] && !in_array($this->objDC->getFirstSorting(), $this->arrDCA['list']['label']['fields']))
        {
            $this->arrDCA['list']['label']['fields'][] = $this->objDC->getFirstSorting();
        }

        $remoteCur  = false;
        $groupclass = 'tl_folder_tlist';
        $eoCount    = -1;

        foreach ($this->objDC->getCurrentCollecion() as $objModelRow)
        {
            $args = $this->getListViewLabelArguments($objModelRow);

            // Shorten the label if it is too long
            $label = vsprintf((strlen($this->arrDCA['list']['label']['format']) ? $this->arrDCA['list']['label']['format'] : '%s'), $args);

            if ($this->arrDCA['list']['label']['maxCharacters'] > 0 && $this->arrDCA['list']['label']['maxCharacters'] < strlen(strip_tags($label)))
            {
                $this->import('String');
                $label = trim($this->String->substrHtml($label, $this->arrDCA['list']['label']['maxCharacters'])) . ' â€¦';
            }

            // Remove empty brackets (), [], {}, <> and empty tags from the label
            $label = preg_replace('/\( *\) ?|\[ *\] ?|\{ *\} ?|< *> ?/i', '', $label);
            $label = preg_replace('/<[^>]+>\s*<\/[^>]+>/i', '', $label);

            // Build the sorting groups
            if ($this->arrDCA['list']['sorting']['mode'] > 0)
            {

                $current     = $objModelRow->getProperty($this->objDC->getFirstSorting());
                $orderBy     = $this->arrDCA['list']['sorting']['fields'];
                $sortingMode = (count($orderBy) == 1 && $this->objDC->getFirstSorting() == $orderBy[0] && $this->arrDCA['list']['sorting']['flag'] != '' && $this->arrDCA['fields'][$this->objDC->getFirstSorting()]['flag'] == '') ? $this->arrDCA['list']['sorting']['flag'] : $this->arrDCA['fields'][$this->objDC->getFirstSorting()]['flag'];

                $remoteNew = $this->objDC->formatCurrentValue($this->objDC->getFirstSorting(), $current, $sortingMode);

                // Add the group header
                if (!$this->arrDCA['list']['label']['showColumns'] && !$this->arrDCA['list']['sorting']['disableGrouping'] && ($remoteNew != $remoteCur || $remoteCur === false))
                {
                    $eoCount = -1;

                    $objModelRow->setProperty('%group%', array(
                        'class' => $groupclass,
                        'value' => $this->objDC->formatGroupHeader($this->objDC->getFirstSorting(), $remoteNew, $sortingMode, $objModelRow)
                    ));

                    $groupclass = 'tl_folder_list';
                    $remoteCur  = $remoteNew;
                }
            }

            $objModelRow->setProperty('%rowClass%', ((++$eoCount % 2 == 0) ? 'even' : 'odd'));

            $colspan = 1;

            // Call label callback            
            $mixedArgs = $this->objDC->getCallbackClass()->labelCallback($objModelRow, $label, $this->arrDCA['list']['label'], $args);

            if (!is_null($mixedArgs))
            {
                // Handle strings and arrays (backwards compatibility)
                if (!$this->arrDCA['list']['label']['showColumns'])
                {
                    $label = is_array($mixedArgs) ? implode(' ', $mixedArgs) : $mixedArgs;
                }
                elseif (!is_array($mixedArgs))
                {
                    $mixedArgs = array($mixedArgs);
                    $colspan = count($this->arrDCA['list']['label']['fields']);
                }
            }

            $arrLabel = array();

            // Add columns
            if ($this->arrDCA['list']['label']['showColumns'])
            {
                foreach ($args as $j => $arg)
                {
                    $arrLabel[] = array(
                        'colspan' => $colspan,
                        'class'   => 'tl_file_list col_' . $this->arrDCA['list']['label']['fields'][$j] . (($this->arrDCA['list']['label']['fields'][$j] == $this->objDC->getFirstSorting()) ? ' ordered_by' : ''),
                        'content' => (($arg != '') ? $arg : '-')
                    );
                }
            }
            else
            {
                $arrLabel[] = array(
                    'colspan' => NULL,
                    'class'   => 'tl_file_list',
                    'content' => $label
                );
            }

            $objModelRow->setProperty('%label%', $arrLabel);
        }
    }

    /**
     * Get arguments for label
     * 
     * @param InterfaceGeneralModel $objModelRow
     * @return array
     */
    protected function getListViewLabelArguments($objModelRow)
    {
        if ($this->arrDCA['list']['sorting']['mode'] == 6)
        {
            $this->loadDataContainer($objDC->getParentTable());
            $objTmpDC = new DC_General($objDC->getParentTable());

            $arrCurrentDCA = $objTmpDC->getDCA();
        }
        else
        {
            $arrCurrentDCA = $this->arrDCA;
        }

        $args = array();
        $showFields = $arrCurrentDCA['list']['label']['fields'];

        // Label
        foreach ($showFields as $k => $v)
        {
            if (strpos($v, ':') !== false)
            {
                $args[$k] = $objModelRow->getProperty('%args%');
            }
            elseif (in_array($this->arrDCA['fields'][$v]['flag'], array(5, 6, 7, 8, 9, 10)))
            {
                if ($this->arrDCA['fields'][$v]['eval']['rgxp'] == 'date')
                {
                    $args[$k] = $this->parseDate($GLOBALS['TL_CONFIG']['dateFormat'], $objModelRow->getProperty($v));
                }
                elseif ($this->arrDCA['fields'][$v]['eval']['rgxp'] == 'time')
                {
                    $args[$k] = $this->parseDate($GLOBALS['TL_CONFIG']['timeFormat'], $objModelRow->getProperty($v));
                }
                else
                {
                    $args[$k] = $this->parseDate($GLOBALS['TL_CONFIG']['datimFormat'], $objModelRow->getProperty($v));
                }
            }
            elseif ($this->arrDCA['fields'][$v]['inputType'] == 'checkbox' && !$this->arrDCA['fields'][$v]['eval']['multiple'])
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
                elseif (($arrCurrentDCA['fields'][$v]['eval']['isAssociative'] || array_is_assoc($arrCurrentDCA['fields'][$v]['options'])) && isset($arrCurrentDCA['fields'][$v]['options'][$objModelRow->getProperty($v)]))
                {
                    $args[$k] = $arrCurrentDCA['fields'][$v]['options'][$objModelRow->getProperty($v)];
                }
                else
                {
                    $args[$k] = $objModelRow->getProperty($v);
                }
            }
        }

        return $args;
    }

    /* /////////////////////////////////////////////////////////////////////////
     * -------------------------------------------------------------------------
     * Button functions
     * -------------------------------------------------------------------------
     * ////////////////////////////////////////////////////////////////////// */

    /**
     * Generate header display buttons
     * 
     * @param string $strButtonId
     * @return string 
     */
    protected function displayButtons($strButtonId)
    {
        $arrReturn = array();

        // Add open wrapper
        $arrReturn[] = '<div id="' . $strButtonId . '">';

        // Add back button
        $arrReturn[] = (($this->objDC->isSelectSubmit() || $this->objDC->getParentTable()) ? '<a href="' . $this->getReferer(true, $this->objDC->getParentTable()) . '" class="header_back" title="' . specialchars($GLOBALS['TL_LANG']['MSC']['backBT']) . '" accesskey="b" onclick="Backend.getScrollOffset();">' . $GLOBALS['TL_LANG']['MSC']['backBT'] . '</a>' : '');

        // Add divider
        $arrReturn[] = (($this->objDC->getParentTable() && !$this->objDC->isSelectSubmit()) ? ' &nbsp; :: &nbsp;' : '');

        if (!$this->objDC->isSelectSubmit())
        {
            // Add new button
            $arrReturn[] = ' ' . (!$this->arrDCA['config']['closed'] ? '<a href="' . (strlen($this->objDC->getParentTable()) ? $this->addToUrl('act=create' . (($this->arrDCA['list']['sorting']['mode'] < 4) ? '&amp;mode=2' : '') . '&amp;pid=' . $this->objDC->getId()) : $this->addToUrl('act=create')) . '" class="header_new" title="' . specialchars($GLOBALS['TL_LANG'][$this->objDC->getTable()]['new'][1]) . '" accesskey="n" onclick="Backend.getScrollOffset();">' . $GLOBALS['TL_LANG'][$this->objDC->getTable()]['new'][0] . '</a>' : '');

            // Add global buttons
            $arrReturn[] = $this->generateGlobalButtons();
        }

        // Add close wrapper
        $arrReturn[] = '</div>';

        $arrReturn[] = $this->getMessages(true);

        return implode('', $arrReturn);
    }

    /**
     * Compile global buttons from the table configuration array and return them as HTML
     * 
     * @param boolean $blnForceSeparator
     * @return string
     */
    protected function generateGlobalButtons($blnForceSeparator = false)
    {
        if (!is_array($this->arrDCA['list']['global_operations']))
        {
            return '';
        }

        $return = '';

        switch ($this->arrDCA['list']['sorting']['mode'])
        {
            case 5:
            case 6:
                // Open/close all nodes
                $return .= '&#160; :: &#160; <a href="'
                        . $this->addToUrl('ptg=all')
                        . '" class="header_toggle" title="'
                        . specialchars($GLOBALS['TL_LANG']['MSC']['toggleNodes'])
                        . '">'
                        . $GLOBALS['TL_LANG']['MSC']['toggleNodes']
                        . '</a> '
                        . "\n";
                break;
        }

        foreach ($this->arrDCA['list']['global_operations'] as $k => $v)
        {
            $v = is_array($v) ? $v : array($v);
            $label      = is_array($v['label']) ? $v['label'][0] : $v['label'];
            $title      = is_array($v['label']) ? $v['label'][1] : $v['label'];
            $attributes = strlen($v['attributes']) ? ' ' . ltrim($v['attributes']) : '';

            if (!strlen($label))
            {
                $label = $k;
            }

            // Call a custom function instead of using the default button
            $strButtonCallback = $this->objDC->getCallbackClass()->globalButtonCallback($v, $label, $title, $attributes, $this->objDC->getTable(), $this->objDC->getRootIds());
            if (!is_null($strButtonCallback))
            {
                $return .= $strButtonCallback;
                continue;
            }

            $return .= ' &#160; :: &#160; <a href="' . $this->addToUrl($v['href']) . '" class="' . $v['class'] . '" title="' . specialchars($title) . '"' . $attributes . '>' . $label . '</a> ';
        }

        return ($this->arrDCA['config']['closed'] && !$blnForceSeparator) ? preg_replace('/^ &#160; :: &#160; /', '', $return) : $return;
    }

    /**
     * Compile buttons from the table configuration array and return them as HTML
     * 
     * @param InterfaceGeneralModel $objModelRow
     * @param string $strTable
     * @param array $arrRootIds
     * @param boolean $blnCircularReference
     * @param array $arrChildRecordIds
     * @param int $strPrevious
     * @param int $strNext
     * @return string
     */
    protected function generateButtons(InterfaceGeneralModel $objModelRow, $strTable, $arrRootIds = array(), $blnCircularReference = false, $arrChildRecordIds = null, $strPrevious = null, $strNext = null)
    {
        if (!count($GLOBALS['TL_DCA'][$strTable]['list']['operations']))
        {
            return '';
        }

        $return = '';

        foreach ($GLOBALS['TL_DCA'][$strTable]['list']['operations'] as $k => $v)
        {
            $v = is_array($v) ? $v : array($v);
            $label      = strlen($v['label'][0]) ? $v['label'][0] : $k;
            $title      = sprintf((strlen($v['label'][1]) ? $v['label'][1] : $k), $objModelRow->getProperty('id'));
            $attributes = strlen($v['attributes']) ? ' ' . ltrim(sprintf($v['attributes'], $objModelRow->getProperty('id'), $objModelRow->getProperty('id'))) : '';

            // Call a custom function instead of using the default button
            $strButtonCallback = $this->objDC->getCallbackClass()->buttonCallback($objModelRow, $v, $label, $title, $attributes, $strTable, $arrRootIds, $arrChildRecordIds, $blnCircularReference, $strPrevious, $strNext);
            if (!is_null($strButtonCallback))
            {
                $return .= $strButtonCallback;
                continue;
            }

            // Generate all buttons except "move up" and "move down" buttons
            if ($k != 'move' && $v != 'move')
            {
                $return .= '<a href="' . $this->addToUrl($v['href'] . '&amp;id=' . $objModelRow->getProperty('id')) . '" title="' . specialchars($title) . '"' . $attributes . '>' . $this->generateImage($v['icon'], $label) . '</a> ';
                continue;
            }

            $arrDirections = array('up', 'down');
            $arrRootIds = is_array($arrRootIds) ? $arrRootIds : array($arrRootIds);

            foreach ($arrDirections as $dir)
            {
                $label = strlen($GLOBALS['TL_LANG'][$strTable][$dir][0]) ? $GLOBALS['TL_LANG'][$strTable][$dir][0] : $dir;
                $title = strlen($GLOBALS['TL_LANG'][$strTable][$dir][1]) ? $GLOBALS['TL_LANG'][$strTable][$dir][1] : $dir;

                $label = $this->generateImage($dir . '.gif', $label);
                $href  = strlen($v['href']) ? $v['href'] : '&amp;act=move';

                if ($dir == 'up')
                {
                    $return .= ((is_numeric($strPrevious) && (!in_array($objModelRow->getProperty('id'), $arrRootIds) || !count($GLOBALS['TL_DCA'][$strTable]['list']['sorting']['root']))) ? '<a href="' . $this->addToUrl($href . '&amp;id=' . $objModelRow->getProperty('id')) . '&amp;sid=' . intval($strPrevious) . '" title="' . specialchars($title) . '"' . $attributes . '>' . $label . '</a> ' : $this->generateImage('up_.gif')) . ' ';
                    continue;
                }

                $return .= ((is_numeric($strNext) && (!in_array($objModelRow->getProperty('id'), $arrRootIds) || !count($GLOBALS['TL_DCA'][$strTable]['list']['sorting']['root']))) ? '<a href="' . $this->addToUrl($href . '&amp;id=' . $objModelRow->getProperty('id')) . '&amp;sid=' . intval($strNext) . '" title="' . specialchars($title) . '"' . $attributes . '>' . $label . '</a> ' : $this->generateImage('down_.gif')) . ' ';
            }
        }

        return trim($return);
    }

    /* /////////////////////////////////////////////////////////////////////////
     * -------------------------------------------------------------------------
     * Panel
     * -------------------------------------------------------------------------
     * ////////////////////////////////////////////////////////////////////// */

    protected function panel()
    {
        $arrReturn = array();

        if (is_array($this->objDC->getPanelView()) && count($this->objDC->getPanelView()) > 0)
        {
            $objTemplate         = new BackendTemplate('dcbe_general_panel');
            $objTemplate->action = ampersand($this->Environment->request, true);
            $objTemplate->theme  = $this->getTheme();
            $objTemplate->panel  = $this->objDC->getPanelView();
            $arrReturn[]         = $objTemplate->parse();
        }

        return implode('', $arrReturn);
    }

}

?>