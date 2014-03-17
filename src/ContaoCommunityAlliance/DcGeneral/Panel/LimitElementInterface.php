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

namespace ContaoCommunityAlliance\DcGeneral\Panel;

/**
 * This interface describes a panel limit element.
 *
 * @package DcGeneral\Panel
 */
interface LimitElementInterface extends PanelElementInterface
{
	/**
	 * Set the offset to use in this element.
	 *
	 * @param int $intOffset The offset.
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
	 * @param int $intAmount The amount.
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
