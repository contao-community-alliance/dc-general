<?php

namespace DcGeneral\Panel;

use DcGeneral\DataDefinition\ContainerInterface;
use DcGeneral\Data\ConfigInterface;
use DcGeneral\EnvironmentInterface;

interface PanelContainerInterface extends \IteratorAggregate
{
	/**
	 * @return EnvironmentInterface
	 */
	public function getEnvironment();

	/**
	 * @param EnvironmentInterface $objEnvironment The DataContainer to use.
	 *
	 * @return PanelContainerInterface
	 */
	public function setEnvironment(EnvironmentInterface $objEnvironment);

	/**
	 * @param string $strKey  Name of the panel.
	 *
	 * @param PanelInterface $objPanel
	 *
	 * @return PanelContainerInterface
	 */
	public function addPanel($strKey, $objPanel);

	/**
	 * @param $strKey
	 *
	 * @return PanelInterface
	 */
	public function getPanel($strKey);

	/**
	 * Initialize all panels and apply all restrictions to the given Config.
	 *
	 * @param ConfigInterface       $objConfig The data config to be populated with the element values.
	 *
	 * @param PanelElementInterface $objElement The element currently being initialized.
	 *
	 * @return PanelContainerInterface
	 */
	public function initialize(ConfigInterface $objConfig, PanelElementInterface $objElement = null);

	/**
	 * Build the container from the given data container array.
	 *
	 * @param ContainerInterface $objDefinition
	 *
	 * @return PanelContainerInterface
	 */
	public function buildFrom($objDefinition);

	/**
	 * Determinator if the panels should be updated from the InputProvider or not.
	 *
	 * @return bool
	 */
	public function updateValues();
}
