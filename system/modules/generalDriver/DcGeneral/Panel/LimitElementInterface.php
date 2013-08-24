<?php

namespace DcGeneral\Panel;

interface LimitElementInterface extends PanelElementInterface
{
	/**
	 * Set the offset to use in this element.
	 *
	 * @param int $intOffset
	 *
	 * @return LimitElementInterface
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
	 * @return LimitElementInterface
	 */
	public function setAmount($intAmount);

	/**
	 * Get the amount to use in this element.
	 *
	 * @return int
	 */
	public function getAmount();
}
