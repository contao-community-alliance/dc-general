<?php

namespace DcGeneral\Panel\Interfaces;

interface LimitElement extends Element
{
	/**
	 * Set the offset to use in this element.
	 *
	 * @param int $intOffset
	 *
	 * @return Element
	 */
	public function setOffset($intOffset);

	/**
	 * Get the offset to use in this element.
	 *
	 * @return int
	 */
	public function getOffset();

	/**
	 * Set the Amount to use in this element.
	 *
	 * @param int $intAmount
	 *
	 * @return Element
	 */
	public function setAmount($intAmount);

	/**
	 * Get the amount to use in this element.
	 *
	 * @return int
	 */
	public function getAmount();
}
