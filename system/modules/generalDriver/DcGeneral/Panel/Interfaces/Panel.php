<?php

namespace DcGeneral\Panel\Interfaces;

use DcGeneral\Data\Interfaces\Config;

interface Panel extends \IteratorAggregate
{
	/**
	 * Get the parenting container.
	 *
	 * @return Container
	 */
	public function getContainer();

	/**
	 * Set the parenting container.
	 *
	 * @param Container $objContainer The Container to be used as parent.
	 *
	 * @return Panel
	 */
	public function setContainer(Container $objContainer);

	/**
	 * @param string  $strKey     Name of the panel.
	 *
	 * @param Element $objElement The element instance to add.
	 *
	 * @return mixed
	 */
	public function addElement($strKey, $objElement);

	/**
	 * @param $strKey
	 *
	 * @return Element
	 */
	public function getElement($strKey);

	/**
	 *
	 *
	 * @param Config  $objConfig        The config to which the initialization shall be applied to.
	 *
	 * @param Element $objElement The element to be initialized (if any).
	 *
	 * @return void
	 */
	public function initialize(Config $objConfig, Element $objElement = null);
}
