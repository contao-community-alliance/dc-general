<?php

namespace DcGeneral\Panel;

use DcGeneral\Data\ConfigInterface;
use DcGeneral\EnvironmentInterface;
use DcGeneral\InputProviderInterface;

abstract class AbstractElement implements PanelElementInterface
{
	/**
	 * @var PanelInterface
	 */
	protected $objPanel;

	private $objOtherConfig;

	/**
	 * Convenience method to retrieve Environment for this Element.
	 *
	 * @return EnvironmentInterface
	 */
	public function getEnvironment()
	{
		return $this->getPanel()->getContainer()->getEnvironment();
	}

	/**
	 * Convenience method to retrieve input provider for this Element.
	 *
	 * @return InputProviderInterface
	 */
	public function getInputProvider()
	{
		return $this->getEnvironment()->getInputProvider();
	}

	/**
	 * Return the parenting panel.
	 *
	 * @return PanelInterface
	 */
	public function getPanel()
	{
		return $this->objPanel;
	}

	/**
	 * Return the parenting panel.
	 *
	 * @param PanelInterface $objPanel The panel to use as parent.
	 *
	 * @return PanelElementInterface
	 */
	public function setPanel(PanelInterface $objPanel)
	{
		$this->objPanel = $objPanel;
	}

	/**
	 * Let all other elements initialize and apply themselves to this config.
	 *
	 * @return ConfigInterface
	 */
	protected function getOtherConfig()
	{
		if (!isset($this->objOtherConfig))
		{
			$this->objOtherConfig = $this
				->getEnvironment()
				->getController()
				->getBaseConfig();

			$this
				->getPanel()
				->getContainer()
				->initialize($this->objOtherConfig, $this);
		}

		return $this->objOtherConfig;
	}
}
