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
	 * @var InputProviderInterface
	 */
	protected $objInputProvider;

	/**
	 *
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
	 * @var ModelInterface
	 */
	protected $objModel;

	/**
	 * @var \DcGeneral\Clipboard\ClipboardInterface
	 */
	protected $objClipboard;

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
	}
}
