<?php
/**
 * PHP version 5
 * @package    generalDriver
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Tristan Lins <tristan.lins@bit3.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

namespace DcGeneral;

use DcGeneral\Controller\ControllerInterface;
use DcGeneral\Exception\DcGeneralInvalidArgumentException;
use DcGeneral\Exception\DcGeneralRuntimeException;
use DcGeneral\Contao\View\Contao2BackendView\BaseView;

class DefaultEnvironment implements EnvironmentInterface
{
	/**
	 * @var ControllerInterface
	 */
	protected $objController;

	/**
	 * @var \DcGeneral\View\ViewInterface
	 */
	protected $objView;

	/**
	 * The data container definition.
	 *
	 * @var \DcGeneral\DataDefinition\ContainerInterface
	 */
	protected $objDataDefinition;

	/**
	 * The data container definition of the parent table.
	 *
	 * @var \DcGeneral\DataDefinition\ContainerInterface
	 */
	protected $objParentDataDefinition;

	/**
	 * @var InputProviderInterface
	 */
	protected $objInputProvider;

	/**
	 * @var \DcGeneral\Data\DriverInterface[]
	 */
	protected $arrDataProvider;

	/**
	 * @var \DcGeneral\Callbacks\CallbacksInterface
	 */
	protected $objCallbackHandler;

	/**
	 * @var \DcGeneral\Data\CollectionInterface
	 */
	protected $objCollection;

	/**
	 * @var \DcGeneral\Data\CollectionInterface
	 */
	protected $objParentCollection;

	/**
	 * @var \DcGeneral\Data\ModelInterface
	 */
	protected $objModel;

	/**
	 * @var array
	 */
	protected $arrRootIds;

	/**
	 * @var \DcGeneral\Clipboard\ClipboardInterface
	 */
	protected $objClipboard;

	/**
	 * @var \DcGeneral\EnvironmentInterface
	 */
	protected $translator;

	/**
	 * @var \DcGeneral\Event\EventPropagatorInterface
	 */
	protected $eventPropagator;

	/**
	 * {@inheritdoc}
	 */
	public function setController($objController)
	{
		$this->objController = $objController;

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getController()
	{
		return $this->objController;
	}

	/**
	 * {@inheritdoc}
	 */
	public function setView($objView)
	{
		$this->objView = $objView;

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getView()
	{
		return $this->objView;
	}

	/**
	 * {@inheritdoc}
	 */
	public function setDataDefinition($objDataDefinition)
	{
		$this->objDataDefinition = $objDataDefinition;

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getDataDefinition()
	{
		return $this->objDataDefinition;
	}

	/**
	 * {@inheritdoc}
	 */
	public function setParentDataDefinition($objParentDataDefinition)
	{
		$this->objParentDataDefinition = $objParentDataDefinition;

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getParentDataDefinition()
	{
		return $this->objParentDataDefinition;
	}

	/**
	 * {@inheritdoc}
	 */
	public function setInputProvider($objInputProvider)
	{
		$this->objInputProvider = $objInputProvider;

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getInputProvider()
	{
		return $this->objInputProvider;
	}

	/**
	 * {@inheritdoc}
	 */
	public function setCallbackHandler($objCallbackHandler)
	{
		$this->objCallbackHandler = $objCallbackHandler;

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getCallbackHandler()
	{
		return $this->objCallbackHandler;
	}

	/**
	 * {@inheritdoc}
	 */
	public function hasDataProvider($strSource = null)
	{
		if ($strSource === null)
		{
			$strSource = $this->getDataDefinition()->getBasicDefinition()->getDataProvider();
		}

		return (isset($this->arrDataProvider[$strSource]));
	}

	/**
	 * {@inheritdoc}
	 */
	public function getDataProvider($strSource = null)
	{
		if ($strSource === null)
		{
			$strSource = $this->getDataDefinition()->getBasicDefinition()->getDataProvider();
		}

		if (isset($this->arrDataProvider[$strSource]))
		{
			return $this->arrDataProvider[$strSource];
		}

		throw new DcGeneralRuntimeException(sprintf('Data provider %s not defined', $strSource));
	}

	/**
	 * {@inheritdoc}
	 */
	public function addDataProvider($strSource, $objDriver)
	{
		// Force removal of an potentially registered driver to ease sub-classing.
		$this->removeDataProvider($strSource);

		$this->arrDataProvider[$strSource] = $objDriver;

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function removeDataProvider($strSource)
	{
		if (isset($this->arrDataProvider[$strSource]))
		{
			unset($this->arrDataProvider[$strSource]);
		}

		return $this;
	}

	/**
	 * @deprecated Use getDataProvider() instead!
	 */
	public function getDataDriver($strSource = null)
	{
		trigger_error(__CLASS__ . '::getDataDriver() is deprecated - please use ' . __CLASS__ . '::getDataProvider().', E_USER_DEPRECATED);
		return $this->getDataProvider($strSource);
	}

	/**
	 * @deprecated Use addDataProvider() instead!
	 */
	public function addDataDriver($strSource, $objDriver)
	{
		trigger_error(__CLASS__ . '::addDataDriver() is deprecated - please use ' . __CLASS__ . '::addDataProvider().', E_USER_DEPRECATED);
		// Force removal of an potentially registered driver to ease sub-classing.
		$this->addDataProvider($strSource, $objDriver);

		return $this;
	}

	/**
	 * @deprecated use removeDataProvider() instead!
	 */
	public function removeDataDriver($strSource)
	{
		trigger_error(__CLASS__ . '::removeDataDriver() is deprecated - please use ' . __CLASS__ . '::removeDataProvider().', E_USER_DEPRECATED);
		$this->removeDataProvider($strSource);

		return $this;
	}

	/**
	 * @deprecated use the proper interface in the view!
	 */
	public function setPanelContainer($objPanelContainer)
	{
		trigger_error(__CLASS__ . '::setPanelContainer() is deprecated - please use the proper interface in the view.', E_USER_DEPRECATED);

		if (!(($view = $this->getView()) instanceof BaseView))
		{
			throw new DcGeneralInvalidArgumentException(__CLASS__ . '::setPanelContainer() got an invalid view instance passed.');
		}

		/** @var BaseView $view */
		$view->setPanel($objPanelContainer);
		return $this;
	}

	/**
	 * @deprecated use the proper interface in the view!
	 */
	public function getPanelContainer()
	{
		trigger_error(__CLASS__ . '::setPanelContainer() is deprecated - please use the proper interface in the view.', E_USER_DEPRECATED);

		if (!(($view = $this->getView()) instanceof BaseView))
		{
			throw new DcGeneralInvalidArgumentException(__CLASS__ . '::setPanelContainer() got an invalid view instance passed.');
		}

		/** @var BaseView $view */
		return $view->getPanel();
	}

	/**
	 * {@inheritdoc}
	 */
	public function setCurrentCollection($objCurrentCollection)
	{
		$this->objCollection = $objCurrentCollection;

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getCurrentCollection()
	{
		return $this->objCollection;
	}

	/**
	 * {@inheritdoc}
	 */
	public function setCurrentModel($objCurrentModel)
	{
		$this->objModel = $objCurrentModel;

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getCurrentModel()
	{
		return $this->objModel;
	}

	/**
	 *
	 * @param \DcGeneral\Data\CollectionInterface $objCurrentParentCollection
	 *
	 * @return EnvironmentInterface
	 */
	public function setCurrentParentCollection($objCurrentParentCollection)
	{
		$this->objParentCollection = $objCurrentParentCollection;

		return $this;
	}

	/**
	 *
	 * @return \DcGeneral\Data\CollectionInterface
	 */
	public function getCurrentParentCollection()
	{
		return $this->objParentCollection;
	}

	/**
	 * {@inheritdoc}
	 */
	public function setRootIds($arrRootIds)
	{
		$this->arrRootIds = $arrRootIds;

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getRootIds()
	{
		return $this->arrRootIds;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getClipboard()
	{
		return $this->objClipboard;
	}

	/**
	 * {@inheritdoc}
	 */
	public function setClipboard($objClipboard)
	{
		if (is_null($objClipboard))
		{
			unset($this->objClipboard);
		}
		else
		{
			$this->objClipboard = $objClipboard;
		}

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function setTranslator(TranslatorInterface $translator)
	{
		$this->translator = $translator;

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getTranslator()
	{
		return $this->translator;
	}

	/**
	 * {@inheritdoc}
	 */
	public function setEventPropagator($propagator)
	{
		$this->eventPropagator = $propagator;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getEventPropagator()
	{
		return $this->eventPropagator;
	}
}
