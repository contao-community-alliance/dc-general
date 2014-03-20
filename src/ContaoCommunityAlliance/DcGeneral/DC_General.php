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

namespace ContaoCommunityAlliance\DcGeneral;

use ContaoCommunityAlliance\Translator\Contao\LangArrayTranslator;
use ContaoCommunityAlliance\Translator\TranslatorChain;
use ContaoCommunityAlliance\DcGeneral\Contao\Callback\Callbacks;
use ContaoCommunityAlliance\DcGeneral\Controller\Ajax2X;
use ContaoCommunityAlliance\DcGeneral\Controller\Ajax3X;
use ContaoCommunityAlliance\DcGeneral\Controller\ControllerInterface;
use ContaoCommunityAlliance\DcGeneral\Event\EventPropagator;
use ContaoCommunityAlliance\DcGeneral\Exception\DcGeneralRuntimeException;
use ContaoCommunityAlliance\DcGeneral\Factory\DcGeneralFactory;
use ContaoCommunityAlliance\DcGeneral\Factory\Event\PopulateEnvironmentEvent;
use ContaoCommunityAlliance\DcGeneral\View\ViewInterface;

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
	 * A list with all field for this dca.
	 *
	 * @var array
	 */
	protected $arrFields = array();

	/**
	 * Create a new instance.
	 *
	 * @param string $strTable         The table name.
	 *
	 * @param array  $arrBackendModule The backend module information array.
	 */
	public function __construct($strTable, array &$arrBackendModule = null)
	{
		parent::__construct();

		$strTable = $this->getTablenameCallback($strTable);

		$dispatcher = $GLOBALS['container']['event-dispatcher'];
		/** @var \Symfony\Component\EventDispatcher\EventDispatcher $dispatcher */
		$dispatcher->addListener(PopulateEnvironmentEvent::NAME, array($this, 'handlePopulateEnvironment'), 4800);
		$propagator = new EventPropagator($dispatcher);

		$translator = new TranslatorChain();
		$translator->add(new LangArrayTranslator($dispatcher));

		$factory = new DcGeneralFactory();

		$factory
			->setContainerName($strTable)
			->setEventPropagator($propagator)
			->setTranslator($translator)
			->createDcGeneral();
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
		return $this->getEnvironment()->getController()->handle(new Action($name, $arguments));
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
		return $this->getEnvironment()->getController()->handle(new Action('copy'));
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
		return $this->getEnvironment()->getController()->handle(new Action('create'));
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
		return $this->getEnvironment()->getController()->handle(new Action('cut'));
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
		return $this->getEnvironment()->getController()->handle(new Action('delete'));
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
		return $this->getEnvironment()->getController()->handle(new Action('edit'));
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
		return $this->getEnvironment()->getController()->handle(new Action('move'));
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
		return $this->getEnvironment()->getController()->handle(new Action('show'));
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
		return $this->getEnvironment()->getController()->handle(new Action('showAll'));
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
		return $this->getEnvironment()->getController()->handle(new Action('undo'));
	}
}
