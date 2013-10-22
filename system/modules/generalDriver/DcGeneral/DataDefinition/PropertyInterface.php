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
	 * Return the widget type name.
	 *
	 * @return string
	 */
	public function getWidgetType();

	/**
	 * Fetch the evaluation information from the field.
	 *
	 * @return array
	 */
	public function getEvaluation();

	/**
	 * Retrieve the sorting flag, this overrides the flag from the data container when sorting is switched to this property.
	 *
	 *  1 Sort by initial letter ascending
	 *  2 Sort by initial letter descending
	 *  3 Sort by initial X letters ascending (see length)
	 *  4 Sort by initial X letters descending (see length)
	 *  5 Sort by day ascending
	 *  6 Sort by day descending
	 *  7 Sort by month ascending
	 *  8 Sort by month descending
	 *  9 Sort by year ascending
	 * 10 Sort by year descending
	 * 11 Sort ascending
	 * 12 Sort descending
	 *
	 * @return int
	 */
	public function getSortingFlag();

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
