<?php

namespace DcGeneral\Panel\Interfaces;

interface SortElement extends Element
{
	/**
	 * Set the default flag to use when no flag has been defined for a certain property.
	 *
	 * @param int $intFlag The flag to use.
	 *
	 * @return SearchElement
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
	 * @param $strPropertyName
	 *
	 * @param $intFlag
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
	 * @param $strPropertyName
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
