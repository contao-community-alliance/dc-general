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

namespace DcGeneral\DataDefinition;

interface PropertyInterface
{
	/**
	 * Return the name of the property.
	 *
	 * @return string
	 */
	public function getName();

	/**
	 * Return the label of the property.
	 *
	 * @return array
	 */
	public function getLabel();

	/**
	 * Retrieve information about a property.
	 *
	 * @return PropertyInterface
	 */
	public function getWidgetType();

	/**
	 * Fetch the evaluation information from the field.
	 *
	 * @return array
	 */
	public function getEvaluation();

	/**
	 * Determinator if search is enabled on this property.
	 *
	 * @return bool
	 */
	public function isSearchable();

	/**
	 * Determinator if filtering may be performed on this property.
	 *
	 * @return bool
	 */
	public function isFilterable();

	/**
	 * Determinator if sorting may be performed on this property.
	 *
	 * @return bool
	 */
	public function isSortable();

	/**
	 * Determinator if the value shall be encrypted.
	 *
	 * @return bool
	 */
	public function isEncrypted();

	/**
	 * Fetch some arbitrary information.
	 *
	 * @param $strKey
	 *
	 * @return mixed
	 */
	public function get($strKey);
}
