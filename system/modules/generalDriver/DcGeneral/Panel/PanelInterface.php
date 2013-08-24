<?php

namespace DcGeneral\Panel;

use DcGeneral\Data\ConfigInterface;
use DcGeneral\DataDefinition\ContainerInterface;

interface PanelInterface extends \IteratorAggregate
{
	/**
	 * Get the parenting container.
	 *
	 * @return PanelContainerInterface
	 */
	public function getContainer();

	/**
	 * Set the parenting container.
	 *
	 * @param ContainerInterface $objContainer The Container to be used as parent.
	 *
	 * @return PanelInterface
	 */
	public function setContainer(PanelContainerInterface $objContainer);

	/**
	 * @param string  $strKey     Name of the panel.
	 *
	 * @param PanelElementInterface $objElement The element instance to add.
	 *
	 * @return mixed
	 */
	public function addElement($strKey, $objElement);

	/**
	 * @param $strKey
	 *
	 * @return PanelElementInterface
	 */
	public function getElement($strKey);

	/**
	 *
	 *
	 * @param ConfigInterface  $objConfig        The config to which the initialization shall be applied to.
	 *
	 * @param PanelElementInterface $objElement The element to be initialized (if any).
	 *
	 * @return void
	 */
	public function initialize(ConfigInterface $objConfig, PanelElementInterface $objElement = null);
}
