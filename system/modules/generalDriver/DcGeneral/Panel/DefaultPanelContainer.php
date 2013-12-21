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

namespace DcGeneral\Panel;

use DcGeneral\Data\ConfigInterface;
use DcGeneral\EnvironmentInterface;

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
