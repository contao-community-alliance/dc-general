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
 * This interface describes a filter panel element.
 *
 * @package DcGeneral\Panel
 */
interface FilterElementInterface
	extends PanelElementInterface
{
	/**
	 * Set the property name to filter on.
	 *
	 * @param string $strProperty The property to filter on.
	 *
	 * @return FilterElementInterface
	 */
	public function setPropertyName($strProperty);

	/**
	 * Retrieve the property name to filter on.
	 *
	 * @return string
	 */
	public function getPropertyName();

	/**
	 * Set the value to filter for.
	 *
	 * @param mixed $mixValue The value to filter for.
	 *
	 * @return FilterElementInterface
	 */
	public function setValue($mixValue);

	/**
	 * Retrieve the value to filter for.
	 *
	 * @return mixed
	 */
	public function getValue();
}
