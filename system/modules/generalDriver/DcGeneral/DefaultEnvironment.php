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

use DcGeneral\EnvironmentInterface;
use DcGeneral\Controller\ControllerInterface;
use DcGeneral\Exception\DcGeneralRuntimeException;
use DcGeneral\InputProviderInterface;
use DcGeneral\Panel\PanelContainerInterface;


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
	protected $arrDataDriver;

	/**
	 * @var \DcGeneral\Callbacks\CallbacksInterface
	 */
	protected $objCallbackHandler;

	/**
	 * @var PanelContainerInterface
	 */
	protected $objPanelContainer;

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
	protected $objTranslationManager;

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
	public function getDataDriver($strSource = null)
	{
		if ($strSource === null)
		{
			$strSource = $this->getDataDefinition()->getName();
		}

		// FIXME: this is deprecated stuff from 0.9 times, we can drop it safely I guess.
		switch ($strSource)
		{
			case 'self':
				$strSource = $this->getDataDefinition()->getName();
				trigger_error('WARNING!!!! Legacy descriptor "self" used for data provider retrieval, expect this to fail in near future.', E_USER_WARNING);
				break;
			case 'parent':
				if ($GLOBALS['TL_DCA'][$this->getDataDefinition()->getName()]['config']['ptable'])
				{
					$strSource = $GLOBALS['TL_DCA'][$this->getDataDefinition()->getName()]['config']['ptable'];
				}
				elseif ($GLOBALS['TL_DCA'][$this->getDataDefinition()->getName()]['dca_config']['data_provider']['parent']['source'])
				{
					$strSource = $GLOBALS['TL_DCA'][$this->getDataDefinition()->getName()]['dca_config']['data_provider']['parent']['source'];
				}
				else
					throw new DcGeneralRuntimeException('Could not determine parent table.');

				trigger_error('WARNING!!!! Legacy descriptor "parent" used for data provider retrieval, expect this to fail in near future.', E_USER_WARNING);
				break;
		}


		if (isset($this->arrDataDriver[$strSource]))
		{
			return $this->arrDataDriver[$strSource];
		}

		throw new DcGeneralRuntimeException(sprintf('Data driver %s not defined', $strSource));
	}

	/**
	 * {@inheritdoc}
	 */
	public function addDataDriver($strSource, $objDriver)
	{
		// Force removal of an potentially registered driver to ease sub-classing.
		$this->removeDataDriver($strSource);

		$this->arrDataDriver[$strSource] = $objDriver;

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function removeDataDriver($strSource)
	{
		if (isset($this->arrDataDriver[$strSource]))
		{
			unset($this->arrDataDriver[$strSource]);
		}

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function setPanelContainer($objPanelContainer)
	{
		$this->objPanelContainer = $objPanelContainer;

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getPanelContainer()
	{
		return $this->objPanelContainer;
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
	public function setTranslationManager($manager)
	{
		$this->objTranslationManager = $manager;

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getTranslationManager()
	{
		return $this->objTranslationManager;
	}
}
