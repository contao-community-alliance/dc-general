<?php
/**
 * PHP version 5
 * @package    generalDriver
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

namespace DcGeneral;

use DcGeneral\Clipboard\DefaultClipboard;
use DcGeneral\Contao\InputProvider;
use DcGeneral\Contao\TranslationManager;
use DcGeneral\Controller\DefaultController as DefaultController;
use DcGeneral\Controller\ControllerInterface;
use DcGeneral\Data\DCGE;
use DcGeneral\Data\DefaultDriver;
use DcGeneral\Data\CollectionInterface;
use DcGeneral\Data\DriverInterface;
use DcGeneral\Data\ModelInterface;
use DcGeneral\Exception\DcGeneralRuntimeException;
use DcGeneral\Factory\DcGeneralFactory;
use DcGeneral\Helper\WidgetAccessor;
use DcGeneral\EnvironmentInterface;
use DcGeneral\DefaultEnvironment;
use DcGeneral\Contao\View\Contao2BackendView as BackendView;
use DcGeneral\Contao\View\Contao2BackendView\ListView;
use DcGeneral\Contao\View\Contao2BackendView\ParentView;
use DcGeneral\Contao\View\Contao2BackendView\TreeView;
use DcGeneral\View\ViewInterface;

class DC_General extends \DataContainer implements DataContainerInterface
{
	/* /////////////////////////////////////////////////////////////////////////
	 * -------------------------------------------------------------------------
	 *  Vars
	 * -------------------------------------------------------------------------
	 * ////////////////////////////////////////////////////////////////////// */


	// Basic Vars ------------------

	/**
	 * @var EnvironmentInterface
	 */
	protected $objEnvironment;

	/**
	 * Id of the item currently in edit view
	 * @var int
	 */
	protected $intId = null;

	/**
	 * Name of current table
	 * @var String
	 */
	protected $strTable = null;

	/**
	 * Name of current parent table
	 * @var String
	 */
	protected $strParentTable = null;

	/**
	 * Name of the child table
	 * @var String
	 */
	protected $strChildTable = null;

	/**
	 * DCA configuration
	 * @var array
	 */
	protected $arrDCA = null;

	/**
	 * Force the edit mode.
	 * @var boolean 
	 */
	protected $blnForceEdit = false;

	// Core Objects ----------------

	/**
	 * Includes all data provider
	 * @var DriverInterface[]
	 */
	protected $arrDataProvider = array();

	/**
	 * The controller that shall be used .
	 * @var ControllerInterface
	 */
	protected $objController = null;

	/**
	 * The child DC
	 * @var DC_General
	 */
	protected $objChildDC = null;

	// Config ----------------------

	/**
	 * Flag to show if the site must not be reloaded. True => do not reload, false do as you want.
	 * @var boolean
	 */
	protected $blnNoReload = false;

	/**
	 * True if we have a widget which is uploadable
	 * @var boolean
	 */
	protected $blnUploadable = false;

	/**
	 * ID of the button container
	 * @param string
	 */
	protected $strButtonId = null;

	// View ------------------------

	/**
	 * Container for panel information
	 * @var array
	 */
	protected $arrPanelView = null;

	// Submitting ------------------

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
	 * State of languagesubmit
	 * @var boolean
	 */
	protected $blnLanguageSubmit = false;

	/**
	 * State of select submit
	 * @var boolean
	 */
	protected $blnSelectSubmit = false;

	// Debug -----------------------

	/**
	 * Timer
	 */
	protected $intTimerStart;

	/**
	 * Amount of queries issued while in DC scope.
	 */
	protected $intQueryCount;

	// Misc. -----------------------

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
	 * Order of the first sorting
	 * @var string
	 */
	protected $strFirstSortingOrder = null;

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
	 * List with all procesed widgets from submit.
	 * @var array
	 */
	protected $arrProcessedWidgets = array();

	/**
	 * The iltimate id for widgets
	 * @var type
	 */
	protected $mixWidgetID = null;

	// Const. ----------------------

	/**
	 * Lookup for special regex
	 * @var array
	 */
	private static $arrDates = array(
		'date' => true,
		'time' => true,
		'datim' => true
	);

	/* /////////////////////////////////////////////////////////////////////////
	 * -------------------------------------------------------------------------
	 *  Constructor and co.
	 * -------------------------------------------------------------------------
	 * ////////////////////////////////////////////////////////////////////// */

	public function __construct($strTable, array &$arrDCA = null, $blnOnloadCallback = true)
	{
		// Set start timer
		$this->intTimerStart = microtime(true);
		$this->intQueryCount = count($GLOBALS['TL_DEBUG']);

		// Call parent
		parent::__construct();

		// Callback
		$strTable = $this->getTablenameCallback($strTable);

		// Basic vars Init
		$this->strTable = $strTable;
		// in contao 3 the second constructor parameter is the backend module array.
		// Therefore we have to check if the passed argument is indeed a valid DCA.
		if ($arrDCA != null && $arrDCA['config'])
		{
			$this->arrDCA = $arrDCA;
		}
		else
		{
			$this->arrDCA = &$GLOBALS['TL_DCA'][$this->strTable];
		}

		$factory = new DcGeneralFactory();

		// FIXME: transporting the current instance via $GLOBALS is needed to tell the callback handler about this class.
		// We definitely want to get rid of this again when dropping all the callback handlers.
		$GLOBALS['objDcGeneral'] = $this;
		$dcGeneral = $factory
			->setContainerName($strTable)
			->createDcGeneral();
		unset($GLOBALS['objDcGeneral']);

		$this->objEnvironment = $dcGeneral->getEnvironment();


		$environment  = $this->getEnvironment();

/*
		$this->objEnvironment = new DefaultEnvironment();
		$this->getEnvironment()
			->setDataDefinition(new Contao\Dca\Container($this->strTable, $this->arrDCA))
			// TODO: make inputprovider configurable somehow - unsure how though.
			->setInputProvider(new InputProvider())
			->setClipboard(new DefaultClipboard())
			->setTranslationManager(new TranslationManager());

		$parentTable = $this->getEnvironment()->getDataDefinition()->getParentDriverName();
		if ($parentTable)
		{
			$this->loadDataContainer($parentTable);
			$this->getEnvironment()->setParentDataDefinition(new Contao\Dca\Container(
				$parentTable,
				$GLOBALS['TL_DCA'][$parentTable]
			));
		}
*/
		// Switch user for FE / BE support
		switch (TL_MODE)
		{
			case 'FE':
				$this->import('FrontendUser', 'User');
				break;

			default:
			case 'BE':
				$this->import('BackendUser', 'User');
				break;
		}

		// Load
		$this->checkPostGet();
		$this->loadProviderAndHandler();

		// Check for forcemode
		if ($this->arrDCA['config']['forceEdit'])
		{
			$this->blnForceEdit = true;
			$this->intId        = 1;
		}
		
		// SH: We need the buttons here, because the onloadCallback is (the only) one 
		// to remove buttons.
		$this->loadDefaultButtons();

		// Load the clipboard.
		$this->getEnvironment()->getClipboard()
			->loadFrom($this->getEnvironment());

		// execute AJAX request, called from Backend::getBackendModule
		// we have to do this here, as otherwise the script will exit as it only checks for DC_Table and DC_File decendant classes. :/
		// FIXME: dependency injection
		if ($_POST && \Environment::getInstance()->isAjaxRequest)
		{
			$this->getControllerHandler()->executePostActions();
		}
	}

	/**
	 * Call the tablename callback
	 *
	 * @param string $strTable
	 *
	 * @return string name of current table
	 */
	protected function getTablenameCallback($strTable)
	{
		if (array_key_exists('tablename_callback', $GLOBALS['TL_DCA'][$strTable]['config']) && is_array($GLOBALS['TL_DCA'][$strTable]['config']['tablename_callback']))
		{
			foreach ($GLOBALS['TL_DCA'][$strTable]['config']['tablename_callback'] as $callback)
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
		$this->loadDataProvider();
	}
	
	/**
	 * Load the default button. 'Save' and 'Save and close'.
	 */
	protected function loadDefaultButtons()
	{
		// Set buttons
		$this->addButton("save", array(
			'id'				 => 'save',
			'formkey'			 => 'save',
			'class'				 => '',
			'accesskey'			 => 's',
			'value'				 => null, // Lookup from DC_General
			'button_callback'	 => null  // Core feature from DC_General
		));

		$this->addButton("saveNclose", array(
			'id'				 => 'saveNclose',
			'formkey'			 => 'saveNclose',
			'class'				 => '',
			'accesskey'			 => 'c',
			'value'				 => null, // Lookup from DC_General
			'button_callback'	 => null  // Core feature from DC_General
		));
	}

	protected function bootDataDriver($strSource, $arrConfig)
	{
		if ($this->getEnvironment()->hasDataProvider($strSource))
		{
			return;
		}

		if ($arrConfig['source'])
		{
			$this->loadLanguageFile($arrConfig['source']);
			$this->loadDataContainer($arrConfig['source']);
		}

		if (array_key_exists('class', $arrConfig))
		{
			$strClass = $arrConfig['class'];
			$provider = new $strClass();
		}
		else
		{
			$provider = new DefaultDriver();
		}

		$provider->setBaseConfig($arrConfig);

		$this->getEnvironment()->addDataProvider($strSource, $provider);
	}

	/**
	 * Load the data provider,
	 * if not set try to load the default one.
	 */
	protected function loadDataProvider()
	{
		$arrSourceConfigs = $this->arrDCA['dca_config']['data_provider'];

		// Set default data provider
		if (isset($arrSourceConfigs['default']))
		{
			$this->bootDataDriver($this->strTable, $arrSourceConfigs['default']);
		}
		else
		{
			$this->bootDataDriver($this->strTable, array(
				'class' => '\DcGeneral\Data\DefaultDriver',
				'source' => $this->strTable
			));

			// DC_Table compatibility fallback, shall we remove this?
			if ($this->arrDCA['config']['ptable'])
			{
				$this->bootDataDriver($this->arrDCA['config']['ptable'], array(
					'class' => '\DcGeneral\Data\DefaultDriver',
					'source' => $this->arrDCA['config']['ptable']
				));
			}
		}

		if (isset($arrSourceConfigs['parent']))
		{
			$this->bootDataDriver($arrSourceConfigs['parent']['source'], $arrSourceConfigs['parent']);
		}
	}

	/**
	 * Check all post/get informations
	 */
	public function checkPostGet()
	{
		// TODO: dependency injection.
		$this->intId = \Input::getInstance()->get('id');

		$this->blnSubmitted = false;
		$this->blnVersionSubmit = false;
		$this->blnLanguageSubmit = false;
		$this->blnSelectSubmit = false;

		// Form Submit check
		switch ($_POST['FORM_SUBMIT'])
		{
			case $this->strTable:
				$this->blnSubmitted = true;
				break;

			case 'tl_version':
				$this->blnVersionSubmit = true;
				break;

			case 'language_switch':
				$this->blnLanguageSubmit = true;
				break;
		}

		// Act check
		// TODO: dependency injection.
		switch (\Input::getInstance()->get('act'))
		{
			case 'select':
				$this->blnSelectSubmit = true;
				break;
		}

		$this->blnAutoSubmitted = $_POST['SUBMIT_TYPE'] == 'auto';

		// TODO: dependency injection.
		$this->arrInputs = $_POST['FORM_INPUTS'] ? array_flip(\Input::getInstance()->post('FORM_INPUTS')) : array();

		// TODO: dependency injection.
		$this->arrStates = \Session::getInstance()->get('fieldset_states');
		$this->arrStates = (array) $this->arrStates[$this->strTable];
	}

	/* /////////////////////////////////////////////////////////////////////////
	 * -------------------------------------------------------------------------
	 *  Getter and Setter
	 * -------------------------------------------------------------------------
	 * ////////////////////////////////////////////////////////////////////// */

	// Magical Functions --------------------

	/**
	 * @param string $name
	 *
	 * @return array|int|mixed|null|String
	 *
	 * @throws DcGeneralRuntimeException
	 *
	 * @deprecated magic access is deprecated.
	 */
	public function __get($name)
	{
		// TODO: we should get rid of all of this when finally dropping the final BC parts.
		switch ($name)
		{
			// DataContainer overwrite
			case 'id':
				return $this->intId;

			// DataContainer overwrite
			case 'table':
				return $this->strTable;

			// DataContainer overwrite
			case 'field':
				return $this->strField;

			// @overwrite DataContainer overwrite
			case 'inputName':
				return $this->strInputName;

			// Return the current DCA
			case 'DCA':
			case 'arrDCA':
			case 'configuration':
			case 'config':
				return $this->arrDCA;

			// DataContainer overwrite
			case 'palette':
			case 'activeRecord':
				throw new DcGeneralRuntimeException("Unsupported getter function for '$name' in DC_General.");
		}
		// allow importing of objects in Contao 3.
		if (version_compare(VERSION, '3.0', '>='))
		{
			return $this->arrObjects[$name];
		}
	}

	public function updateDCA($arrDCA)
	{
		$this->arrDCA = array_merge($this->arrDCA, $arrDCA);
	}

	// Submitting / State -------------------

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

	public function isLanguageSubmit()
	{
		return $this->blnLanguageSubmit;
	}

	public function isSelectSubmit()
	{
		return $this->blnSelectSubmit;
	}

	// MVC ----------------------------------

	public function getName()
	{
		return $this->strTable;
	}

	/**
	 * {@inheritdoc}
	 *
	 * @deprecated
	 */
	public function getDataProvider($strSource = null)
	{
		trigger_error('deprecated use of getDataProvider() - use getEnvironment()->getDataDriver() instead.');
		return $this->getEnvironment()->getDataDriver($strSource);
	}

	public function getEnvironment()
	{
		return $this->objEnvironment;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getInputProvider()
	{
		return $this->getEnvironment()->getInputProvider();
	}

	/**
	 * {@inheritdoc}
	 */
	public function getDataDefinition()
	{
		return $this->getEnvironment()->getDataDefinition();
	}

	/**
	 * {@inheritdoc}
	 */
	public function getPanelInformation()
	{
		return $this->getEnvironment()->getPanelContainer();
	}

	public function getViewHandler()
	{
		return $this->getEnvironment()->getView();
	}

	public function setViewHandler($objViewHandler)
	{
		$this->getEnvironment()->setView($objViewHandler);
	}

	/**
	 * Get the callback class for this dc
	 *
	 * @deprecated
	 */
	public function getCallbackClass()
	{
		return $this->getEnvironment()->getCallbackHandler();
	}

	public function getControllerHandler()
	{
		return $this->objController;
	}

	public function setControllerHandler($objController)
	{
		$this->objController = $objController;
	}

	// Join Conditions & Co. ----------------

	/**
	 * Get the parent -> child condition from the parenting to the child table.
	 *
	 * @param mixed   $mixParent   either the model that shall be taken as parent or the name of the parent table.
	 *
	 * @param string  $strDstTable the name of the desired child table.
	 */
	public function getParentChildCondition($mixParent, $strDstTable)
	{
		$arrChildDefinitions = $this->arrDCA['dca_config']['childCondition'];
		if (is_array($arrChildDefinitions) && !empty($arrChildDefinitions))
		{
			if (is_object($mixParent))
			{
				// must be model!
				if (! $mixParent instanceof ModelInterface)
				{
					throw new DcGeneralRuntimeException('incompatible object passed');
				}
				$strSrcTable = $mixParent->getProviderName();
			}
			else
			{
				$strSrcTable = $mixParent;
			}

			if ($strSrcTable == 'self')
			{
				$strSrcTable = $this->getTable();
			}

			foreach ($arrChildDefinitions as $arrCondition)
			{
				$strFrom = $arrCondition['from'];
				$strTo = $arrCondition['to'];
				// check table naming match
				if ((($strFrom == $strSrcTable) || (($strFrom == 'self') && ($strSrcTable == $this->getTable())))
					&& ((($strTo == $strDstTable) || (($strTo == 'self') && ($strDstTable == $this->getTable())))))
				{
					return $arrCondition;
				}
			}
		}
		else
		{
			// fallback to pid <=> id mapping (legacy dca).
			return array
				(
				'from' => 'self',
				'to' => $strDstTable,
				'setOn' => array
				(
				array(
					'to_field' => 'pid',
					'from_field' => 'id',
				),
				),
				'filter' => array
				(
				array
					(
					'local' => 'pid',
					'remote' => 'id',
					'operation' => '=',
				)
				)
			);
		}
	}

	/**
	 * Return a array with the join conditions for a special table.
	 * If no value is found in the dca, the default id=pid conditions will be used.
	 *
	 * @param ModelInterface $objParentModel the model that holds data from the src (aka parent).
	 *
	 * @param string                $strDstTable    Name of table for "child"
	 *
	 * @return array
	 */
	public function getChildCondition(ModelInterface $objParentModel, $strDstTable)
	{
		$arrReturn = array();

		if ($strDstTable == 'self')
		{
			$strDstTable = $this->getTable();
		}

		$arrCondition = $this->getParentChildCondition($objParentModel, $strDstTable);

		if (is_array($arrCondition) && !empty($arrCondition))
		{
			// now we have a valid condition found for the desired direction.
			// We will now replace the local and remote parts in the subconditions with the desired values
			// from the provided model.
			foreach ($arrCondition['filter'] as $subCondition)
			{
				$arrNew = array
					(
					'operation' => $subCondition['operation'],
					'property' => $subCondition['local']
				);
				if ($subCondition['remote'])
				{
					$arrNew['value'] = $objParentModel->getProperty($subCondition['remote']);
				}
				else if (isset($subCondition['remote_value']))
				{
					// NOTE: keep isset() above to also allow values of '0' and 'false'.
					$arrNew['value'] = $subCondition['remote_value'];
				}
				else
				{
					throw new DcGeneralRuntimeException('Error: neither remote field nor remote value specified in: ' . var_export($subCondition, true), 1);
				}
				$arrReturn[] = $arrNew;
			}
		}

		// fallback to pid <=> id mapping (legacy dca).
		if (empty($arrReturn))
		{
			$arrReturn[] = array
				(
				'operation' => '=',
				'property' => 'pid',
				'value' => $objParentModel->getProperty('id')
			);
		}

		return $arrReturn;
	}

	/**
	 * Get the definition of a root entry setter
	 *
	 * @return array
	 */
	public function getRootSetter($strTable)
	{
		if ($strTable == $this->getTable())
		{
			$strTable = 'self';
		}
		$arrReturn = array();
		// parse the condition into valid filter rules.
		$arrFilters = $this->arrDCA['dca_config']['rootEntries'][$strTable]['setOn'];
		if ($arrFilters)
		{
			$arrReturn = $arrFilters;
		}
		else
		{
			$arrReturn[] = array
				(
				'property' => 'pid',
				'value' => 0
			);
		}
		return $arrReturn;
	}

	public function isRootItem(ModelInterface $objParentModel, $strTable)
	{
		return $this->getEnvironment()->getDataDefinition($strTable)->getRootCondition()->matches($objParentModel);
	}

	/**
	 * Sets all parent condition fields in the destination to the values from the source model.
	 * Useful when moving an element after another in a different parent.
	 *
	 * @param ModelInterface $objDestination the model that shall get updated.
	 * @param ModelInterface $objCopyFrom    the model that the values shall get retrieved from.
	 * @param string                $strParentTable the parent table for the objects.
	 */
	public function setSameParent(ModelInterface $objDestination, ModelInterface $objCopyFrom, $strParentTable)
	{
		if ($this->isRootItem($objCopyFrom, $strParentTable))
		{
			// copy root setter values.
			$arrChildCondition = $this->getRootSetter($strParentTable);
		}
		else
		{
			$arrChildCondition = $this->getParentChildCondition($strParentTable, $objCopyFrom->getProviderName());
			$arrChildCondition = $arrChildCondition['setOn'];
		}
		if ($arrChildCondition)
		{
			foreach ($arrChildCondition as $arrOperation)
			{
				$strProperty = array_key_exists('to_field', $arrOperation) ? $arrOperation['to_field'] : $arrOperation['property'];
				if (!$strProperty)
				{
					throw new DcGeneralRuntimeException('neither to_field nor property found in condition');
				}
				$objDestination->setProperty($strProperty, $objCopyFrom->getProperty($strProperty));
			}
		}
	}

	// Basic vars ---------------------------

	public function getId()
	{
		return $this->intId;
	}

	/**
	 * @return array|null
	 *
	 * @deprecated Use getEnvironment()->getRootIds() instead.
	 */
	public function getRootIds()
	{
		trigger_error('deprecated use of getRootIds() - use getEnvironment()->getRootIds() instead.', E_USER_DEPRECATED);

		return $this->getEnvironment()->getRootIds();
	}

	public function getTable()
	{
		return $this->strTable;
	}

	/**
	 * @return string
	 *
	 * @deprecated
	 */
	public function getParentTable()
	{
		trigger_error('Use of deprecated getParentTable() - use getEnvironment()->getDataDefinition()->getParentDriverName() instead');
		return $this->getDataDefinition()->getParentDriverName();
	}

	/**
	 * Get name of child table
	 *
	 * @return string
	 */
	public function getChildTable()
	{
		return $this->strChildTable;
	}

	/**
	 * Set the name of the child table
	 *
	 * @param stirng $strChildTable
	 */
	public function setChildTable($strChildTable)
	{
		$this->strChildTable = $strChildTable;
	}

	// Sorting ------------------------------

	/**
	 * Set the primary field for sorting
	 *
	 * @param str $strFirstSorting
	 */
	public function setFirstSorting($strFirstSorting, $strSortingOrder = DCGE::MODEL_SORTING_ASC)
	{
		$this->strFirstSorting = $strFirstSorting;
		$this->strFirstSortingOrder = $strSortingOrder;
	}

	/**
	 * Get the primary field for sorting
	 *
	 * @return string
	 */
	public function getFirstSorting()
	{
		return $this->strFirstSorting;
	}

	/**
	 * Get the order for the primary field of sorting
	 *
	 * @return string
	 */
	public function getFirstSortingOrder()
	{
		return $this->strFirstSortingOrder;
	}

	/**
	 * Set the sorting fields
	 *
	 * @param array $arrSorting
	 */
	public function setSorting($arrSorting)
	{
		$this->arrSorting = $arrSorting;
	}

	/**
	 * Get the sorting fields
	 *
	 * @return array
	 */
	public function getSorting()
	{
		return $this->arrSorting;
	}

	// Msc. ---------------------------------

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

	public function getButtonId()
	{
		return $this->strButtonId;
	}

	/**
	 * @param $arrRootIds
	 *
	 * @deprecated use getEnvironment()->setRootIds() instead.
	 */
	public function setRootIds($arrRootIds)
	{
		trigger_error('deprecated use of setRootIds() - use getEnvironment()->setRootIds() instead.', E_USER_DEPRECATED);

		$this->getEnvironment()->setRootIds($arrRootIds);
	}

	public function setFilter($arrFilter)
	{
		if (is_array($this->arrFilter))
		{
			$this->arrFilter = array_merge($this->arrFilter, $arrFilter);
		}
		else
		{
			$this->arrFilter = $arrFilter;
		}
	}

	public function setButtonId($strButtonId)
	{
		$this->strButtonId = $strButtonId;
	}

	/**
	 * Check if this DCA is editable
	 *
	 * @return boolean
	 *
	 * @deprecated Use getDataDefinition()->isEditable()
	 */
	public function isEditable()
	{
		return $this->getDataDefinition()->isEditable();
	}

	/**
	 * Check if this DCA is closed
	 *
	 * @return boolean
	 *
	 * @deprecated Use getDataDefinition()->isClosed()
	 */
	public function isClosed()
	{
		return $this->getDataDefinition()->isClosed();
	}

	/**
	 *
	 * @return CollectionInterface
	 *
	 * @deprecated
	 */
	public function getCurrentCollecion()
	{
		trigger_error('do not use this method, it was a typo! - use getCurrentCollection() instead.', E_USER_DEPRECATED);
		return $this->getCurrentCollection();
	}

	/**
	 *
	 * @return CollectionInterface
	 *
	 * @deprecated
	 */
	public function getCurrentCollection()
	{
		trigger_error('deprecated - use getEnvironment()->getCurrentCollection() instead.', E_USER_DEPRECATED);
		return $this->getEnvironment()->getCurrentCollection();
	}

	/**
	 *
	 * @return CollectionInterface
	 *
	 * @deprecated
	 */
	public function getCurrentParentCollection()
	{
		trigger_error('deprecated us of getCurrentParentCollection - use getEnvironment()->getCurrentParentCollection() instead.', E_USER_DEPRECATED);
		return $this->getEnvironment()->getCurrentParentCollection();
	}

	/**
	 *
	 * @return ModelInterface
	 *
	 * @deprecated
	 */
	public function getCurrentModel()
	{
		trigger_error('deprecated - use getEnvironment()->getCurrentModel() instead.', E_USER_DEPRECATED);
		return $this->getEnvironment()->getCurrentModel();
	}

	/**
	 *
	 * @param CollectionInterface $objCurrentParentCollection
	 *
	 * @deprecated
	 */
	public function setCurrentParentCollection(CollectionInterface $objCurrentParentCollection)
	{
		trigger_error('deprecated us of setCurrentParentCollection - use getEnvironment()->setCurrentParentCollection() instead.', E_USER_DEPRECATED);
		$this->getEnvironment()->setCurrentParentCollection($objCurrentParentCollection);
	}

	/**
	 * @param CollectionInterface $objCurrentCollection
	 *
	 * @deprecated
	 */
	public function setCurrentCollecion(CollectionInterface $objCurrentCollection)
	{
		trigger_error('do not use this method, it was a typo! - use setCurrentCollection() instead.', E_USER_DEPRECATED);
		$this->setCurrentCollection($objCurrentCollection);
	}

	/**
	 *
	 * @param CollectionInterface $objCurrentCollection
	 *
	 * @return void
	 *
	 * @deprecated
	 */
	public function setCurrentCollection(CollectionInterface $objCurrentCollection)
	{
		trigger_error('deprecated - use getEnvironment()->setCurrentCollection() instead.', E_USER_DEPRECATED);
		$this->getEnvironment()->setCurrentCollection($objCurrentCollection);
	}

	/**
	 *
	 * @param ModelInterface $objCurrentModel
	 *
	 * @deprecated
	 */
	public function setCurrentModel(ModelInterface $objCurrentModel)
	{
		trigger_error('deprecated - use getEnvironment()->setCurrentModel() instead.', E_USER_DEPRECATED);
		return $this->getEnvironment()->setCurrentModel($objCurrentModel);
	}

	/**
	 * Update the current model from a post request. Additionally, trigger meta palettes, if installed.
	 */
	public function updateModelFromPOST()
	{
		$propertyValues = $this->getEnvironment()->getView()->processInput();
		if ($propertyValues) {
			// callback to tell visitors that we have just updated the model.
			$this->getCallbackClass()->onModelBeforeUpdateCallback($this->getEnvironment()->getCurrentModel());

			foreach ($propertyValues as $property => $value)
			{
				try
				{
					$this->getEnvironment()->getCurrentModel()->setProperty($property, $value);
					$this->getEnvironment()->getCurrentModel()->setMeta(DCGE::MODEL_IS_CHANGED, true);
				}
				catch (\Exception $exception)
				{
					$this->blnNoReload = true;
					$propertyValues->markPropertyValueAsInvalid($property, $exception);
				}
			}

			// FIXME: dependency injection.
			if (in_array($this->arrDCA['list']['sorting']['mode'], array(4, 5, 6)) && (strlen(\Input::getInstance()->get('pid')) > 0))
			{
				$objParentDriver = $this->getEnvironment()->getDataDriver($this->getEnvironment()->getDataDefinition()->getParentDriverName());
				// pull correct condition from DCA and update according to setOn values.
				$objParentModel = $objParentDriver->fetch($objParentDriver->getEmptyConfig()->setId(\Input::getInstance()->get('pid')));
				$arrCond = $this->getParentChildCondition($objParentModel, $this->getEnvironment()->getDataDefinition()->getName());

				if (is_array($arrCond) && array_key_exists('setOn', $arrCond))
				{
					foreach ($arrCond['setOn'] as $arrSetOn)
					{
						if ($arrSetOn['from_field'])
						{
							$this->getEnvironment()->getCurrentModel()->setProperty($arrSetOn['to_field'], $objParentModel->getProperty($arrSetOn['from_field']));
						}
						else
						{
							$this->getEnvironment()->getCurrentModel()->setProperty($arrSetOn['to_field'], $arrSetOn['value']);
						}
					}
				}
			}

			// TODO: is this really a wise idea here?
			// FIXME: dependency injection.
			if (in_array('metapalettes', \Config::getInstance()->getActiveModules()))
			{
				\MetaPalettes::getInstance()->generateSubSelectPalettes($this);
			}

			// callback to tell visitors that we have just updated the model.
			$this->getCallbackClass()->onModelUpdateCallback($this->getEnvironment()->getCurrentModel());
		}
	}

	/**
	 * Return the Child DC
	 * @return DC_General
	 */
	public function getChildDC()
	{
		return $this->objChildDC;
	}

	/**
	 * Set the Child DC
	 * @param DC_General $objChildDC
	 */
	public function setChildDC($objChildDC)
	{
		$this->objChildDC = $objChildDC;
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
	 *
	 * @deprecated use getEnvironment()->getDataDefinition()->getSubPalettes() instead.
	 */
	public function getSubpalettesDefinition()
	{
		trigger_error('deprecated use of getSubpalettesDefinition() - use getEnvironment()->getDataDefinition()->getSubPalettes() instead.');
		// return is_array($this->arrDCA['subpalettes']) ? $this->arrDCA['subpalettes'] : array();
		return $this->getEnvironment()->getDataDefinition()->getSubPalettes();
	}

	/**
	 * Get palettes definition
	 *
	 * @return array
	 *
	 * @deprecated use getEnvironment()->getDataDefinition()->getPalettes() instead.
	 */
	public function getPalettesDefinition()
	{
		trigger_error('deprecated use of getPalettesDefinition() - use getEnvironment()->getDataDefinition()->getPalettes() instead.');
		// return is_array($this->arrDCA['palettes']) ? $this->arrDCA['palettes'] : array();
		return $this->getEnvironment()->getDataDefinition()->getPalettes();
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
		$arrButtons = $this->getButtonsDefinition();
		
		// Check if the button have the lable value itself
		if(array_key_exists($strButton, $arrButtons) && !empty($arrButtons[$strButton]['value']))
		{
			return $arrButtons[$strButton]['value'];
		}
		// else try to finde a language array
		else if (isset($GLOBALS['TL_LANG'][$this->strTable][$strButton]))
		{
			return $GLOBALS['TL_LANG'][$this->strTable][$strButton];
		}
		else if (isset($GLOBALS['TL_LANG']['MSC'][$strButton]))
		{
			return $GLOBALS['TL_LANG']['MSC'][$strButton];
		}
		// Fallback, just return the key as is it.
		else
		{
			return $strButton;
		}
	}

	/**
	 * Add a Button to the dca
	 *
	 * $arrConfig = array(
	 *       'id'              => [ID eg. name],
	 *       'formkey'         => [ID eg. name],
	 *       'class'           => [css class],
	 *       'accesskey'       => 'g',
	 *       'value'           => [value for displaying],
	 *       'button_callback' => array([class], [function])
	 *  );
	 *
	 * @param string $strButton Id of the button.
	 *
	 * @param array $arrConfig An array with information.
	 *
	 * @deprecated Use the GetEditModeButtonsEvent for manipulating buttons in the views.
	 */
	public function addButton($strButton, $arrConfig = array())
	{
		trigger_error('Deprecated use of DC_General::addButton() - Use the GetEditModeButtonsEvent for manipulating buttons in the views.', E_USER_DEPRECATED);
		// Make an array, for older calles.
		if (empty($arrConfig))
		{
			$arrConfig = array
			(
				'id'				 => $strButton,
				'formkey'			 => $strButton,
				'class'				 => '',
				'accesskey'			 => 's',
				'value'				 => null,
				'button_callback'	 => null
			);
		}

		$this->arrDCA['buttons'][$strButton] = $arrConfig;
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
	 * Remove a button from dca
	 *
	 * @param string $strButton
	 *
	 * @deprecated Use the GetEditModeButtonsEvent for manipulating buttons in the views.
	 */
	public function removeButton($strButton)
	{
		trigger_error('Deprecated use of DC_General::removeButton() - Use the GetEditModeButtonsEvent for manipulating buttons in the views.', E_USER_DEPRECATED);

		if (array_key_exists($strButton, $this->arrDCA['buttons']))
		{
			unset($this->arrDCA['buttons'][$strButton]);
		}
	}

	/* /////////////////////////////////////////////////////////////////////////
	 * -------------------------------------------------------------------------
	 * Functions
	 * -------------------------------------------------------------------------
	 * ////////////////////////////////////////////////////////////////////// */

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

	/* /////////////////////////////////////////////////////////////////////////
	 * -------------------------------------------------------------------------
	 * Helper
	 * -------------------------------------------------------------------------
	 * ////////////////////////////////////////////////////////////////////// */

	/**
	 * Return the formatted group header as string
	 *
	 * @param string $field
	 * @param mixed $value
	 * @param integer $mode
	 * @return string
	 *
	 * @deprecated
	 */
	public function formatCurrentValue($field, $value, $mode)
	{
		trigger_error('Deprecated formatCurrentValue used. Please rewrite the views to inherit from BaseView and use the formatCurrentValue() contained in there.');

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
			$intMonth = ($value != '') ? (date('m', $value) - 1) : '-';

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
	 * @param ModelInterface
	 * @return string
	 *
	 * @deprecated
	 */
	public function formatGroupHeader($field, $value, $mode, ModelInterface $objModelRow)
	{
		trigger_error('Deprecated formatGroupHeader used. Please rewrite the views to inherit from BaseView and use the formatGroupHeader() contained in there.');

		$group = '';
		static $lookup = array();

		if (array_is_assoc($this->arrDCA['fields'][$field]['options']))
		{
			$group = $this->arrDCA['fields'][$field]['options'][$value];
		}
		else if (is_array($this->arrDCA['fields'][$field]['options_callback']))
		{
			if (!isset($lookup[$field]))
			{
				$lookup[$field] = $this->getEnvironment()->getCallbackHandler()->optionsCallback($field);
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

		$group = $this->getEnvironment()->getCallbackHandler()->groupCallback($group, $mode, $field, $objModelRow);

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
			// In Contao 3 it is always a file picker - no need for the button.
			if (version_compare(VERSION, '3.0', '<'))
			{
				$strXLabel .= sprintf(
					' <a href="contao/files.php" title="%s" onclick="Backend.getScrollOffset(); Backend.openWindow(this, 750, 500); return false;">%s</a>', specialchars($GLOBALS['TL_LANG']['MSC']['fileManager']), $this->generateImage(
						'filemanager.gif', $GLOBALS['TL_LANG']['MSC']['fileManager'], 'style="vertical-align:text-bottom;"'
					)
				);
			}
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
		if (count($this->getEnvironment()->getDataDefinition()->getSubPalettes()) == 0)
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

	/* /////////////////////////////////////////////////////////////////////////
	 * -------------------------------------------------------------------------
	 * Field Helper Functions
	 * -------------------------------------------------------------------------
	 * ////////////////////////////////////////////////////////////////////// */

	/**
	 * Get for a field the readable value
	 *
	 * @param string $strFieldName
	 * @param mixed $mixValue
	 * @return mixed [string|int]
	 */
	public function getReadableFieldValue($strFieldName, $mixValue)
	{
		if (!key_exists($strFieldName, $this->arrDCA['fields']))
		{
			return $mixValue;
		}

		// Load the config for current field
		$arrFieldConfig = $this->arrDCA['fields'][$strFieldName];
		$mixModelField = $this->getEnvironment()->getCurrentModel()->getProperty($strFieldName);

		/*
		 * @todo Maybe the controlle should handle this ?
		 */
		if (isset($arrFieldConfig['foreignKey']))
		{
			$temp = array();
			$chunks = explode('.', $arrFieldConfig['foreignKey'], 2);


			foreach ((array) $value as $v)
			{
//                    $objKey = $this->Database->prepare("SELECT " . $chunks[1] . " AS value FROM " . $chunks[0] . " WHERE id=?")
//                            ->limit(1)
//                            ->execute($v);
//
//                    if ($objKey->numRows)
//                    {
//                        $temp[] = $objKey->value;
//                    }
			}

//                $row[$i] = implode(', ', $temp);
		}
		// Decode array
		else if (is_array($mixValue))
		{
			foreach ($mixValue as $kk => $vv)
			{
				if (is_array($vv))
				{
					$vals = array_values($vv);
					$mixValue[$kk] = $vals[0] . ' (' . $vals[1] . ')';
				}
			}

			return implode(', ', $mixValue);
		}
		// Date Formate
		else if ($arrFieldConfig['eval']['rgxp'] == 'date')
		{
			return $this->parseDate($GLOBALS['TL_CONFIG']['dateFormat'], $mixValue);
		}
		// Date Formate
		else if ($arrFieldConfig['eval']['rgxp'] == 'time')
		{
			return $this->parseDate($GLOBALS['TL_CONFIG']['timeFormat'], $mixValue);
		}
		// Date Formate
		else if ($arrFieldConfig['eval']['rgxp'] == 'datim' || in_array($arrFieldConfig['flag'], array(5, 6, 7, 8, 9, 10)) || $strFieldName == 'tstamp')
		{
			return $this->parseDate($GLOBALS['TL_CONFIG']['datimFormat'], $mixValue);
		}
		else if ($arrFieldConfig['inputType'] == 'checkbox' && !$arrFieldConfig['eval']['multiple'])
		{
			return strlen($mixValue) ? $GLOBALS['TL_LANG']['MSC']['yes'] : $GLOBALS['TL_LANG']['MSC']['no'];
		}
		else if ($arrFieldConfig['inputType'] == 'textarea' && ($arrFieldConfig['eval']['allowHtml'] || $arrFieldConfig['eval']['preserveTags']))
		{
			return nl2br_html5(specialchars($mixValue));
		}
		else if (is_array($arrFieldConfig['reference']))
		{
			return isset($arrFieldConfig['reference'][$mixModelField]) ?
				((is_array($arrFieldConfig['reference'][$mixModelField])) ?
					$arrFieldConfig['reference'][$mixModelField][0] :
					$arrFieldConfig['reference'][$mixModelField]) :
				$mixModelField;
		}
		else if (array_is_assoc($arrFieldConfig['options']))
		{
			return $arrFieldConfig['options'][$mixModelField];
		}
		else
		{
			return $mixValue;
		}
	}

	/* /////////////////////////////////////////////////////////////////////////
	 * -------------------------------------------------------------------------
	 * Interface funtions
	 * -------------------------------------------------------------------------
	 * ////////////////////////////////////////////////////////////////////// */

	public function __call($name, $arguments)
	{
		$strReturn = call_user_func_array(array($this->objController, $name), array_merge(array($this), $arguments));
		if ($strReturn != null && $strReturn != "")
		{
			return $strReturn;
		}

		return call_user_func_array(array($this->getViewHandler(), $name), array_merge(array($this), $arguments));
	}

	public function paste()
	{
		return call_user_func_array(array($this->getViewHandler(), 'paste'), func_get_args());
	}

	public function generateAjaxPalette($strSelector)
	{
		$strReturn = $this->objController->generateAjaxPalette($strSelector);
		if ($strReturn != null && $strReturn != "")
		{
			return $strReturn;
		}

		return $this->getViewHandler()->generateAjaxPalette($strSelector);
	}

	public function ajaxTreeView($intID, $intLevel)
	{
		return $this->getViewHandler()->ajaxTreeView($intID, $intLevel);

		$strReturn = $this->objController->ajaxTreeView($intID, $intLevel);
		if ($strReturn != null && $strReturn != "")
		{
			return $strReturn;
		}

		return $this->getViewHandler()->ajaxTreeView($intID, $intLevel);
	}

	public function copy()
	{
		$strReturn = $this->objController->copy();
		if ($strReturn != null && $strReturn != "")
		{
			return $strReturn;
		}

		return $this->getViewHandler()->copy();
	}

	public function create()
	{
		// If forcemode true, use edit mode only.
		if($this->blnForceEdit)
		{
			return $this->edit();
		}

		$strReturn = $this->objController->create();
		if ($strReturn != null && $strReturn != "")
		{
			return $strReturn;
		}

		$strReturn = $GLOBALS['TL_CONFIG']['debugMode'] ? $this->setupTimer() : '';
		return $strReturn . $this->getViewHandler()->create();
	}

	public function cut()
	{
		$strReturn = $this->objController->cut();
		if ($strReturn != null && $strReturn != "")
		{
			return $strReturn;
		}

		return $this->getViewHandler()->cut();
	}

	public function delete()
	{
		$strReturn = $this->objController->delete();
		if ($strReturn != null && $strReturn != "")
		{
			return $strReturn;
		}

		return $this->getViewHandler()->delete();
	}

	public function edit()
	{
		$strReturn = $this->objController->edit();
		if ($strReturn != null && $strReturn != "")
		{
			return $strReturn;
		}

		$strReturn = $GLOBALS['TL_CONFIG']['debugMode'] ? $this->setupTimer() : '';
		return $strReturn . $this->getViewHandler()->edit();
	}

	public function move()
	{
		$strReturn = $this->objController->move();
		if ($strReturn != null && $strReturn != "")
		{
			return $strReturn;
		}

		return $this->getViewHandler()->move();
	}

	public function show()
	{
		// If forcemode true, use edit mode only.
		if($this->blnForceEdit)
		{
			return $this->edit();
		}

		$strReturn = $this->objController->show();
		if ($strReturn != null && $strReturn != "")
		{
			return $strReturn;
		}

		$strReturn = $GLOBALS['TL_CONFIG']['debugMode'] ? $this->setupTimer() : '';
		return $strReturn . $this->getViewHandler()->show();
	}

	public function showAll()
	{
		// If force edit mode true, use edit mode only.
		if($this->blnForceEdit)
		{
			return $this->edit();
		}

		return $this->getEnvironment()->getView()->showAll();
	}

	public function undo()
	{
		$strReturn = $this->getControllerHandler()->undo();
		if ($strReturn != null && $strReturn != "")
		{
			return $strReturn;
		}

		return $this->getViewHandler()->undo();
	}

	protected function setupTimer()
	{
		if (version_compare(VERSION, '3.1', '>='))
		{
			$query_count = count($GLOBALS['TL_DEBUG']['database_queries']);
		}
		else
		{
			$query_count = $GLOBALS['TL_DEBUG'];
		}

		return sprintf(
			'<div style="padding:5px; border:1px solid gray; margin:7px;"> Runtime: %s Sec. - Queries: %s - Mem: %s</div>',
			number_format((microtime(true) - $this->intTimerStart), 4),
			$query_count - $this->intQueryCount,
			$this->getReadableSize(memory_get_peak_usage(true))
		);
	}

}
