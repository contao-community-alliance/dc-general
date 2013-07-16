<?php

namespace DcGeneral\Panel\Interfaces;

use DcGeneral\Panel\Interfaces\Element;

interface FilterElement extends Element
{
	/**
	 * @param string $strProperty The property to filter on.
	 *
	 * @return Element
	 */
	public function setPropertyName($strProperty);

	/**
	 * @return string
	 */
	public function getPropertyName();

	/**
	 * @param mixed $mixValue The value to filter for.
	 *
	 * @return Element
	 */
	public function setValue($mixValue);

	/**
	 * @return mixed
	 */
	public function getValue();
}
