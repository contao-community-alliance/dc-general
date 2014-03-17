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
 * This interface describes a sort panel element.
 *
 * @package DcGeneral\Panel
 */
interface SortElementInterface
	extends PanelElementInterface
{
	/**
	 * Set the default flag to use when no flag has been defined for a certain property.
	 *
	 * @param int $intFlag The flag to use.
	 *
	 * @return SearchElementInterface
	 */
	public function setDefaultFlag($intFlag);

	/**
	 * Get the default flag to use when no flag has been defined for a certain property.
	 *
	 * @return int
	 */
	public function getDefaultFlag();

	/**
	 * Add a property for sorting.
	 *
	 * @param string $strPropertyName The property name to enable sorting for.
	 *
	 * @param int    $intFlag         The flag to use for sorting.
	 *
	 * @return mixed
	 */
	public function addProperty($strPropertyName, $intFlag);

	/**
	 * Retrieve the list of properties to allow search on.
	 *
	 * @return string[]
	 */
	public function getPropertyNames();

	/**
	 * Set the selected property for sorting.
	 *
	 * @param string $strPropertyName The property name to mark as selected.
	 *
	 * @return mixed
	 */
	public function setSelected($strPropertyName);

	/**
	 * Return the name of the currently selected property.
	 *
	 * @return string
	 */
	public function getSelected();

	/**
	 * Return the flag of the currently selected property.
	 *
	 * @return int
	 */
	public function getFlag();
	// TODO: wouln't it be nice to also have a direction setting here instead of only the flag?
}
