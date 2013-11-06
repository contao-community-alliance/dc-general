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

namespace DcGeneral\DataDefinition\Section\View\Panel;

interface FilterElementInformationInterface extends ElementInformationInterface
{
	/**
	 * @param string $propertyName The property to filter on.
	 *
	 * @return FilterElementInformationInterface
	 */
	public function setPropertyName($propertyName);

	/**
	 * @return string
	 */
	public function getPropertyName();
}
