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

use CyberSpectrum\ContaoDebugger\Debugger;
use DcGeneral\Contao\Callback\Callbacks;
use DcGeneral\Contao\LangArrayTranslator;
use DcGeneral\Data\ModelInterface;
use DcGeneral\Event\EventPropagator;
use DcGeneral\Exception\DcGeneralRuntimeException;
use DcGeneral\Factory\DcGeneralFactory;
use DcGeneral\Factory\Event\PopulateEnvironmentEvent;

class DC_General extends \DataContainer implements DataContainerInterface
{
	/**
	 * @var EnvironmentInterface
	 */
	protected $objEnvironment;

	/**
	 * DCA configuration
	 * @var array
	 */
	protected $arrDCA = null;

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

	// Misc. -----------------------

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

	/* /////////////////////////////////////////////////////////////////////////
	 * -------------------------------------------------------------------------
	 *  Constructor and co.
	 * -------------------------------------------------------------------------
	 * ////////////////////////////////////////////////////////////////////// */

	public function __construct($strTable, array &$arrDCA = null, $blnOnloadCallback = true)
	{
		// Call parent
		parent::__construct();

		// Callback
		$strTable = $this->getTablenameCallback($strTable);

		// in contao 3 the second constructor parameter is the backend module array.
		// Therefore we have to check if the passed argument is indeed a valid DCA.
		if ($arrDCA != null && $arrDCA['config'])
		{
			$this->arrDCA = $arrDCA;
		}
		else
		{
			$this->arrDCA = &$GLOBALS['TL_DCA'][$strTable];
		}

		global $container;
		$dispatcher = $container['event-dispatcher'];
		/** @var \Symfony\Component\EventDispatcher\EventDispatcher $dispatcher */
		$dispatcher->addListener(PopulateEnvironmentEvent::NAME, array($this, 'handlePopulateEnvironment'));

		/** @var \Symfony\Component\EventDispatcher\EventDispatcher $dispatcher */
		$dispatcher = $container['event-dispatcher'];
		$propagator = new EventPropagator($dispatcher);

		// TODO support additional translators
		$translator = new LangArrayTranslator();

		$factory = new DcGeneralFactory();
		// FIXME: transporting the current instance via $GLOBALS is needed to tell the callback handler about this class.
		// We definitely want to get rid of this again when dropping all the callback handlers. See also: ExtendedLegacyDcaPopulator::populateCallback()
		$GLOBALS['objDcGeneral'] = $this;
		$dcGeneral = $factory
			->setContainerName($strTable)
			->setEventPropagator($propagator)
			->setTranslator($translator)
			->createDcGeneral();
		unset($GLOBALS['objDcGeneral']);
		$dispatcher->removeListener(PopulateEnvironmentEvent::NAME, array($this, 'handlePopulateEnvironment'));

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

		// Check for forcemode
		if ($this->arrDCA['config']['forceEdit'])
		{
			$this->blnForceEdit = true;
			$this->intId        = 1;
		}

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

	public function handlePopulateEnvironment(PopulateEnvironmentEvent $event)
	{
		$this->objEnvironment = $event->getEnvironment();
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
				$strCurrentTable = Callbacks::call($callback, $strTable, $this);

				if ($strCurrentTable != null)
				{
					$strTable = $strCurrentTable;
				}
			}
		}

		return $strTable;
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

		// Form Submit check
		switch ($_POST['FORM_SUBMIT'])
		{
			case $this->getEnvironment()->getDataDefinition()->getName():
				$this->blnSubmitted = true;
				break;

			case 'tl_version':
				$this->blnVersionSubmit = true;
				break;
		}

		$this->blnAutoSubmitted = $_POST['SUBMIT_TYPE'] == 'auto';

		// TODO: dependency injection.
		$this->arrInputs = $_POST['FORM_INPUTS'] ? array_flip(\Input::getInstance()->post('FORM_INPUTS')) : array();

		// TODO: dependency injection.
		$this->arrStates = \Session::getInstance()->get('fieldset_states');
		$this->arrStates = (array) $this->arrStates[$this->getEnvironment()->getDataDefinition()->getName()];
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
		switch ($name)
		{
			case 'table':
				return $this->getEnvironment()->getDataDefinition()->getName();
		}

		throw new DcGeneralRuntimeException("Unsupported getter function for '$name' in DC_General.");
	}

	public function getDCA()
	{
		return $this->arrDCA;
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

	// MVC ----------------------------------

	public function getName()
	{
		return $this->getEnvironment()->getDataDefinition()->getName();
	}

	public function getEnvironment()
	{
		if (!$this->objEnvironment)
		{
			throw new DcGeneralRuntimeException('No Environment set.');
		}

		return $this->objEnvironment;
	}

	public function getViewHandler()
	{
		return $this->getEnvironment()->getView();
	}

	public function getControllerHandler()
	{
		return $this->getEnvironment()->getController();
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

		$event = new FormatGroupLabelEvent($this->getEnvironment(), $objModelRow, $field, $mode);
		$event->setGroupLabel($group);

		$this->getEnvironment()->getEventPropagator()->propagate(
			$event,
			$this->getEnvironment()->getDataDefinition()->getName()
		);

		$group = $event->getGroupLabel();

		return $group;
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
	 * Interface funtions
	 * -------------------------------------------------------------------------
	 * ////////////////////////////////////////////////////////////////////// */

	/**
	 * Delegate all calls directly to current view.
	 */
	public function __call($name, $arguments)
	{
		return call_user_func_array(array($this->getViewHandler(), $name), $arguments);
	}

	/**
	 * @deprecated Only here as requirement of \editable
	 */
	public function copy()
	{
		return $this->getViewHandler()->copy();
	}

	/**
	 * @deprecated Only here as requirement of \editable
	 */
	public function create()
	{
		return $this->getViewHandler()->create();
	}

	/**
	 * @deprecated Only here as requirement of \editable
	 */
	public function cut()
	{
		return $this->getViewHandler()->cut();
	}

	/**
	 * @deprecated Only here as requirement of \listable
	 */
	public function delete()
	{
		return $this->getViewHandler()->delete();
	}

	/**
	 * @deprecated Only here as requirement of \editable
	 */
	public function edit()
	{
		return $this->getViewHandler()->edit();
	}

	/**
	 * @deprecated Only here as requirement of \editable
	 */
	public function move()
	{
		return $this->getViewHandler()->move();
	}

	/**
	 * @deprecated Only here as requirement of \listable
	 */
	public function show()
	{
		return $this->getViewHandler()->show();
	}

	/**
	 * @deprecated Only here as requirement of \listable
	 */
	public function showAll()
	{
		return $this->getViewHandler()->showAll();
	}

	/**
	 * @deprecated Only here as requirement of \listable
	 */
	public function undo()
	{
		return $this->getViewHandler()->undo();
	}
}
