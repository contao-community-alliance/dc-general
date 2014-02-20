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

use ContaoCommunityAlliance\Translator\Contao\LangArrayTranslator;
use ContaoCommunityAlliance\Translator\TranslatorChain;
use DcGeneral\Contao\Callback\Callbacks;
use DcGeneral\Controller\Ajax2X;
use DcGeneral\Controller\Ajax3X;
use DcGeneral\Controller\ControllerInterface;
use DcGeneral\Data\ModelInterface;
use DcGeneral\Event\EventPropagator;
use DcGeneral\Exception\DcGeneralRuntimeException;
use DcGeneral\Factory\DcGeneralFactory;
use DcGeneral\Factory\Event\PopulateEnvironmentEvent;
use DcGeneral\View\ViewInterface;

/**
 * This class is only present so Contao can instantiate a backend properly as it needs a \DataContainer descendant.
 *
 * @package DcGeneral
 */
// @codingStandardsIgnoreStart - Class is not in camelCase as Contao does not allow us to.
class DC_General
// @codingStandardsIgnoreEnd
	extends \DataContainer
	implements DataContainerInterface
{
	/**
	 * The environment attached to this DC.
	 *
	 * @var EnvironmentInterface
	 */
	protected $objEnvironment;

	/**
	 * DCA configuration.
	 *
	 * @var array
	 */
	protected $arrDCA = null;

	/**
	 * A list with all field for this dca.
	 *
	 * @var array
	 */
	protected $arrFields = array();

	/**
	 * Create a new instance.
	 *
	 * @param string $strTable          The table name.
	 *
	 * @param array  $arrDCA            The Dca array.
	 *
	 * @param bool   $blnOnloadCallback Fire the onload callback.
	 */
	public function __construct($strTable, array &$arrDCA = null, $blnOnloadCallback = true)
	{
		parent::__construct();

		$strTable = $this->getTablenameCallback($strTable);

		// In contao 3 the second constructor parameter is the backend module array.
		// Therefore we have to check if the passed argument is indeed a valid DCA.
		if ($arrDCA != null && $arrDCA['config'])
		{
			$this->arrDCA = $arrDCA;
		}
		else
		{
			$this->arrDCA = &$GLOBALS['TL_DCA'][$strTable];
		}

		$dispatcher = $GLOBALS['container']['event-dispatcher'];
		/** @var \Symfony\Component\EventDispatcher\EventDispatcher $dispatcher */
		$dispatcher->addListener(PopulateEnvironmentEvent::NAME, array($this, 'handlePopulateEnvironment'), 4800);
		$propagator = new EventPropagator($dispatcher);

		$translator = new TranslatorChain();
		$translator->add(new LangArrayTranslator($dispatcher));

		$factory = new DcGeneralFactory();
		// We definitely want to get rid of this again when dropping all the callback handlers.
		// See also implementation of: ExtendedLegacyDcaPopulator::populateCallback().
		// FIXME: transporting the current instance via $GLOBALS is needed to tell the callback handler about this class.
		$GLOBALS['objDcGeneral'] = $this;

		$factory
			->setContainerName($strTable)
			->setEventPropagator($propagator)
			->setTranslator($translator)
			->createDcGeneral();
		unset($GLOBALS['objDcGeneral']);
		$dispatcher->removeListener(PopulateEnvironmentEvent::NAME, array($this, 'handlePopulateEnvironment'));

		// Switch user for FE / BE support.
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

		// Check for force mode.
		if ($this->arrDCA['config']['forceEdit'])
		{
			$this->blnForceEdit = true;
			$this->intId        = 1;
		}

		// Load the clipboard.
		$this->getEnvironment()->getClipboard()
			->loadFrom($this->getEnvironment());

		// Execute AJAX request, called from Backend::getBackendModule
		// we have to do this here, as otherwise the script will exit as it only checks for DC_Table and DC_File
		// derived classes.
		// FIXME: dependency injection.
		if ($_POST && \Environment::getInstance()->isAjaxRequest)
		{
			$this->getViewHandler()->handleAjaxCall();

			// Fallback to Contao for ajax requests we do not know.
			if (version_compare(VERSION, '3.0', '>='))
			{
				$objHandler = new Ajax3X();
			}
			else
			{
				$objHandler = new Ajax2X();
			}
			$objHandler->executePostActions($this);
		}
	}

	/**
	 * Callback coming from the environment populator.
	 *
	 * This is used to get to know the environment here in the DC.
	 * See the implementation in constructor and ExtendedLegacyDcaPopulator::populateCallback().
	 *
	 * @param PopulateEnvironmentEvent $event The event.
	 *
	 * @return void
	 */
	public function handlePopulateEnvironment(PopulateEnvironmentEvent $event)
	{
		$this->objEnvironment = $event->getEnvironment();
	}

	/**
	 * Call the table name callback.
	 *
	 * @param string $strTable The current table name.
	 *
	 * @return string New name of current table.
	 */
	protected function getTablenameCallback($strTable)
	{
		if (array_key_exists('tablename_callback', $GLOBALS['TL_DCA'][$strTable]['config'])
			&& is_array($GLOBALS['TL_DCA'][$strTable]['config']['tablename_callback']))
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
	 * Magic getter.
	 *
	 * @param string $name Name of the property to retrieve.
	 *
	 * @return mixed
	 *
	 * @throws DcGeneralRuntimeException If an invalid key is requested.
	 *
	 * @deprecated magic access is deprecated.
	 */
	public function __get($name)
	{
		switch ($name)
		{
			case 'table':
				return $this->getEnvironment()->getDataDefinition()->getName();
			default:
		}

		throw new DcGeneralRuntimeException('Unsupported getter function for \'' . $name . '\' in DC_General.');
	}

	/**
	 * Retrieve the DCA.
	 *
	 * @return array
	 */
	public function getDCA()
	{
		return $this->arrDCA;
	}

	/**
	 * Retrieve the name of the data container.
	 *
	 * @return string
	 */
	public function getName()
	{
		return $this->getEnvironment()->getDataDefinition()->getName();
	}

	/**
	 * Retrieve the environment.
	 *
	 * @return EnvironmentInterface
	 *
	 * @throws DcGeneralRuntimeException When no environment has been set.
	 */
	public function getEnvironment()
	{
		if (!$this->objEnvironment)
		{
			throw new DcGeneralRuntimeException('No Environment set.');
		}

		return $this->objEnvironment;
	}

	/**
	 * Retrieve the view.
	 *
	 * @return ViewInterface
	 */
	public function getViewHandler()
	{
		return $this->getEnvironment()->getView();
	}

	/**
	 * Retrieve the controller.
	 *
	 * @return ControllerInterface
	 */
	public function getControllerHandler()
	{
		return $this->getEnvironment()->getController();
	}

	/**
	 * Get the definition of a root entry setter.
	 *
	 * @param string $strTable The table name.
	 *
	 * @return array
	 *
	 * @deprecated FIXME: Move to Controller.
	 */
	public function getRootSetter($strTable)
	{
		$arrReturn = array();
		// Parse the condition into valid filter rules.
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

	/**
	 * Check if the given item is a root item.
	 *
	 * @param ModelInterface $objParentModel The model to check.
	 *
	 * @param string         $strTable       The name of the data definition this model could be a root item in.
	 *
	 * @return bool
	 *
	 * @deprecated FIXME: Move to Controller.
	 */
	public function isRootItem(ModelInterface $objParentModel, $strTable)
	{
		return $this->getEnvironment()->getDataDefinition($strTable)->getRootCondition()->matches($objParentModel);
	}

	/**
	 * Sets all parent condition fields in the destination to the values from the source model.
	 *
	 * Useful when moving an element after another in a different parent.
	 *
	 * @param ModelInterface $objDestination The model that shall get updated.
	 *
	 * @param ModelInterface $objCopyFrom    The model that the values shall get retrieved from.
	 *
	 * @param string         $strParentTable The parent table for the objects.
	 *
	 * @return void
	 *
	 * @throws DcGeneralRuntimeException When a property in the condition has not been found.
	 *
	 * @deprecated FIXME: Move to Controller.
	 */
	public function setSameParent(ModelInterface $objDestination, ModelInterface $objCopyFrom, $strParentTable)
	{
		if ($this->isRootItem($objCopyFrom, $strParentTable))
		{
			// Copy root setter values.
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

	/**
	 * Delegate all calls directly to current view.
	 *
	 * @param string $name      Name of the method.
	 *
	 * @param array  $arguments Array of arguments.
	 *
	 * @return mixed
	 */
	public function __call($name, $arguments)
	{
		return call_user_func_array(array($this->getViewHandler(), $name), $arguments);
	}

	/**
	 * Do not use.
	 *
	 * @deprecated Only here as requirement of \editable
	 *
	 * @return string
	 */
	public function copy()
	{
		return $this->getViewHandler()->copy();
	}

	/**
	 * Do not use.
	 *
	 * @deprecated Only here as requirement of \editable
	 *
	 * @return string
	 */
	public function create()
	{
		return $this->getViewHandler()->create();
	}

	/**
	 * Do not use.
	 *
	 * @deprecated Only here as requirement of \editable
	 *
	 * @return string
	 */
	public function cut()
	{
		return $this->getViewHandler()->cut();
	}

	/**
	 * Do not use.
	 *
	 * @deprecated Only here as requirement of \listable
	 *
	 * @return string
	 */
	public function delete()
	{
		return $this->getViewHandler()->delete();
	}

	/**
	 * Do not use.
	 *
	 * @deprecated Only here as requirement of \editable
	 *
	 * @return string
	 */
	public function edit()
	{
		return $this->getViewHandler()->edit();
	}

	/**
	 * Do not use.
	 *
	 * @deprecated Only here as requirement of \editable
	 *
	 * @return string
	 */
	public function move()
	{
		return $this->getViewHandler()->move();
	}

	/**
	 * Do not use.
	 *
	 * @deprecated Only here as requirement of \listable
	 *
	 * @return string
	 */
	public function show()
	{
		return $this->getViewHandler()->show();
	}

	/**
	 * Do not use.
	 *
	 * @deprecated Only here as requirement of \listable
	 *
	 * @return string
	 */
	public function showAll()
	{
		return $this->getViewHandler()->showAll();
	}

	/**
	 * Do not use.
	 *
	 * @deprecated Only here as requirement of \listable
	 *
	 * @return string
	 */
	public function undo()
	{
		return $this->getViewHandler()->undo();
	}
}
