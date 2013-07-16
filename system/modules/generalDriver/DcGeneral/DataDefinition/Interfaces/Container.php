<?php
/**
 * PHP version 5
 * @package    generalDriver
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

namespace DcGeneral\DataDefinition\Interfaces;


interface Container
{
	/**
	 * Return the name of the definition.
	 *
	 * @return string
	 */
	public function getName();

	/**
	 * Retrieve information about a property.
	 *
	 * @param string $strProperty The name of the property.
	 *
	 * @return Property
	 */
	public function getProperty($strProperty);

	/**
	 * Retrieve the names of all defined properties.
	 *
	 * @return string[]
	 */
	public function getPropertyNames();

	/**
	 * Retrieve the panel layout.
	 *
	 * Returns an array of arrays of which each level 1 array is a separate group.
	 *
	 * @return array
	 */
	public function getPanelLayout();

	/**
	 * Retrieve the names of properties to use for secondary sorting.
	 *
	 * @return string[]
	 */
	public function getAdditionalSorting();
}
