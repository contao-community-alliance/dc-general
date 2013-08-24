<?php

namespace DcGeneral\Panel\Interfaces;

use DcGeneral\Panel\PanelElementInterface;

interface FilterElement extends PanelElementInterface
{
	/**
	 * @param string $strProperty The property to filter on.
	 *
	 * @return FilterElement
	 */
	public function setPropertyName($strProperty);

	/**
	 * @return string
	 */
	public function getPropertyName();

	/**
	 * @param mixed $mixValue The value to filter for.
	 *
	 * @return FilterElement
	 */
	public function setValue($mixValue);

	/**
	 * @return mixed
	 */
	public function getValue();
}
