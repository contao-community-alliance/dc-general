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

namespace ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\Panel;

/**
 * Interface FilterElementInformationInterface.
 *
 * This interface describes a filter panel element information filtering on a property.
 *
 * @package DcGeneral\DataDefinition\Definition\View\Panel
 */
interface FilterElementInformationInterface extends ElementInformationInterface
{
	/**
	 * Set the name of the property to filter on.
	 *
	 * @param string $propertyName The property to filter on.
	 *
	 * @return FilterElementInformationInterface
	 */
	public function setPropertyName($propertyName);

	/**
	 * Retrieve the name of the property to filter on.
	 *
	 * @return string
	 */
	public function getPropertyName();
}
