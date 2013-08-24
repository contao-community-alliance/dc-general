<?php

namespace DcGeneral\Panel;

use DcGeneral\Panel\PanelElementInterface;

interface FilterElementInterface extends PanelElementInterface
{
	/**
	 * @param string $strProperty The property to filter on.
	 *
	 * @return FilterElementInterface
	 */
	public function setPropertyName($strProperty);

	/**
	 * @return string
	 */
	public function getPropertyName();

	/**
	 * @param mixed $mixValue The value to filter for.
	 *
	 * @return FilterElementInterface
	 */
	public function setValue($mixValue);

	/**
	 * @return mixed
	 */
	public function getValue();
}
