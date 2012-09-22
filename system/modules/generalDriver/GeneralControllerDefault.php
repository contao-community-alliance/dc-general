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
 * @copyright  MEN AT WORK 2012
 * @package    generalDriver
 * @license    GNU/LGPL
 * @filesource
 */
class GeneralControllerDefault extends Controller implements InterfaceGeneralController
{
    /* /////////////////////////////////////////////////////////////////////////
     * -------------------------------------------------------------------------
     * Vars
     * -------------------------------------------------------------------------
     * ////////////////////////////////////////////////////////////////////// */

    // Obejcts -----------------------

    /**
     * Contao Session Object
     * @var Session
     */
    protected $objSession = null;

    /**
     * Current DC General
     * @var DC_General
     */
    protected $objDC = null;

    /**
     * Current dataprovider
     * @var InterfaceGeneralData
     */
    protected $objDataProvider = null;

    // Current -----------------------

    /**
     * A list with all current ID`s
     * @var array
     */
    protected $arrInsertIDs = array();

    // States ------------------------

    /**
     * State of Show/Close all
     * @var boolean
     */
    protected $blnShowAllEntries = false;

    // Misc. -------------------------

    /**
     * Error msg
     *
     * @var string
     */
    protected $notImplMsg = "<div style='text-align:center; font-weight:bold; padding:40px;'>The function/view &quot;%s&quot; is not implemented.</div>";

    /**
     * Field for the function sortCollection
     *
     * @var string $arrColSort
     */
    protected $arrColSort;

    // Const -------------------------

    /**
     * Const Language
     */

    const LANGUAGE_SL = 1;
    const LANGUAGE_ML = 2;

    /* /////////////////////////////////////////////////////////////////////////
     * -------------------------------------------------------------------------
     * Magic functions
     * -------------------------------------------------------------------------
     * ////////////////////////////////////////////////////////////////////// */

    public function __construct()
    {
        parent::__construct();

        // Init Helper
        $this->objSession = Session::getInstance();

        // Check some vars
        $this->blnShowAllEntries = ($this->Input->get('ptg') == 'all') ? 1 : 0;
    }

    public function __call($name, $arguments)
    {
        switch ($name)
        {
            default:
                throw new Exception("Error Processing Request: " . $name, 1);
                return sprintf($this->notImplMsg, $name);
                break;
        };
    }

    /**
     * Get the dataprovider from DC
     */
    protected function loadCurrentDataProvider()
    {
        $this->objDataProvider = $this->getDC()->getDataProvider();
    }

    /* /////////////////////////////////////////////////////////////////////////
     * -------------------------------------------------------------------------
     * Getter & Setter
     * -------------------------------------------------------------------------
     * ////////////////////////////////////////////////////////////////////// */

    /**
     * Get DC General
     * @return DC_General
     */
    public function getDC()
    {
        return $this->objDC;
    }

    /**
     * Set DC General
     * @param DC_General $objDC
     */
    public function setDC($objDC)
    {
        $this->objDC = $objDC;
    }

    /**
     * Get filter for the data provider
     * @return array();
     */
    protected function getFilter()
    {
        $arrFilter = $this->getDC()->getFilter();

        if ($arrFilter)
        {
            return $arrFilter;
        }

        $arrDCA = $this->getDC()->getDCA();

        // Custom filter
        if (is_array($arrDCA['list']['sorting']['filter']) && !empty($arrDCA['list']['sorting']['filter']))
        {
            $arrFilters = array();
            foreach ($arrDCA['list']['sorting']['filter'] as $filter)
            {
                $arrFilters[] = array('operation' => '=', 'property'  => $filter[0], 'value'     => $filter[1]);
            }
            if (count($arrFilters))
            {
                $this->getDC()->setFilter(array(array('operation' => 'AND', 'childs'    => $arrFilters)));
            }
        }

        if (is_array($arrDCA['list']['sorting']['root']) && !empty($arrDCA['list']['sorting']['root']))
        {
            $arrFilters = array();
            foreach ($arrDCA['list']['sorting']['root'] as $mixId)
            {
                $arrFilters[] = array('operation' => '=', 'property'  => 'id', 'value'     => $mixId);
            }
            if (count($arrFilters))
            {
                $this->getDC()->setFilter(array(array('operation' => 'OR', 'childs'    => $arrFilters)));
            }
        }

        // TODO: we need to transport all the fields from the root conditions via the url and set filters accordingly here.
        // FIXME: this is only valid for mode 4 appearantly, fix for other views.
        if ($this->Input->get('table') && !is_null($this->getDC()->getParentTable()))
        {
            $objParentDP   = $this->getDC()->getDataProvider('parent');
            $objParentItem = $objParentDP->fetch($objParentDP->getEmptyConfig()->setId(CURRENT_ID));
            $objCollection = $objParentDP->getEmptyCollection();
            $objCollection->add($objParentItem);
            // NOTE: we set the parent collection here, which will get used in the parentView() routine.
            $this->getDC()->setCurrentParentCollection($objCollection);
            $arrFilter     = $this->getDC()->getChildCondition($objParentItem, 'self');

            $this->getDC()->setFilter($arrFilter);
        }

        // FIXME implement panel filter from session

        return $this->getDC()->getFilter();
    }

    /**
     * Get limit for the data provider
     * @return array
     */
    protected function getLimit()
    {
        $arrLimit = array(0, 0);
        if (!is_null($this->getDC()->getLimit()))
        {
            $arrLimit = explode(',', $this->getDC()->getLimit());
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
        $arrDCA       = $this->getDC()->getDCA();
        $mixedOrderBy = $arrDCA['list']['sorting']['fields'];

        if (is_null($this->getDC()->getFirstSorting()))
        {
            $this->getDC()->setFirstSorting(preg_replace('/\s+.*$/i', '', $mixedOrderBy[0]));
        }

        // Check if current sorting is set
        if (!is_null($this->getDC()->getSorting()))
        {
            $mixedOrderBy = $this->getDC()->getSorting();
        }

        if (is_array($mixedOrderBy) && $mixedOrderBy[0] != '')
        {
            foreach ($mixedOrderBy as $key => $strField)
            {
                if ($arrDCA['fields'][$strField]['eval']['findInSet'])
                {
                    $arrOptionsCallback = $this->getDC()->getCallbackClass()->optionsCallback($strField);

                    if (!is_null($arrOptionsCallback))
                    {
                        $keys = $arrOptionsCallback;
                    }
                    else
                    {
                        $keys = $arrDCA['fields'][$strField]['options'];
                    }

                    if ($arrDCA['fields'][$v]['eval']['isAssociative'] || array_is_assoc($keys))
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
        if ($arrDCA['list']['sorting']['mode'] == 1 && ($arrDCA['list']['sorting']['flag'] % 2) == 0)
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
        $firstOrderBy = '';

        // Check if current sorting is set
        if (!is_null($this->getDC()->getSorting()))
        {
            $mixedOrderBy = $this->getDC()->getSorting();
        }

        if (is_array($mixedOrderBy) && $mixedOrderBy[0] != '')
        {
            $firstOrderBy = preg_replace('/\s+.*$/i', '', $mixedOrderBy[0]);

            // Order by the foreign key
            if (isset($arrDCA['fields'][$firstOrderBy]['foreignKey']))
            {
                $key = explode('.', $arrDCA['fields'][$firstOrderBy]['foreignKey'], 2);

                $this->foreignKey = true;

                // TODO remove sql
                $this->arrFields = array(
                    '*',
                    "(SELECT " . $key[1] . " FROM " . $key[0] . " WHERE " . $this->getDC()->getTable() . "." . $firstOrderBy . "=" . $key[0] . ".id) AS foreignKey"
                );

                $mixedOrderBy[0] = 'foreignKey';
            }
        }
        else if (is_array($GLOBALS['TL_DCA'][$this->getDC()->getTable()]['list']['sorting']['fields']))
        {
            $mixedOrderBy = $GLOBALS['TL_DCA'][$this->getDC()->getTable()]['list']['sorting']['fields'];
            $firstOrderBy = preg_replace('/\s+.*$/i', '', $mixedOrderBy[0]);
        }

        if (is_array($mixedOrderBy) && $mixedOrderBy[0] != '')
        {
            foreach ($mixedOrderBy as $key => $strField)
            {
                $mixedOrderBy[$key] = $strField;
            }
        }

        $this->getDC()->setFirstSorting($firstOrderBy);

        return $mixedOrderBy;
    }

    /* /////////////////////////////////////////////////////////////////////////
     * -------------------------------------------------------------------------
     *  Core Support functions
     * -------------------------------------------------------------------------
     * ////////////////////////////////////////////////////////////////////// */

    /**
     * Redirects to the real back end module.
     */
    protected function redirectHome()
    {
        if ($this->Input->get('table') && $this->Input->get('id'))
        {
            $this->redirect(sprintf('contao/main.php?do=%s&table=%s&id=%s', $this->Input->get('do'), $this->getDC()->getTable(), $this->Input->get('id')));
        }

        $this->redirect('contao/main.php?do=' . $this->Input->get('do'));
    }

    /**
     * Check if the curren model support multi language.
     * Load the language from SESSION, POST or use a fallback.
     *
     * @return int return the mode multilanguage, singellanguage or unsupportet language
     */
    protected function checkLanguage()
    {
        // Load basic informations
        $intID           = $this->getDC()->getId();
        $objDataProvider = $this->getDC()->getDataProvider();

        if (in_array('InterfaceGeneralDataML', class_implements($objDataProvider)))
        {
            $objLanguagesSupported = $this->getDC()->getDataProvider()->getLanguages($intID);
        }
        else
        {
            $objLanguagesSupported = NULL;
        }

        //Check if we have some languages
        if ($objLanguagesSupported == null)
        {
            return self::LANGUAGE_SL;
        }

        // Load language from Session
        $arrSession = $this->Session->get("dc_general");
        if (!is_array($arrSession))
        {
            $arrSession = array();
        }

        // try to get the language from session
        if (isset($arrSession["ml_support"][$this->getDC()->getTable()][$intID]))
        {
            $strCurrentLanguage = $arrSession["ml_support"][$this->getDC()->getTable()][$intID];
        }
        else
        {
            $strCurrentLanguage = $GLOBALS['TL_LANGUAGE'];
        }

        // Make a array from the collection
        $arrLanguage = array();
        foreach ($objLanguagesSupported as $value)
        {
            $arrLanguage[$value->getID()] = $value->getProperty("name");
        }

        // Get/Check the new language
        if (strlen($this->Input->post("language")) != 0 && $_POST['FORM_SUBMIT'] == 'language_switch')
        {
            if (key_exists($this->Input->post("language"), $arrLanguage))
            {
                $strCurrentLanguage                                           = $this->Input->post("language");
                $arrSession["ml_support"][$this->getDC()->getTable()][$intID] = $strCurrentLanguage;
            }
            else if (key_exists($strCurrentLanguage, $arrLanguage))
            {
                $arrSession["ml_support"][$this->getDC()->getTable()][$intID] = $strCurrentLanguage;
            }
            else
            {
                $objlanguageFallback                                          = $objDataProvider->getFallbackLanguage();
                $strCurrentLanguage                                           = $objlanguageFallback->getID();
                $arrSession["ml_support"][$this->getDC()->getTable()][$intID] = $strCurrentLanguage;
            }
        }

        $this->Session->set("dc_general", $arrSession);

        $objDataProvider->setCurrentLanguage($strCurrentLanguage);

        return self::LANGUAGE_ML;
    }

    /* /////////////////////////////////////////////////////////////////////////
     * -------------------------------------------------------------------------
     * Clipboard functions
     * -------------------------------------------------------------------------
     * ////////////////////////////////////////////////////////////////////// */

    /**
     * Clear clipboard
     * @param boolean $blnRedirect True - redirect to home site
     */
    protected function resetClipboard($blnRedirect = false)
    {
        // Get clipboard
        $arrClipboard = $this->loadClipboard();

        $this->getDC()->setClipboardState(false);
        unset($arrClipboard[$this->getDC()->getTable()]);

        // Save
        $this->saveClipboard($arrClipboard);

        // Redirect
        if ($blnRedirect == true)
        {
            $this->redirectHome();
        }

        // Set DC state
        $this->getDC()->setClipboardState(false);
    }

    /**
     * Check clipboard state. Clear or save state of it.
     */
    protected function checkClipboard()
    {
        $arrClipboard = $this->loadClipboard();

        // Reset Clipboard
        if ($this->Input->get('clipboard') == '1')
        {
            $this->resetClipboard(true);
        }
        // Add new entry
        else if ($this->Input->get('act') == 'paste')
        {
            $this->getDC()->setClipboardState(true);

            $arrClipboard[$this->getDC()->getTable()] = array(
                'id'     => $this->Input->get('id'),
                'source' => $this->Input->get('source'),
                'childs' => $this->Input->get('childs'),
                'mode'   => $this->Input->get('mode'),
                'pdp'    => $this->Input->get('pdp'),
                'cdp'    => $this->Input->get('cdp'),
            );

            switch ($this->Input->get('mode'))
            {
                case 'cut':
                    // Id Array
                    $arrIDs = array();
                    $arrIDs[] = $this->Input->get('source');

                    switch ($this->arrDCA['list']['sorting']['mode'])
                    {
                        case 5:
                            // Run each id
                            for ($i = 0; $i < count($arrIDs); $i++)
                            {
                                // Get current model
                                $objCurrentConfig = $this->getDC()->getDataProvider()->getEmptyConfig();
                                $objCurrentConfig->setId($arrIDs[$i]);
//                        $objCurrentConfig->setFields(array($arrJoinCondition[0]['srcField']));

                                $objCurrentModel = $this->getDC()->getDataProvider()->fetch($objCurrentConfig);

                                // Get the join field
                                $arrJoinCondition = $this->getDC()->getChildCondition($objCurrentModel, 'self');

                                $objChildConfig = $this->getDC()->getDataProvider()->getEmptyConfig();
                                $objChildConfig->setFilter($arrJoinCondition);
                                $objChildConfig->setIdOnly(true);

                                $objChildCollection = $this->getDC()->getDataProvider()->fetchAll($objChildConfig);

                                foreach ($objChildCollection as $key => $value)
                                {
                                    if (!in_array($value, $arrIDs))
                                    {
                                        $arrIDs[] = $value;
                                    }
                                }
                            }
                            break;
                    }

                    $arrClipboard[$this->getDC()->getTable()]['ignoredIDs'] = $arrIDs;

                    break;
            }

            $this->getDC()->setClipboard($arrClipboard[$this->getDC()->getTable()]);
        }
        // Check clipboard from session
        else if (key_exists($this->getDC()->getTable(), $arrClipboard))
        {
            $this->getDC()->setClipboardState(true);
            $this->getDC()->setClipboard($arrClipboard[$this->getDC()->getTable()]);
        }

        $this->saveClipboard($arrClipboard);
    }

    protected function loadClipboard()
    {
        $arrClipboard = $this->Session->get('CLIPBOARD');
        if (!is_array($arrClipboard))
        {
            $arrClipboard = array();
        }

        return $arrClipboard;
    }

    protected function saveClipboard($arrClipboard)
    {
        if (is_array($arrClipboard))
        {
            $this->Session->set('CLIPBOARD', $arrClipboard);
        }
    }

    /* /////////////////////////////////////////////////////////////////////////
     * -------------------------------------------------------------------------
     * Check Function
     * -------------------------------------------------------------------------
     * ////////////////////////////////////////////////////////////////////// */

    /**
     * Check if is editable AND not clodes
     */
    protected function checkIsWritable()
    {
        // Check if table is editable
        if (!$this->getDC()->isEditable())
        {
            $this->log('Table ' . $this->getDC()->getTable() . ' is not editable', 'DC_General - Controller - copy()', TL_ERROR);
            $this->redirect('contao/main.php?act=error');
        }

        // Check if table is editable
        if ((!$this->getDC()->getId()) && $this->getDC()->isClosed())
        {
            $this->log('Table ' . $this->getDC()->getTable() . ' is closed', 'DC_General - Controller - copy()', TL_ERROR);
            $this->redirect('contao/main.php?act=error');
        }
    }

    /* /////////////////////////////////////////////////////////////////////////
     * -------------------------------------------------------------------------
     * Core Functions
     * -------------------------------------------------------------------------
     * ////////////////////////////////////////////////////////////////////// */

    /**
     * Cut and paste
     *
     * <p>
     * -= GET Parameter =-<br/>
     * act      - Mode like cut | copy | and co <br/>
     * after    - ID of target element <br/>
     * source   - ID of the element which should moved <br/>
     * mode     - 1 Insert after | 2 Insert into <br/>
     * pid      - Id of the parent used in list mode 4,5 <br/>
     * child    - WTF at the moment unknown <br/>
     * pdp      - Parent Data Provider real name <br/>
     * cdp      - Current Data Provider real name <br/>
     * id       - Parent child id used for redirect <br/>
     * </p>
     */
    public function cut()
    {
        // Checks
        $this->checkIsWritable();

        // Load some vars
        $this->loadCurrentDataProvider();

        //main.php?do=metamodels&table=tl_metamodel_dcasetting&id=1&act=cut&mode=1&pid=1&after=3&source=8&childs=&pdp=tl_metamodel_dca&cdp=tl_metamodel_dcasetting

        $mixAfter  = $this->Input->get('after');
        $mixSource = $this->Input->get('source');
        $intMode   = $this->Input->get('mode');
        $mixPid    = $this->Input->get('pid');
        $mixChild  = $this->Input->get('child');
        $strPDP    = $this->Input->get('pdp');
        $strCDP    = $this->Input->get('cdp');
        $intId     = $this->Input->get('id');

        // Check basic vars
        if (empty($mixSource) || empty($mixAfter) || empty($intMode) || empty($strCDP))
        {
            $this->log('Missing parameter for copy in ' . $this->getDC()->getTable(), __CLASS__ . ' - ' . __FUNCTION__, TL_ERROR);
            $this->redirect('contao/main.php?act=error');
        }

        // Load current data provider
        $objCurrentDataProvider = $this->objDC->getDataProvider($strCDP);
        if ($objCurrentDataProvider == null)
        {
            throw new Exception('Could not load current data provider in ' . __CLASS__ . ' - ' . __FUNCTION__);
        }

        $objParentDataProvider = null;
        if (!empty($strPDP))
        {
            $objParentDataProvider = $this->objDC->getDataProvider($strPDP);
            if ($objCurrentDataProvider == null)
            {
                throw new Exception('Could not load parent data provider ' . $strPDP . ' in ' . __CLASS__ . ' - ' . __FUNCTION__);
            }
        }

        // Load the source model
        $objSrcModel = $objCurrentDataProvider->fetch($this->objDataProvider->getEmptyConfig()->setId($mixSource));

        // Load current dca
        $arrDCA = $this->getDC()->getDCA();

        // Check mode
        switch ($arrDCA['list']['sorting']['mode'])
        {
            case 1:
            case 2:
            case 3:
                return vsprintf($this->notImplMsg, 'cut - Mode ' . $arrDCA['list']['sorting']['mode']);
                break;

            case 4:
                $this->getNewPosition($objCurrentDataProvider, $objParentDataProvider, $objSrcModel, $mixAfter, 'cut', $intMode);
                break;

            case 5:
                switch ($intMode)
                {
                    // Insert After => Get the parent from the target id
                    case 1:
                        $objParent = $this->getParent('self', null, $intPid);
                        if ($objParent)
                        {
                            $this->setParent($objSrcModel, $objParent, 'self');
                        }
                        else
                        {
                            $this->setRoot($objSrcModel, 'self');
                        }

                        break;

                    // Insert Into => use the pid
                    case 2:
                        if (!$intPid)
                        {
                            // no pid => insert at top level.
                            $this->setRoot($objSrcModel, 'self');
                        }
                        else if ($this->isRootEntry('self', $intPid))
                        {
                            $this->setRoot($objSrcModel, 'self');
                        }
                        else
                        {
                            $objParentConfig = $this->getDC()->getDataProvider()->getEmptyConfig();
                            $objParentConfig->setId($intPid);

                            $objParentModel = $this->getDC()->getDataProvider()->fetch($objParentConfig);

                            $this->setParent($objSrcModel, $objParentModel, 'self');
                        }
                        break;

                    default:
                        $this->log('Unknown create mode for copy in ' . $this->getDC()->getTable(), 'DC_General - Controller - copy()', TL_ERROR);
                        $this->redirect('contao/main.php?act=error');
                        break;
                }
                break;

            default:
                return vsprintf($this->notImplMsg, 'cut - Mode ' . $arrDCA['list']['sorting']['mode']);
                break;
        }

        // Save new sorting
        $this->objDataProvider->save($objSrcModel);

        // Reset clipboard + redirect
        $this->resetClipboard(true);
    }

    /**
     * Copy a entry and all childs
     *
     * @return string error msg for an unknown mode
     */
    public function copy()
    {
        // Load current DCA
        $this->loadCurrentDataProvider();

        // Check
        $this->checkIsWritable();

        $arrDCA = $this->getDC()->getDCA();
        switch ($arrDCA['list']['sorting']['mode'])
        {
            case 5:
                // Init Vars
                $intMode   = $this->Input->get('mode');
                $intPid    = $this->Input->get('pid');
                $intId     = $this->Input->get('id');
                $intChilds = $this->Input->get('childs');

                if (strlen($intMode) == 0 || strlen($intPid) == 0 || strlen($intId) == 0)
                {
                    $this->log('Missing parameter for copy in ' . $this->getDC()->getTable(), 'DC_General - Controller - copy()', TL_ERROR);
                    $this->redirect('contao/main.php?act=error');
                }

                // Get the join field
                $arrJoinCondition = $this->getDC()->getJoinConditions('self');

                // Insert the copy
                $this->insertCopyModel($intId, $intPid, $intMode, $intChilds, $arrJoinCondition[0]['srcField'], $arrJoinCondition[0]['dstField'], $arrJoinCondition[0]['operation']);
                break;

            default:
                return vsprintf($this->notImplMsg, 'copy - Mode ' . $arrDCA['list']['sorting']['mode']);
                break;
        }

        // Reset clipboard + redirect
        $this->resetClipboard(true);
    }

    /**
     * Create a new entry
     */
    public function create()
    {
        // Load current values
        $this->loadCurrentDataProvider();

        // Check
        $this->checkIsWritable();

        // Load fields and co
        $this->getDC()->loadEditableFields();
        $this->getDC()->setWidgetID($this->getDC()->getId());

        // Check if we have fields
        if (!$this->getDC()->hasEditableFields())
        {
            $this->redirect($this->getReferer());
        }

        // Load something
        $this->getDC()->preloadTinyMce();

        // Check load multi language check
        $this->checkLanguage($this->getDC());

        // Set buttons
        $this->getDC()->addButton("save");
        $this->getDC()->addButton("saveNclose");

        // Load record from data provider
        $objDBModel = $this->objDataProvider->getEmptyModel();
        $this->getDC()->setCurrentModel($objDBModel);

        $arrDCA = $this->getDC()->getDCA();

        if ($arrDCA['list']['sorting']['mode'] < 4)
        {
            // check if the pid id/word is set
            if ($this->Input->get('pid'))
            {
                $objParentDP = $this->objDC->getDataProvider('parent');
                $objParent   = $objParentDP->fetch($objParentDP->getEmptyConfig()->setId($this->Input->get('pid')));
                $this->setParent($objDBModel, $objParent, 'self');
            }
        }
        else if ($arrDCA['list']['sorting']['mode'] == 4)
        {
            // check if the pid id/word is set
            if ($this->Input->get('pid') == '')
            {
                $this->log('Missing pid for new entry in ' . $this->getDC()->getTable(), 'DC_General - Controller - create()', TL_ERROR);
                $this->redirect('contao/main.php?act=error');
            }

            $objDBModel->setProperty('pid', $this->Input->get('pid'));
        }
        else if ($arrDCA['list']['sorting']['mode'] == 5 && $this->Input->get('mode') != '')
        {
            // check if the pid id/word is set
            if ($this->Input->get('pid') == '')
            {
                $this->log('Missing pid for new entry in ' . $this->getDC()->getTable(), 'DC_General - Controller - create()', TL_ERROR);
                $this->redirect('contao/main.php?act=error');
            }

            switch ($this->Input->get('mode'))
            {
                case 1:
                    $this->setParent($objDBModel, $this->getParent('self', $objCurrentModel, $this->Input->get('pid')), 'self');
                    break;

                case 2:
                    if (($this->Input->get('pid') == 0) || $this->isRootEntry('self', $this->Input->get('pid')))
                    {
                        $this->setRoot($objDBModel, 'self');
                    }
                    else
                    {
                        $objParentConfig = $this->getDC()->getDataProvider()->getEmptyConfig();
                        $objParentConfig->setId($this->Input->get('pid'));

                        $objParentModel = $this->getDC()->getDataProvider()->fetch($objParentConfig);

                        $this->setParent($objDBModel, $objParentModel, 'self');
                    }
                    break;

                default:
                    $this->log('Unknown create mode for new entry in ' . $this->getDC()->getTable(), 'DC_General - Controller - create()', TL_ERROR);
                    $this->redirect('contao/main.php?act=error');
                    break;
            }

            // Reste clipboard
            $this->resetClipboard();
        }

        // Check if we have a auto submit
        $this->getDC()->updateModelFromPOST();

        // Check submit
        if ($this->getDC()->isSubmitted() == true)
        {
            if (isset($_POST["save"]))
            {
                // process input and update changed properties.
                if (($objModell = $this->doSave($this->getDC())) !== false)
                {
                    // Callback
                    $this->getDC()->getCallbackClass()->oncreateCallback($objDBModel->getID(), $objDBModel->getPropertiesAsArray());
                    // Log
                    $this->log('A new entry in table "' . $this->getDC()->getTable() . '" has been created (ID: ' . $objModell->getID() . ')', 'DC_General - Controller - create()', TL_GENERAL);
                    // Redirect
                    $this->redirect($this->addToUrl("id=" . $objDBModel->getID() . "&amp;act=edit"));
                }
            }
            else if (isset($_POST["saveNclose"]))
            {
                // process input and update changed properties.
                if (($objModell = $this->doSave($this->getDC())) !== false)
                {
                    setcookie('BE_PAGE_OFFSET', 0, 0, '/');

                    $_SESSION['TL_INFO']    = '';
                    $_SESSION['TL_ERROR']   = '';
                    $_SESSION['TL_CONFIRM'] = '';

                    // Callback
                    $this->getDC()->getCallbackClass()->oncreateCallback($objDBModel->getID(), $objDBModel->getPropertiesAsArray());
                    // Log
                    $this->log('A new entry in table "' . $this->getDC()->getTable() . '" has been created (ID: ' . $objModell->getID() . ')', 'DC_General - Controller - create()', TL_GENERAL);
                    // Redirect
                    $this->redirect($this->getReferer());
                }
            }
        }
    }

    public function delete()
    {
        // Load current values
        $this->loadCurrentDataProvider();

        // Init some vars
        $intRecordID = $this->getDC()->getId();

        // Check if we have a id
        if (strlen($intRecordID) == 0)
        {
            $this->reload();
        }

        $arrDCA = $this->getDC()->getDCA();
        // Check if is it allowed to delete a record
        if ($arrDCA['config']['notDeletable'])
        {
            $this->log('Table "' . $this->getDC()->getTable() . '" is not deletable', 'DC_General - Controller - delete()', TL_ERROR);
            $this->redirect('contao/main.php?act=error');
        }

        // Callback
        $this->getDC()->setCurrentModel($this->objDataProvider->fetch($this->objDataProvider->getEmptyConfig()->setId($intRecordID)));
        $this->getDC()->getCallbackClass()->ondeleteCallback();

        $arrDelIDs = array();

        // Delete record
        switch ($arrDCA['list']['sorting']['mode'])
        {
            case 1:
            case 2:
            case 3:
            case 4:
                $arrDelIDs = array();
                $arrDelIDs[] = $intRecordID;
                break;

            case 5:
                $arrJoinCondition = $this->getDC()->getJoinConditions('self');

                $arrDelIDs = array();
                $arrDelIDs[] = $intRecordID;

                // Get all child entries
                for ($i = 0; $i < count($arrDelIDs); $i++)
                {
                    // Get the current model
                    $objTempModel = $this->getDC()->getDataProvider()->fetch($this->getDC()->getDataProvider()->getEmptyConfig()->setId($arrDelIDs[$i]));

                    // Build filter
                    $strFilter      = $arrJoinCondition[0]['dstField'] . $arrJoinCondition[0]['operation'] . $objTempModel->getProperty($arrJoinCondition[0]['srcField']);
                    $objChildConfig = $this->getDC()->getDataProvider()->getEmptyConfig();
                    $objChildConfig->setFilter(array($strFilter));

                    // Get child collection
                    $objChildCollection = $this->getDC()->getDataProvider()->fetchAll($objChildConfig);

                    foreach ($objChildCollection as $value)
                    {
                        $arrDelIDs[] = $value->getID();
                    }
                }
                break;
        }

        // Delete all entries
        foreach ($arrDelIDs as $value)
        {
            $this->getDC()->getDataProvider()->delete($value);

            // Add a log entry unless we are deleting from tl_log itself
            if ($this->getDC()->getTable() != 'tl_log')
            {
                $this->log('DELETE FROM ' . $this->getDC()->getTable() . ' WHERE id=' . $value, 'DC_General - Controller - delete()', TL_GENERAL);
            }
        }

        $this->redirect($this->getReferer());
    }

    public function edit()
    {
        // Load some vars
        $this->loadCurrentDataProvider();

        // Check
        $this->checkIsWritable();
        $this->checkLanguage($this->getDC());

        // Load an older Version
        if (strlen($this->Input->post("version")) != 0 && $this->getDC()->isVersionSubmit())
        {
            $this->loadVersion($this->getDC()->getId(), $this->Input->post("version"));
        }

        // Load fields and co
        $this->getDC()->loadEditableFields();
        $this->getDC()->setWidgetID($this->getDC()->getId());

        // Check if we have fields
        if (!$this->getDC()->hasEditableFields())
        {
            $this->redirect($this->getReferer());
        }

        // Load something
        $this->getDC()->preloadTinyMce();

        // Set buttons
        $this->getDC()->addButton("save");
        $this->getDC()->addButton("saveNclose");

        // Load record from data provider
        $objDBModel = $this->objDataProvider->fetch($this->objDataProvider->getEmptyConfig()->setId($this->getDC()->getId()));
        if ($objDBModel == null)
        {
            $objDBModel = $this->objDataProvider->getEmptyModel();
        }

        $this->getDC()->setCurrentModel($objDBModel);

        // Check if we have a auto submit
        $this->getDC()->updateModelFromPOST();

        // Check submit
        if ($this->getDC()->isSubmitted() == true)
        {
            if (isset($_POST["save"]))
            {
                // process input and update changed properties.
                if ($this->doSave($this->getDC()) !== false)
                {
                    $this->reload();
                }
            }
            else if (isset($_POST["saveNclose"]))
            {
                // process input and update changed properties.
                if ($this->doSave($this->getDC()) !== false)
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

    /**
     * Show informations about one entry
     */
    public function show()
    {
        // Load check multi language
        $this->loadCurrentDataProvider();

        // Check
        $this->checkLanguage($this->getDC());

        // Load record from data provider
        $objDBModel = $this->objDataProvider->fetch($this->objDataProvider->getEmptyConfig()->setId($this->getDC()->getId()));

        if ($objDBModel == null)
        {
            $this->log('Could not find ID ' . $this->getDC()->getId() . ' in Table ' . $this->getDC()->getTable() . '.', 'DC_General show()', TL_ERROR);
            $this->redirect('contao/main.php?act=error');
        }

        $this->getDC()->setCurrentModel($objDBModel);
    }

    /**
     * Show all entries from a table
     *
     * @return void | String if error
     */
    public function showAll()
    {
        // Check clipboard
        $this->checkClipboard();

        $this->getDC()->setButtonId('tl_buttons');

        $this->getFilter();

        $arrDCA = $this->getDC()->getDCA();

        $this->filterMenu('set');

        // Switch mode
        switch ($arrDCA['list']['sorting']['mode'])
        {
            case 1:
            case 2:
            case 3:
                $this->listView();
                break;

            case 4:
                $this->parentView();
                break;

            case 5:
                $this->treeViewM5();
                break;

            default:
                return $this->notImplMsg;
                break;
        }
        // keep panel after real view compilation, as in there the limits etc will get compiled.
        $this->panel($this->getDC());
    }

    /* /////////////////////////////////////////////////////////////////////////
     * -------------------------------------------------------------------------
     * AJAX
     * -------------------------------------------------------------------------
     * ////////////////////////////////////////////////////////////////////// */

    public function ajaxTreeView($intID, $intLevel)
    {
        // Load current informations
        $this->loadCurrentDataProvider();

        $strToggleID = $this->getDC()->getTable() . '_tree';

        $arrToggle = $this->Session->get($strToggleID);
        if (!is_array($arrToggle))
        {
            $arrToggle = array();
        }

        $arrToggle[$intID] = 1;

        $this->Session->set($strToggleID, $arrToggle);

        // Init some vars
        $objTableTreeData = $this->objDataProvider->getEmptyCollection();
        $objRootConfig    = $this->objDataProvider->getEmptyConfig();
        $objRootConfig->setId($intID);

        $objModel = $this->objDataProvider->fetch($objRootConfig);

        $this->treeWalkModel($objModel, $intLevel, $arrToggle, array('self'));

        foreach ($objModel->getMeta(DCGE::TREE_VIEW_CHILD_COLLECTION) as $objCollection)
        {
            foreach ($objCollection as $objSubModel)
            {
                $objTableTreeData->add($objSubModel);
            }
        }

        $this->getDC()->setCurrentCollecion($objTableTreeData);
    }

    /**
     * Loads the current model from the data provider and overrides the selector
     *
     * @param type $strSelector the name of the checkbox toggling the palette.
     */
    public function generateAjaxPalette($strSelector)
    {
        // Load some vars
        $this->loadCurrentDataProvider();

        // Check
        $this->checkIsWritable();
        $this->checkLanguage($this->getDC());

        // Load fields and co
        $this->getDC()->loadEditableFields();
        $this->getDC()->setWidgetID($this->getDC()->getId());

        // Check if we have fields
        if (!$this->getDC()->hasEditableFields())
        {
            $this->redirect($this->getReferer());
        }

        // Load something
        $this->getDC()->preloadTinyMce();

        $objDataProvider = $this->getDC()->getDataProvider();

        // Load record from data provider
        $objDBModel = $objDataProvider->fetch($objDataProvider->getEmptyConfig()->setId($this->getDC()->getId()));
        if ($objDBModel == null)
        {
            $objDBModel = $objDataProvider->getEmptyModel();
        }

        $this->getDC()->setCurrentModel($objDBModel);

        // override the setting from POST now.
        $objDBModel->setProperty($strSelector, intval($this->Input->post('state')));
    }

    /* /////////////////////////////////////////////////////////////////////////
     * -------------------------------------------------------------------------
     * Edit modes
     * -------------------------------------------------------------------------
     * ////////////////////////////////////////////////////////////////////// */

    /**
     * Load an older version
     */
    protected function loadVersion($intID, $mixVersion)
    {
        // Load record from version
        $objVersionModel = $this->objDataProvider->getVersion($intID, $mixVersion);

        // Redirect if there is no record with the given ID
        if ($objVersionModel == null)
        {
            $this->log('Could not load record ID ' . $intID . ' of table "' . $this->getDC()->getTable() . '"', 'DC_General - Controller - edit()', TL_ERROR);
            $this->redirect('contao/main.php?act=error');
        }

        $this->objDataProvider->save($objVersionModel);
        $this->objDataProvider->setVersionActive($intID, $mixVersion);

        // Callback onrestoreCallback
        $arrData       = $objVersionModel->getPropertiesAsArray();
        $arrData["id"] = $objVersionModel->getID();

        $this->getDC()->getCallbackClass()->onrestoreCallback($intID, $this->getDC()->getTable(), $arrData, $mixVersion);

        $this->log(sprintf('Version %s of record ID %s (table %s) has been restored', $this->Input->post('version'), $this->getDC()->getId(), $this->getDC()->getTable()), 'DC_General - Controller - edit()', TL_GENERAL);

        // Reload page with new recored
        $this->reload();
    }

    /**
     * Perform low level saving of the current model in a DC.
     * NOTE: the model will get populated with the new values within this function.
     * Therefore the current submitted data will be stored within the model but only on
     * success also be saved into the DB.
     *
     * @return bool|InterfaceGeneralModel Model if the save operation was successful, false otherwise.
     */
    protected function doSave()
    {
        $objDBModel = $this->getDC()->getCurrentModel();
        $arrDCA     = $this->getDC()->getDCA();

        // Check if table is closed
        if ($arrDCA['config']['closed'] && !($objDBModel->getID()))
        {
            // TODO show alarm message
            $this->redirect($this->getReferer());
        }

        // process input and update changed properties.
        foreach (array_keys($this->getDC()->getFieldList()) as $key)
        {
            $varNewValue = $this->getDC()->processInput($key);

            if (!is_null($varNewValue) && ($objDBModel->getProperty($key) != $varNewValue))
            {
                $objDBModel->setProperty($key, $varNewValue);
            }
        }

        // if we may not store the value, we keep the changes
        // in the current model and return (DO NOT SAVE!).
        if ($this->getDC()->isNoReload() == true)
        {
            return false;
        }

        // Callback
        $this->getDC()->getCallbackClass()->onsubmitCallback();

        // Refresh timestamp
        if ($this->getDC()->getDataProvider()->fieldExists("tstamp") == true)
        {
            $objDBModel->setProperty("tstamp", time());
        }

//        $this->getNewPosition($objDBModel, 'create', null, false);
        // everything went ok, now save the new record
        $this->getDC()->getDataProvider()->save($objDBModel);

        // Check if versioning is enabled
        if (isset($arrDCA['config']['enableVersioning']) && $arrDCA['config']['enableVersioning'] == true)
        {
            // Compare version and current record
            $mixCurrentVersion = $this->getDC()->getDataProvider()->getActiveVersion($objDBModel->getID());
            if ($mixCurrentVersion != null)
            {
                $mixCurrentVersion = $this->getDC()->getDataProvider()->getVersion($objDBModel->getID(), $mixCurrentVersion);

                if ($this->getDC()->getDataProvider()->sameModels($objDBModel, $mixCurrentVersion) == false)
                {
                    // TODO: FE|BE switch
                    $this->import('BackendUser', 'User');
                    $this->getDC()->getDataProvider()->saveVersion($objDBModel, $this->User->username);
                }
            }
            else
            {
                // TODO: FE|BE switch
                $this->import('BackendUser', 'User');
                $this->getDC()->getDataProvider()->saveVersion($objDBModel, $this->User->username);
            }
        }

        // Return the current model
        return $objDBModel;
    }

    /**
     * Calculate the new position of an element
     *
     * Warning this function needs the cdp (current data provider).
     * Warning this function needs the pdp (parent data provider).
     *
     * Warning nekos could live here.
     *
     * @param InterfaceGeneralData $objCDP - Current data provider
     * @param InterfaceGeneralData $objPDP - Parent data provider
     * @param InterfaceGeneralModel $objDBModel - Model of element which should moved
     * @param mixed $mixAfter - Target element
     * @param string $strMode - Mode like cut | create and so on
     * @param integer $intInsertMode - Insert Mode => 1 After | 2 Into
     * @param mixed $mixParentID - Parent ID of table or element
     *
     * @return void
     */
    protected function getNewPosition($objCDP, $objPDP, $objDBModel, $mixAfter, $strMode, $intInsertMode, $mixParentID = null)
    {
        $blnSortingExists = $objCDP->fieldExists('sorting');
        $intHigestSorting = 128;
        $intLowestSorting = 128;
        $intNextSorting   = 0;

        // Check if we have a sorting field, if not skip here
        if (!$blnSortingExists)
        {
            return;
        }

        // Funktion for create
        if ($strMode == 'create')
        {
//            // Default - Add to end off all
//            // Search for the highest sorting
//            $objConfig = $objCDP->getEmptyConfig();
//            $objConfig->setFields(array('sorting'));
//            $arrCollection = $objCDP->fetchAll($objConfig);
//
//            foreach ($arrCollection as $value)
//            {
//                if ($value->getProperty('sorting') > $intHigestSorting)
//                {
//                    $intHigestSorting = $value->getProperty('sorting');
//                }
//            }
//
//            $intNextSorting = $intHigestSorting + 128;
//
//            // Set new Sorting
//            $objDBModel->setProperty('sorting', $intNextSorting);
//
//            return;
        }
        // Funktion for cut
        else if ($strMode == 'cut' && $intInsertMode != false)
        {
            switch ($intInsertMode)
            {
                case 1:
                    // Get all elements
                    $objConfig = $this->objDataProvider->getEmptyConfig();
                    $objConfig->setFields(array('sorting'));
                    $objConfig->setSorting(array('sorting'));
                    $objConfig->setFilter($this->getFilter());
                    $arrCollection = $this->objDataProvider->fetchAll($objConfig);

                    $intSortingAfter = 0;
                    $intSortingNext  = 0;

                    foreach ($arrCollection as $value)
                    {
                        // After we have it, get the next sorting and break out
                        if ($intSortingAfter != 0)
                        {
                            $intSortingNext = $value->getProperty('sorting');
                            break;
                        }

                        // Search for my targeting element
                        if ($value->getID() == $mixAfter)
                        {
                            $intSortingAfter = $value->getProperty('sorting');
                        }
                    }

                    $intNewSorting = $intSortingAfter + round(($intSortingNext - $intSortingAfter) / 2);

                    if ($intNewSorting <= $intSortingAfter || $intNewSorting >= $intSortingNext)
                    {
                        $objConfig = $this->objDataProvider->getEmptyConfig();
                        $objConfig->setFilter($this->getFilter());
                        $this->reorderSorting($objConfig);
                        $this->getNewPosition($objCDP, $objPDP, $objDBModel, $mixAfter, $strMode, $intInsertMode, $mixParentID);
                        return;
                    }

                    $objDBModel->setProperty('sorting', ($intSortingNext - 2));
                    return;

                case 2:
                    // Search for the lowest sorting
                    $objConfig = $this->objDataProvider->getEmptyConfig();
                    $objConfig->setFields(array('sorting'));
                    $arrCollection = $this->objDataProvider->fetchAll($objConfig);

                    foreach ($arrCollection as $value)
                    {
                        if ($value->getProperty('sorting') < $intLowestSorting && $value->getProperty('sorting') != 0)
                        {
                            $intLowestSorting = $value->getProperty('sorting');
                        }
                    }

                    // If we have no room, reorder all sortings and call the function again
                    if ($intLowestSorting <= 1)
                    {
                        $objConfig = $this->objDataProvider->getEmptyConfig();
                        $objConfig->setFilter($this->getFilter());
                        $this->reorderSorting($objConfig);
                        $this->getNewPosition($objCDP, $objPDP, $objDBModel, $mixAfter, $strMode, $intInsertMode, $mixParentID);
                        return;
                    }

                    $intNextSorting = round($intLowestSorting - 2);

                    // Set new Sorting
                    $objDBModel->setProperty('sorting', $intNextSorting);

                    return;
            }
        }
    }

    protected function reorderSorting($objConfig)
    {
        if ($objConfig == null)
        {
            $objConfig = $this->objDataProvider->getEmptyConfig();
        }

        // Search for the lowest sorting
        $objConfig->setFields(array('sorting'));
        $objConfig->setSorting(array('sorting', 'id'));
        $arrCollection = $this->objDataProvider->fetchAll($objConfig);

        $i        = 1;
        $intCount = 128;

        foreach ($arrCollection as $value)
        {
            $value->setProperty('sorting', $intCount * $i++);
            $this->objDataProvider->save($value);
        }
    }

    /* /////////////////////////////////////////////////////////////////////////
     * -------------------------------------------------------------------------
     * Copy modes
     * -------------------------------------------------------------------------
     * ////////////////////////////////////////////////////////////////////// */

    /**
     * @todo Make it fine
     *
     * @param type $intSrcID
     * @param type $intDstID
     * @param type $intMode
     * @param type $blnChilds
     * @param type $strDstField
     * @param type $strSrcField
     * @param type $strOperation
     */
    protected function insertCopyModel($intIdSource, $intIdTarget, $intMode, $blnChilds, $strFieldId, $strFieldPid, $strOperation)
    {
        // Get dataprovider
        $objDataProvider = $this->getDC()->getDataProvider();

        // Load the source model
        $objSrcModel = $objDataProvider->fetch($objDataProvider->getEmptyConfig()->setId($intIdSource));

        // Create a empty model for the copy
        $objCopyModel = $objDataProvider->getEmptyModel();

        // Load all params
        $arrProperties = $objSrcModel->getPropertiesAsArray();

        $arrDCA = $this->getDC()->getDCA();
        // Clear some fields, see dca
        foreach ($arrProperties as $key => $value)
        {
            // If the field is not known, remove it
            if (!key_exists($key, $arrDCA['fields']))
            {
                continue;
            }

            // Check doNotCopy
            if ($arrDCA['fields'][$key]['eval']['doNotCopy'] == true)
            {
                unset($arrProperties[$key]);
                continue;
            }

            // Check fallback
            if ($arrDCA['fields'][$key]['eval']['fallback'] == true)
            {
                $objDataProvider->resetFallback($key);
            }

            // Check unique
            if ($arrDCA['fields'][$key]['eval']['unique'] == true && $objDataProvider->isUniqueValue($key, $value))
            {
                throw new Exception(vsprintf($GLOBALS['TL_LANG']['ERR']['unique'], $key));
            }
        }

        // Add the properties to the empty model
        $objCopyModel->setPropertiesAsArray($arrProperties);

        // Check mode insert into/after
        switch ($intMode)
        {
            //Insert After => Get the parent from he target id
            case 1:
                $this->setParent($objCopyModel, $this->getParent('self', null, $intIdTarget), 'self');
                break;

            // Insert Into => use the pid
            case 2:
                if ($this->isRootEntry('self', $intIdTarget))
                {
                    $this->setRoot($objCopyModel, 'self');
                }
                else
                {
                    $objParentConfig = $this->getDC()->getDataProvider()->getEmptyConfig();
                    $objParentConfig->setId($intIdTarget);

                    $objParentModel = $this->getDC()->getDataProvider()->fetch($objParentConfig);

                    $this->setParent($objCopyModel, $objParentModel, 'self');
                }
                break;

            default:
                $this->log('Unknown create mode for copy in ' . $this->getDC()->getTable(), 'DC_General - Controller - copy()', TL_ERROR);
                $this->redirect('contao/main.php?act=error');
                break;
        }

        $objDataProvider->save($objCopyModel);

        $this->arrInsertIDs[$objCopyModel->getID()] = true;

        if ($blnChilds == true)
        {
            $strFilter      = $strFieldPid . $strOperation . $objSrcModel->getProperty($strFieldId);
            $objChildConfig = $objDataProvider->getEmptyConfig()->setFilter(array($strFilter));
            $objChildCollection = $objDataProvider->fetchAll($objChildConfig);

            foreach ($objChildCollection as $key => $value)
            {
                if (key_exists($value->getID(), $this->arrInsertIDs))
                {
                    continue;
                }

                $this->insertCopyModel($value->getID(), $objCopyModel->getID(), 2, $blnChilds, $strFieldId, $strFieldPid, $strOperation);
            }
        }
    }

    /* /////////////////////////////////////////////////////////////////////////
     * -------------------------------------------------------------------------
     * showAll Modis
     * -------------------------------------------------------------------------
     * ////////////////////////////////////////////////////////////////////// */

    protected function treeViewM5()
    {
        $arrDCA          = $this->getDC()->getDCA();
        // Load some infromations from DCA
        $arrNeededFields = $arrDCA['list']['label']['fields'];
        $arrLablesFields = $arrDCA['list']['label']['fields'];
        $arrTitlePattern = $arrDCA['list']['label']['format'];
        $arrRootEntries  = $this->getDC()->getRootConditions('self');

        // TODO: @CS we need this to be srctable_dsttable_tree for interoperability, for mode5 this will be self_self_tree but with strTable.
        $strToggleID = $this->getDC()->getTable() . '_tree';

        $arrToggle = $this->Session->get($strToggleID);
        if (!is_array($arrToggle))
        {
            $arrToggle = array();
        }

        // Check if the open/close all is active
        if ($this->blnShowAllEntries == true)
        {
            if (key_exists('all', $arrToggle))
            {
                $arrToggle = array();
            }
            else
            {
                $arrToggle = array();
                $arrToggle['all'] = 1;
            }

            // Save in session and redirect
            $this->Session->set($strToggleID, $arrToggle);
            $this->redirectHome();
        }

        // Init some vars
        $objTableTreeData = $this->getDC()->getDataProvider()->getEmptyCollection();
        $objRootConfig    = $this->getDC()->getDataProvider()->getEmptyConfig();
        /*
          $arrChildFilterPattern = array();

          // Build a filter array for the join conditions
          foreach ($arrChildFilter as $key => $value)
          {
          if ($value['dstField'] != '')
          {
          $arrNeededFields[]                      = trim($value['srcField']);
          $arrChildFilterPattern[$key]['field']   = $value['srcField'];
          $arrChildFilterPattern[$key]['pattern'] = $value['dstField'] . ' ' . $value['operation'] . ' %s';
          }
          else
          {
          $arrChildFilterPattern[$key]['pattern'] = $value['srcField'] . ' ' . $value['operation'];
          }
          }
         */

        // TODO: @CS rebuild to new layout of filters here.
        // Set fields limit
        $objRootConfig->setFields(array_keys(array_flip($arrNeededFields)));

        // Set Filter for root elements
        $objRootConfig->setFilter($arrRootEntries);

        // Fetch all root elements
        $objRootCollection = $this->getDC()->getDataProvider()->fetchAll($objRootConfig);

        foreach ($objRootCollection as $objRootModel)
        {
            $objTableTreeData->add($objRootModel);
            $this->treeWalkModel($objRootModel, 0, $arrToggle, array('self'));
        }
        $this->getDC()->setCurrentCollecion($objTableTreeData);
    }

    protected function calcLabelFields($strTable)
    {
        $arrDCA = $this->getDC()->getDCA();
        if ($strTable == $this->getDC()->getTable())
        {
            // easy, take from DCA.
            return $arrDCA['list']['label']['fields'];
        }

        $arrChildDef = $arrDCA['dca_config']['child_list'];
        if (is_array($arrChildDef) && array_key_exists($strTable, $arrChildDef) && isset($arrChildDef[$strTable]['fields']))
        {
            // check if defined in child conditions.
            return $arrChildDef[$strTable]['fields'];
        }
        else if (($strTable == 'self') && is_array($arrChildDef) && array_key_exists('self', $arrChildDef) && $arrChildDef['self']['fields'])
        {
            return $arrChildDef['self']['fields'];
        }
    }

    protected function calcLabelPattern($strTable)
    {
        $arrDCA = $this->getDC()->getDCA();
        if ($strTable == $this->getDC()->getTable())
        {
            // easy, take from DCA.
            return $arrDCA['list']['label']['format'];
        }

        $arrChildDef = $arrDCA['dca_config']['child_list'];
        if (is_array($arrChildDef) && array_key_exists($strTable, $arrChildDef) && isset($arrChildDef[$strTable]['format']))
        {
            // check if defined in child conditions.
            return $arrChildDef[$strTable]['format'];
        }
        else if (($strTable == 'self') && array_key_exists('self', $arrChildDef) && $arrChildDef['self']['format'])
        {
            return $arrChildDef['self']['format'];
        }
    }

    protected function calcNeededFields(InterfaceGeneralModel $objModel, $strDstTable)
    {
        $arrFields    = $this->calcLabelFields($strDstTable);
        $arrChildCond = $this->getDC()->getChildCondition($objModel, $strDstTable);
        foreach ($arrChildCond as $arrCond)
        {
            if ($arrCond['property'])
            {
                $arrFields[] = $arrCond['property'];
            }
        }
        return $arrFields;
    }

    protected function buildLabel(InterfaceGeneralModel $objModel)
    {
        // Build full lable
        $arrFields = array();
        foreach ($this->calcLabelFields($objModel->getProviderName()) as $strField)
        {
            $arrFields[] = $objModel->getProperty($strField);
        }
        $objModel->setMeta(DCGE::TREE_VIEW_TITLE, vsprintf($this->calcLabelPattern($objModel->getProviderName()), $arrFields));

        // Callback - let it override the just generated label
        $strLabel = $this->getDC()->getCallbackClass()->labelCallback($objModel, $objModel->getMeta(DCGE::TREE_VIEW_TITLE), $arrFields);
        if ($strLabel != '')
        {
            $objModel->setMeta(DCGE::TREE_VIEW_TITLE, $strLabel);
        }
    }

    /**
     * This "renders" a model for tree view.
     *
     * @param InterfaceGeneralModel $objModel     the model to render.
     *
     * @param int                   $intLevel     the current level in the tree hierarchy.
     *
     * @param array                 $arrToggle    the array that determines the current toggle states for the table of the given model.
     *
     * @param array                 $arrSubTables the tables that shall be rendered "below" this item.
     *
     */
    protected function treeWalkModel(InterfaceGeneralModel $objModel, $intLevel, $arrToggle, $arrSubTables = array())
    {
        $objModel->setMeta(DCGE::TREE_VIEW_LEVEL, $intLevel);

        $this->buildLabel($objModel);

        if ($arrToggle['all'] == 1 && !(key_exists($objModel->getID(), $arrToggle) && $arrToggle[$objModel->getID()] == 0))
        {
            $objModel->setMeta(DCGE::TREE_VIEW_ISOPEN, true);
        }
        // Get toogle state
        else if (key_exists($objModel->getID(), $arrToggle) && $arrToggle[$objModel->getID()] == 1)
        {
            $objModel->setMeta(DCGE::TREE_VIEW_IS_OPEN, true);
        }
        else
        {
            $objModel->setMeta(DCGE::TREE_VIEW_IS_OPEN, false);
        }

        $arrChildCollections = array();
        foreach ($arrSubTables as $strSubTable)
        {
            // evaluate the child filter for this item.
            $arrChildFilter = $this->getDC()->getChildCondition($objModel, $strSubTable);

            // if we do not know how to render this table within here, continue with the next one.
            if (!$arrChildFilter)
            {
                continue;
            }

            // Create a new Config
            $objChildConfig = $this->getDC()->getDataProvider($strSubTable)->getEmptyConfig();
            $objChildConfig->setFilter($arrChildFilter);

            $objChildConfig->setFields($this->calcNeededFields($objModel, $strSubTable));

            // Fetch all children
            $objChildCollection = $this->getDC()->getDataProvider($strSubTable)->fetchAll($objChildConfig);

            if ($objChildCollection->length() > 0)
            {
                // TODO: @CS we need this to be srctable_dsttable_tree for interoperability, for mode5 this will be self_self_tree but with strTable.
                $strToggleID = $this->getDC()->getTable() . '_tree';

                $arrSubToggle = $this->Session->get($strToggleID);
                if (!is_array($arrSubToggle))
                {
                    $arrSubToggle = array();
                }

                foreach ($objChildCollection as $objChildModel)
                {
                    // TODO: determine the real subtables here.
                    $this->treeWalkModel($objChildModel, $intLevel + 1, $arrSubToggle, $arrSubTables);
                }
                $arrChildCollections[] = $objChildCollection;

                // speed up, if not open, one item is enough to break as we have some childs.
                if (!$objModel->getMeta(DCGE::TREE_VIEW_IS_OPEN))
                {
                    break;
                }
            }
        }

        // If open store children
        if ($objModel->getMeta(DCGE::TREE_VIEW_IS_OPEN) && $arrChildCollections)
        {
            $objModel->setMeta(DCGE::TREE_VIEW_CHILD_COLLECTION, $arrChildCollections);
            $objModel->setMeta(DCGE::TREE_VIEW_HAS_CHILDS, true);
        }
        else
        {
            $objModel->setMeta(DCGE::TREE_VIEW_HAS_CHILDS, false);
        }
        $objModel->setMeta(DCGE::TREE_VIEW_HAS_CHILDS, count($arrChildCollections));
    }

    protected function listView()
    {
        $objDataProvider = $this->getDC()->getDataProvider();
        $arrCurrentDCA   = $this->getDC()->getDCA();

        // Get limits
        $arrLimit = $this->getLimit();

        // Load record from data provider
        $objConfig = $this->getDC()->getDataProvider()->getEmptyConfig()
//                ->setIdOnly(true)
                ->setStart($arrLimit[0])
                ->setAmount($arrLimit[1])
                ->setFilter($this->getFilter())
                ->setSorting($this->getListViewSorting());

        $objCollection = $objDataProvider->fetchAll($objConfig);

        // Rename each pid to its label and resort the result (sort by parent table)
        if ($arrCurrentDCA['list']['sorting']['mode'] == 3)
        {
            $this->getDC()->setFirstSorting('pid');
            $showFields = $arrCurrentDCA['list']['label']['fields'];

            foreach ($objCollection as $objModel)
            {
                $objFieldModel = $this->getDC()->getDataProvider('parent')->fetch($this->getDC()->getDataProvider()->getEmptyConfig()->setId($objModel->getID()));
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
                if ($arrCurrentDCA['fields'][$v]['eval']['encrypt'])
                {
                    $objModelRow->setProperty($v, deserialize($objModelRow->getProperty($v)));

                    $this->import('Encryption');
                    $objModelRow->setProperty($v, $this->Encryption->decrypt($objModelRow->getProperty($v)));
                }

                if (strpos($v, ':') !== false)
                {
                    list($strKey, $strTable) = explode(':', $v);
                    list($strTable, $strField) = explode('.', $strTable);


                    $objModel = $this->getDC()->getDataProvider($strTable)->fetch(
                            $this->getDC()->getDataProvider()->getEmptyConfig()
                                    ->setId($row[$strKey])
                                    ->setFields(array($strField))
                    );

                    $objModelRow->setMeta(DCGE::MODEL_LABEL_ARGS, (($objModel->hasProperties()) ? $objModel->getProperty($strField) : ''));
                }
            }
        }

        $this->getDC()->setCurrentCollecion($objCollection);
    }

    /**
     * Show header of the parent table and list all records of the current table
     * @return string
     */
    protected function parentView()
    {
        if (!CURRENT_ID)
        {
            throw new Exception("mode 4 need a proper parent id defined, somehow none is defined?", 1);
        }

        if (!($objParentDP = $this->getDC()->getDataProvider('parent')))
        {
            throw new Exception("mode 4 need a proper parent dataprovide defined, somehow none is defined?", 1);
        }

        $objParentItem = $this->objDC->getCurrentParentCollection()->get(0);

        // Get limits
        $arrLimit = $this->getLimit();

        // Load record from data provider
        $objConfig = $this->getDC()->getDataProvider()->getEmptyConfig()
                ->setStart($arrLimit[0])
                ->setAmount($arrLimit[1])
                ->setFilter($this->getFilter())
                ->setSorting($this->getParentViewSorting());

        if ($this->foreignKey)
        {
            $objConfig->setFields($this->arrFields);
        }

        $this->getDC()->setCurrentCollecion($this->getDC()->getDataProvider()->fetchAll($objConfig));
    }

    /* /////////////////////////////////////////////////////////////////////////
     * -------------------------------------------------------------------------
     * Panels
     * -------------------------------------------------------------------------
     * ////////////////////////////////////////////////////////////////////// */

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

        $arrDCA = $this->getDC()->getDCA();
        if (!strlen($arrDCA['list']['sorting']['panelLayout']) || !is_array($filter) && !is_array($search) && !is_array($limit) && !is_array($sort))
        {
            return;
        }

        if ($this->Input->post('FORM_SUBMIT') == 'tl_filters')
        {
            $this->reload();
        }

        $panelLayout = $arrDCA['list']['sorting']['panelLayout'];
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
            $this->getDC()->setPanelView(array_values($arrPanelView));
        }
    }

    /**
     * Generate the filter panel and return it as HTML string
     * @return string
     */
    protected function filterMenu($type = 'add')
    {
        $arrDCA           = $this->getDC()->getDCA();
        $this->getDC()->setButtonId('tl_buttons_a');
        $arrSortingFields = array();
        $arrSession = $this->Session->getData();
        $strFilter  = ($arrDCA['list']['sorting']['mode'] == 4) ? $this->getDC()->getTable() . '_' . CURRENT_ID : $this->getDC()->getTable();

        $this->arrDCA = $arrDCA;

        // Get sorting fields
        foreach ($arrDCA['fields'] as $k => $v)
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
        if ($type == 'set')
        {
            $this->filterMenuSetFilter($arrSortingFields, $arrSession, $strFilter);
            return;
        }

        // Add options
        if ($type == 'add')
        {
            $arrPanelView = $this->filterMenuAddOptions($arrSortingFields, $arrSession, $strFilter);
            return $arrPanelView;
        }
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
        $arrDCA = $this->getDC()->getDCA();
        foreach ($arrDCA['fields'] as $k => $v)
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
            $session['search'][$this->getDC()->getTable()]['value'] = '';
            $session['search'][$this->getDC()->getTable()]['field'] = $this->Input->post('tl_field', true);

            // Make sure the regular expression is valid
            if ($this->Input->postRaw('tl_value') != '')
            {
                try
                {
                    $objConfig = $this->getDC()->getDataProvider()->getEmptyConfig()
                            ->setAmount(1)
                            ->setFilter(array($this->Input->post('tl_field', true) . " REGEXP '" . $this->Input->postRaw('tl_value') . "'"))
                            ->setSorting($this->getListViewSorting());

                    $this->getDC()->getDataProvider()->fetchAll($objConfig);

                    $session['search'][$this->getDC()->getTable()]['value'] = $this->Input->postRaw('tl_value');
                }
                catch (Exception $e)
                {
                    // Do nothing
                }
            }

            $this->objSession->setData($session);
        }

        // Set search value from session
        else if ($session['search'][$this->getDC()->getTable()]['value'] != '')
        {
            if (substr($GLOBALS['TL_CONFIG']['dbCollation'], -3) == '_ci')
            {
                $this->getDC()->setFilter(array("LOWER(CAST(" . $session['search'][$this->getDC()->getTable()]['field'] . " AS CHAR)) REGEXP LOWER('" . $session['search'][$this->getDC()->getTable()]['value'] . "')"));
            }
            else
            {
                $this->getDC()->setFilter(array("CAST(" . $session['search'][$this->getDC()->getTable()]['field'] . " AS CHAR) REGEXP '" . $session['search'][$this->getDC()->getTable()]['value'] . "'"));
            }
        }

        $arrOptions = array();

        foreach ($searchFields as $field)
        {
            $mixedOptionsLabel = strlen($this->arrDCA['fields'][$field]['label'][0]) ? $this->arrDCA['fields'][$field]['label'][0] : $GLOBALS['TL_LANG']['MSC'][$field];

            $arrOptions[utf8_romanize($mixedOptionsLabel) . '_' . $field] = array(
                'value'   => specialchars($field),
                'select'  => (($field == $session['search'][$this->getDC()->getTable()]['field']) ? ' selected="selected"' : ''),
                'content' => $mixedOptionsLabel
            );
        }

        // Sort by option values
        uksort($arrOptions, 'strcasecmp');
        $arrPanelView['option'] = $arrOptions;

        $active = strlen($session['search'][$this->getDC()->getTable()]['value']) ? true : false;

        $arrPanelView['select'] = array(
            'class' => 'tl_select' . ($active ? ' active' : '')
        );

        $arrPanelView['input'] = array(
            'class' => 'tl_text' . (($active) ? ' active' : ''),
            'value' => specialchars($session['search'][$this->getDC()->getTable()]['value'])
        );

        return $arrPanelView;
    }

    /**
     * Return a select menu to limit results
     *
     * @param boolean
     *
     * @return string
     */
    protected function limitMenu($blnOptional = false)
    {
        $arrPanelView = array();

        $session = $this->objSession->getData();

        $arrDCA = $this->getDC()->getDCA();
        $filter = ($arrDCA['list']['sorting']['mode'] == 4) ? $this->getDC()->getTable() . '_' . CURRENT_ID : $this->getDC()->getTable();

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
                $this->getDC()->setLimit((($session['filter'][$filter]['limit'] == 'all') ? null : $session['filter'][$filter]['limit']));
            }
            else
            {
                $this->getDC()->setLimit('0,' . $GLOBALS['TL_CONFIG']['resultsPerPage']);
            }

            $intCount               = $this->getDC()->getDataProvider()->getCount($this->getDC()->getDataProvider()->getEmptyConfig()->setFilter($this->getFilter()));
            $blnIsMaxResultsPerPage = false;

            // Overall limit
            if ($intCount > $GLOBALS['TL_CONFIG']['maxResultsPerPage'] && (is_null($this->getDC()->getLimit()) || preg_replace('/^.*,/i', '', $this->getDC()->getLimit()) == $GLOBALS['TL_CONFIG']['maxResultsPerPage']))
            {
                if (is_null($this->getDC()->getLimit()))
                {
                    $this->getDC()->setLimit('0,' . $GLOBALS['TL_CONFIG']['maxResultsPerPage']);
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
                if (!is_null($this->getDC()->getLimit()) && ($this->getDC()->getLimit() == '' || preg_replace('/,.*$/i', '', $this->getDC()->getLimit()) > $intCount))
                {
                    $this->getDC()->setLimit('0,' . $GLOBALS['TL_CONFIG']['resultsPerPage']);
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
                        'select'  => $this->optionSelected($this->getDC()->getLimit(), $this_limit),
                        'content' => ($i * $GLOBALS['TL_CONFIG']['resultsPerPage'] + 1) . ' - ' . $upper_limit
                    );
                }

                if (!$blnIsMaxResultsPerPage)
                {
                    $arrPanelView['option'][] = array(
                        'value'   => 'all',
                        'select'  => $this->optionSelected($this->getDC()->getLimit(), null),
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
     *
     * @return string
     */
    protected function sortMenu()
    {
        $arrPanelView = array();

        if ($this->arrDCA['list']['sorting']['mode'] != 2 && $this->arrDCA['list']['sorting']['mode'] != 4)
        {
            return array();
        }

        $sortingFields = array();

        // Get sorting fields
        foreach ($this->arrDCA['fields'] as $k => $v)
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

        $this->getDC()->setButtonId('tl_buttons_a');
        $session      = $this->objSession->getData();
        $orderBy      = $this->arrDCA['list']['sorting']['fields'];
        $firstOrderBy = preg_replace('/\s+.*$/i', '', $orderBy[0]);

        // Add PID to order fields
        if ($this->arrDCA['list']['sorting']['mode'] == 3 && $this->getDC()->getDataProvider()->fieldExists('pid'))
        {
            array_unshift($orderBy, 'pid');
        }

        // Set sorting from user input
        if ($this->Input->post('FORM_SUBMIT') == 'tl_filters')
        {
            $session['sorting'][$this->getDC()->getTable()] = in_array($this->arrDCA['fields'][$this->Input->post('tl_sort')]['flag'], array(2, 4, 6, 8, 10, 12)) ? $this->Input->post('tl_sort') . ' DESC' : $this->Input->post('tl_sort');
            $this->objSession->setData($session);
        }

        // Overwrite the "orderBy" value with the session value
        elseif (strlen($session['sorting'][$this->getDC()->getTable()]))
        {
            $overwrite = preg_quote(preg_replace('/\s+.*$/i', '', $session['sorting'][$this->getDC()->getTable()]), '/');
            $orderBy   = array_diff($orderBy, preg_grep('/^' . $overwrite . '/i', $orderBy));

            array_unshift($orderBy, $session['sorting'][$this->getDC()->getTable()]);

            $this->getDC()->setFirstSorting($overwrite);
            $this->getDC()->setSorting($orderBy);
        }

        $arrOptions = array();

        foreach ($sortingFields as $field)
        {
            $mixedOptionsLabel = strlen($this->arrDCA['fields'][$field]['label'][0]) ? $this->arrDCA['fields'][$field]['label'][0] : $GLOBALS['TL_LANG']['MSC'][$field];

            if (is_array($mixedOptionsLabel))
            {
                $mixedOptionsLabel = $mixedOptionsLabel[0];
            }

            $arrOptions[$mixedOptionsLabel] = array(
                'value'   => specialchars($field),
                'select'  => ((!strlen($session['sorting'][$this->getDC()->getTable()]) && $field == $firstOrderBy || $field == str_replace(' DESC', '', $session['sorting'][$this->getDC()->getTable()])) ? ' selected="selected"' : ''),
                'content' => $mixedOptionsLabel
            );
        }

        // Sort by option values
        uksort($arrOptions, 'strcasecmp');
        $arrPanelView['option'] = $arrOptions;

        return $arrPanelView;
    }

    /* /////////////////////////////////////////////////////////////////////////
     * -------------------------------------------------------------------------
     * Helper DataProvider
     * -------------------------------------------------------------------------
     * ////////////////////////////////////////////////////////////////////// */

    /**
     * Check if a entry has some childs
     *
     * @param array $arrFilterPattern
     * @param InterfaceGeneralModel $objParentModel
     *
     * @return boolean True => has children | False => no children
     */
    protected function hasChildren($objParentModel, $strTable)
    {
        $arrFilter = array();

        // Build filter Settings
        foreach ($this->getDC()->getJoinConditions($objParentModel, $strTable) as $valueFilter)
        {
            if (isset($valueFilter['srcField']) && $valueFilter['srcField'] != '')
            {
                $arrFilter[] = $valueFilter['dstField'] . $valueFilter['operation'] . $objParentModel->getProperty($valueFilter['srcField']);
            }
            else
            {
                $arrFilter[] = $valueFilter['dstField'] . $valueFilter['operation'];
            }
        }

        // Create a new Config
        $objConfig = $this->getDC()->getDataProvider()->getEmptyConfig();
        $objConfig->setFilter($arrFilter);

        // Fetch all children
        if ($this->getDC()->getDataProvider()->getCount($objConfig) != 0)
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    protected function setParent(InterfaceGeneralModel $objChildEntry, InterfaceGeneralModel $objParentEntry, $strTable)
    {
        $arrChildCondition = $this->getDC()->getParentChildCondition($objParentEntry, $objChildEntry->getProviderName());
        if (!($arrChildCondition && $arrChildCondition['setOn']))
        {
            throw new Exception("Can not calculate parent.", 1);
        }

        foreach ($arrChildCondition['setOn'] as $arrCondition)
        {
            if ($arrCondition['from_field'])
            {
                $objChildEntry->setProperty($arrCondition['to_field'], $objParentEntry->getProperty($arrCondition['from_field']));
            }
            else if ($arrCondition['value'])
            {
                $objChildEntry->setProperty($arrCondition['to_field'], $arrCondition['value']);
            }
            else
            {
                throw new Exception("Error Processing child condition, neither from_field nor value specified: " . var_export($arrCondition, true), 1);
            }
        }
    }

    protected function getParent($strTable, $objCurrentModel = null, $intCurrentID = null)
    {
        // Check if something is set
        if ($objCurrentModel == null && $intCurrentID == null)
        {
            return null;
        }

        // If we have only the id load current model
        if ($objCurrentModel == null)
        {
            $objCurrentConfig = $this->getDC()->getDataProvider()->getEmptyConfig();
            $objCurrentConfig->setId($intCurrentID);

            $objCurrentModel = $this->getDC()->getDataProvider()->fetch($objCurrentConfig);
        }

        // Build child to parent
        $strFilter = $arrJoinCondition[0]['srcField'] . $arrJoinCondition[0]['operation'] . $objCurrentModel->getProperty($arrJoinCondition[0]['dstField']);

        // Load model
        $objParentConfig = $this->getDC()->getDataProvider()->getEmptyConfig();
        $objParentConfig->setFilter(array($strFilter));

        return $this->getDC()->getDataProvider()->fetch($objParentConfig);
    }

    protected function isRootEntry($strTable, $mixID)
    {
        // Get the join field
        $arrRootCondition = $this->getDC()->getRootConditions($strTable);

        switch ($arrRootCondition[0]['operation'])
        {
            case '=':
                return ($mixID == $arrRootCondition[0]['value']);

            case '<':
                return ($arrRootCondition[0]['value'] < $mixID);

            case '>':
                return ($arrRootCondition[0]['value'] > $mixID);

            case '!=':
                return ($arrRootCondition[0]['value'] != $mixID);
        }

        return false;
    }

    protected function setRoot(InterfaceGeneralModel $objCurrentEntry, $strTable)
    {
        $arrRootSetter = $this->getDC()->getRootSetter($strTable);
        if (!($arrRootSetter && $arrRootSetter))
        {
            throw new Exception("Can not calculate parent.", 1);
        }

        foreach ($arrRootSetter as $arrCondition)
        {
            if (!($arrCondition['property'] && $arrCondition['value']))
            {
                $objCurrentEntry->setProperty($arrCondition['property'], $arrCondition['value']);
            }
            else
            {
                throw new Exception("Error Processing root condition, you need to specify property and value: " . var_export($arrCondition, true), 1);
            }
        }
    }

    /* /////////////////////////////////////////////////////////////////////////
     * -------------------------------------------------------------------------
     * Helper
     * -------------------------------------------------------------------------
     * ////////////////////////////////////////////////////////////////////// */

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
                    if (in_array($this->arrDCA['fields'][$field]['flag'], array(5, 6)))
                    {
                        if ($arrSession['filter'][$strFilter][$field] == '')
                        {
                            $this->getDC()->setFilter(array(array('operation' => '=', 'property'  => $field, 'value'     => '')));
                        }
                        else
                        {
                            $objDate = new Date($arrSession['filter'][$strFilter][$field]);
                            $this->getDC()->setFilter(array(
                                array('operation' => '>', 'property'  => $field, 'value'     => $objDate->dayBegin),
                                array('operation' => '<', 'property'  => $field, 'value'     => $objDate->dayEnd)
                            ));
                        }
                    }

                    // Sort by month
                    elseif (in_array($this->arrDCA['fields'][$field]['flag'], array(7, 8)))
                    {
                        if ($arrSession['filter'][$strFilter][$field] == '')
                        {
                            $this->getDC()->setFilter(array(array('operation' => '=', 'property'  => $field, 'value'     => '')));
                        }
                        else
                        {
                            $objDate = new Date($arrSession['filter'][$strFilter][$field]);
                            $this->getDC()->setFilter(array(
                                array('operation' => '>', 'property'  => $field, 'value'     => $objDate->monthBegin),
                                array('operation' => '<', 'property'  => $field, 'value'     => $objDate->monthEnd)
                            ));
                        }
                    }

                    // Sort by year
                    elseif (in_array($this->arrDCA['fields'][$field]['flag'], array(9, 10)))
                    {
                        if ($arrSession['filter'][$strFilter][$field] == '')
                        {
                            $this->getDC()->setFilter(array(array('operation' => '=', 'property'  => $field, 'value'     => '')));
                        }
                        else
                        {
                            $objDate = new Date($arrSession['filter'][$strFilter][$field]);
                            $this->getDC()->setFilter(array(
                                array('operation' => '>', 'property'  => $field, 'value'     => $objDate->yearBegin),
                                array('operation' => '<', 'property'  => $field, 'value'     => $objDate->yearEnd)
                            ));
                        }
                    }

                    // Manual filter
                    elseif ($this->arrDCA['fields'][$field]['eval']['multiple'])
                    {
                        // TODO find in set
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
                        $this->getDC()->setFilter(
                                array(
                                    array('operation' => '=', 'property'  => $field, 'value'     => $arrSession['filter'][$strFilter][$field])
                                )
                        );
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

            if ($this->arrDCA['list']['sorting']['mode'] == 4)
            {
                $arrProcedure[] = array('operation' => '=', 'property'  => 'pid', 'value'     => CURRENT_ID);
            }

            if (!is_null($this->getDC()->getRootIds()) && is_array($this->getDC()->getRootIds()))
            {
                $arrProcedure[] = array('operation' => 'IN', 'property'  => 'id', 'values'    => array_map('intval', $this->getDC()->getRootIds()));
            }

            $objtmpCollection = $this->getDC()->getDataProvider()->fetchAll($this->getDC()->getDataProvider()->getEmptyConfig()->setFields(array($field))->setFilter($arrProcedure));

            $arrFields = array();
            $objCollection = $this->getDC()->getDataProvider()->getEmptyCollection();
            foreach ($objtmpCollection as $key => $objModel)
            {
                $value = $objModel->getProperty($field);
                if (!in_array($value, $arrFields))
                {
                    $arrFields[] = $value;
                    $objNewModel = $this->getDC()->getDataProvider()->getEmptyModel();
                    $objNewModel->setProperty($field, $value);
                    $objCollection->add($objNewModel);
                }
            }

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
                        'content' => (is_array($this->arrDCA['fields'][$field]['label']) ? $this->arrDCA['fields'][$field]['label'][0] : $this->arrDCA['fields'][$field]['label'])
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
                        'reverse' => ($this->arrDCA['fields'][$field]['flag'] == 6) ? true : false
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
                elseif (in_array($this->arrDCA['fields'][$field]['flag'], array(7, 8)))
                {
                    $this->arrColSort = array(
                        'field'   => $field,
                        'reverse' => ($this->arrDCA['fields'][$field]['flag'] == 8) ? true : false
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
                elseif (in_array($this->arrDCA['fields'][$field]['flag'], array(9, 10)))
                {
                    $this->arrColSort = array(
                        'field'   => $field,
                        'reverse' => ($this->arrDCA['fields'][$field]['flag'] == 10) ? true : false
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
                if ($this->arrDCA['fields'][$field]['eval']['multiple'])
                {
                    $moptions = array();

                    foreach ($objCollection as $objModel)
                    {
                        if (isset($this->arrDCA['fields'][$field]['eval']['csv']))
                        {
                            $doptions = trimsplit($this->arrDCA['fields'][$field]['eval']['csv'], $objModel->getProperty($field));
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
                if (is_array($this->arrDCA['fields'][$field]['options_callback']) && !$this->arrDCA['fields'][$field]['reference'])
                {
                    $arrOptionsCallback = $this->getDC()->getCallbackClass()->optionsCallback($field);

                    // Sort options according to the keys of the callback array
                    if (!is_null($arrOptionsCallback))
                    {
                        $options = array_intersect(array_keys($arrOptionsCallback), $options);
                    }
                }

                $arrOptions = array();
                $arrSortOptions = array();
                $blnDate = in_array($this->arrDCA['fields'][$field]['flag'], array(5, 6, 7, 8, 9, 10));

                // Options
                foreach ($options as $kk => $vv)
                {

                    $value = $blnDate ? $kk : $vv;

                    // Replace the ID with the foreign key
                    if (isset($this->arrDCA['fields'][$field]['foreignKey']))
                    {
                        $key = explode('.', $this->arrDCA['fields'][$field]['foreignKey'], 2);

                        $objModel = $this->getDC()->getDataProvider($key[0])->fetch(
                                $this->getDC()->getDataProvider($key[0])->getEmptyConfig()
                                        ->setId($vv)
                                        ->setFields(array($key[1] . ' AS value'))
                        );

                        if ($objModel->hasProperties())
                        {
                            $vv = $objModel->getProperty('value');
                        }
                    }

                    // Replace boolean checkbox value with "yes" and "no"
                    elseif ($this->arrDCA['fields'][$field]['eval']['isBoolean'] || ($this->arrDCA['fields'][$field]['inputType'] == 'checkbox' && !$this->arrDCA['fields'][$field]['eval']['multiple']))
                    {
                        $vv = ($vv != '') ? $GLOBALS['TL_LANG']['MSC']['yes'] : $GLOBALS['TL_LANG']['MSC']['no'];
                    }

                    // Options callback
                    elseif (is_array($arrOptionsCallback) && !empty($arrOptionsCallback))
                    {
                        $vv = $arrOptionsCallback[$vv];
                    }

                    // Get the name of the parent record
                    elseif ($field == 'pid')
                    {
                        // Load language file and data container array of the parent table
                        $this->loadLanguageFile($this->getDC()->getParentTable());
                        $this->loadDataContainer($this->getDC()->getParentTable());

                        $objParentDC  = new DC_General($this->getDC()->getParentTable());
                        $arrParentDca = $objParentDC->getDCA();

                        $showFields = $arrParentDca['list']['label']['fields'];

                        if (!$showFields[0])
                        {
                            $showFields[0] = 'id';
                        }

                        $objModel = $this->getDC()->getDataProvider('parent')->fetch(
                                $this->getDC()->getDataProvider('parent')->getEmptyConfig()
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
                    if (isset($this->arrDCA['fields'][$field]['reference']))
                    {
                        $strOptionsLabel = is_array($this->arrDCA['fields'][$field]['reference'][$vv]) ? $this->arrDCA['fields'][$field]['reference'][$vv][0] : $this->arrDCA['fields'][$field]['reference'][$vv];
                    }

                    // Associative array
                    elseif ($this->arrDCA['fields'][$field]['eval']['isAssociative'] || array_is_assoc($this->arrDCA['fields'][$field]['options']))
                    {
                        $strOptionsLabel = $this->arrDCA['fields'][$field]['options'][$vv];
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

                    if (in_array($this->arrDCA['fields'][$field]['flag'], array(2, 4, 12)))
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

    /**
     * Ajax actions that do require a data container object
     * @param DataContainer
     */
    public function executePostActions()
    {
        header('Content-Type: text/html; charset=' . $GLOBALS['TL_CONFIG']['characterSet']);

        switch ($this->Input->post('action'))
        {
            // Toggle subpalettes
            case 'toggleSubpalette':
                $this->import('BackendUser', 'User');

                $arrDCA = $this->getDC()->getDCA();

                // Check whether the field is a selector field and allowed for regular users (thanks to Fabian Mihailowitsch) (see #4427)
                if (!is_array($arrDCA['palettes']['__selector__'])
                        || !in_array($this->Input->post('field'), $arrDCA['palettes']['__selector__'])
                        || ($arrDCA['fields'][$this->Input->post('field')]['exclude']
                        && !$this->User->hasAccess($this->getDC()->getTable() . '::' . $this->Input->post('field'), 'alexf')))
                {
                    $this->log('Field "' . $this->Input->post('field') . '" is not an allowed selector field (possible SQL injection attempt)', 'DC_General executePostActions()', TL_ERROR);
                    header('HTTP/1.1 400 Bad Request');
                    die('Bad Request');
                }

                if ($this->Input->get('act') == 'editAll')
                {
                    throw new Exception("Ajax editAll unimplemented, I do not know what to do.", 1);
                    if ($this->Input->post('load'))
                    {
                        echo $this->getDC()->editAll();
                    }
                }
                else
                {
                    if ($this->Input->post('load'))
                    {
                        echo $this->getDC()->generateAjaxPalette($this->Input->post('field'));
                    }
                }
                exit;
                break;
            default:
            // do nothing, the normal workflow from Backend will kick in now.
        }
    }

}

?>
