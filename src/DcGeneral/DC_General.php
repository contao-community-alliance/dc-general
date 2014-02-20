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
use DcGeneral\Data\ModelInterface;
use DcGeneral\Event\EventPropagator;
use DcGeneral\Exception\DcGeneralRuntimeException;
use DcGeneral\Factory\DcGeneralFactory;
use DcGeneral\Factory\Event\PopulateEnvironmentEvent;

/**
 * This class is only present so Contao can instantiate a backend properly as it needs a \DataContainer descendant.
 *
 * @package DcGeneral
 */
class DC_General
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
	 * DCA configuration
	 * @var array
	 */
	protected $arrDCA = null;

	/**
	/**
	 * A list with all field for this dca
	 * @var array
	 */
	protected $arrFields = array();

	/**
	 */
	public function __construct($strTable, array &$arrDCA = null, $blnOnloadCallback = true)
	{
		parent::__construct();

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

	/**
	 *
	 * Get the definition of a root entry setter
	 *
	 * @return array
	 */
	public function getRootSetter($strTable)
	{
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
