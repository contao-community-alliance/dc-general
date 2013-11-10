<?php

namespace DcGeneral\Panel;

use DcGeneral\DataDefinition\ContainerInterface;
use DcGeneral\Data\ConfigInterface;
use DcGeneral\DataDefinition\Definition\BackendViewDefinitionInterface;
use DcGeneral\DataDefinition\Definition\View\Panel\FilterElementInformationInterface;
use DcGeneral\DataDefinition\Definition\View\Panel\SortElementInformationInterface;
use DcGeneral\EnvironmentInterface;
use DcGeneral\Exception\DcGeneralRuntimeException;

class DefaultPanelContainer implements PanelContainerInterface
{
	/**
	 * @var EnvironmentInterface
	 */
	protected $objEnvironment;

	/**
	 * @var PanelInterface[]
	 */
	protected $arrPanels = array();

	/**
	 * {@inheritdoc}
	 */
	public function getEnvironment()
	{
		return $this->objEnvironment;
	}
	/**
	 * {@inheritdoc}
	 */
	public function setEnvironment(EnvironmentInterface $objEnvironment)
	{
		$this->objEnvironment = $objEnvironment;
		return $this;
	}

	/**
	 * @param string $strKey  Name of the panel.
	 *
	 * @param PanelInterface $objPanel
	 *
	 * @return mixed
	 */
	public function addPanel($strKey, $objPanel)
	{
		$this->arrPanels[$strKey] = $objPanel;
		$objPanel->setContainer($this);

		return $this;
	}

	/**
	 * @param $strKey
	 *
	 * @return PanelInterface
	 */
	public function getPanel($strKey)
	{
		return $this->arrPanels[$strKey];
	}

	public function initialize(ConfigInterface $objConfig, PanelElementInterface $objElement = null)
	{
		/** @var PanelInterface $objPanel */
		foreach ($this as $objPanel)
		{
			$objPanel->initialize($objConfig, $objElement);
		}
	}

	public function updateValues()
	{
		return ($this->getEnvironment()->getInputProvider()->getValue('FORM_SUBMIT') === 'tl_filters');
	}

	/**
	 * {@inheritdoc}
	 */
	public function getIterator()
	{
		return new \ArrayIterator($this->arrPanels);
	}
}
