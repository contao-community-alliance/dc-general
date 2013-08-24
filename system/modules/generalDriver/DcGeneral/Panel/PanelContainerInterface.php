<?php

namespace DcGeneral\Panel;

use DcGeneral\DataDefinition\ContainerInterface;
use DcGeneral\DataContainerInterface;
use DcGeneral\Data\ConfigInterface;

interface PanelContainerInterface extends \IteratorAggregate
{
	/**
	 * @return DataContainerInterface
	 */
	public function getDataContainer();

	/**
	 * @param DataContainerInterface $objDataContainer The DataContainer to use.
	 *
	 * @return PanelContainerInterface
	 */
	public function setDataContainer(DataContainerInterface $objDataContainer);

	/**
	 * @param string $strKey  Name of the panel.
	 *
	 * @param Panel $objPanel
	 *
	 * @return PanelContainerInterface
	 */
	public function addPanel($strKey, $objPanel);

	/**
	 * @param $strKey
	 *
	 * @return Panel
	 */
	public function getPanel($strKey);

	/**
	 * Initialize all panels and apply all restrictions to the given Config.
	 *
	 * @param ConfigInterface  $objConfig The data config to be populated with the element values.
	 *
	 * @param Element $objElement The element currently being initialized.
	 *
	 * @return PanelContainerInterface
	 */
	public function initialize(ConfigInterface $objConfig, Element $objElement = null);

	/**
	 * Build the container from the given data container array.
	 *
	 * @param Definition $objDefinition
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
