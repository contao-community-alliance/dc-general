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

namespace DcGeneral\Clipboard\Interfaces;

interface Clipboard
{
	/**
	 * Load the content of the clipboard to the given input provider.
	 *
	 * @param \DcGeneral\Interfaces\Environment $objEnvironment
	 *
	 * @return mixed
	 */
	public function loadFrom($objEnvironment);

	/**
	 * Save the content of the clipboard to the given input provider.
	 *
	 * @param \DcGeneral\Interfaces\Environment $objEnvironment
	 *
	 * @return mixed
	 */
	public function saveTo($objEnvironment);

	/**
	 * Clear the content of the clipboard.
	 *
	 * @return Clipboard
	 */
	public function clear();

	/**
	 * Determine if the clipboard is empty.
	 *
	 * @return bool
	 */
	public function isEmpty();

	/**
	 * Determine if the clipboard is not empty.
	 *
	 * @return bool
	 */
	public function isNotEmpty();

	/**
	 * Determine if the content in the clipboard shall be cut.
	 *
	 * @return bool
	 */
	public function isCut();

	/**
	 * Determine if the content in the clipboard shall be copied.
	 *
	 * @return bool
	 */
	public function isCopy();

	/**
	 * Set the clipboard to copy mode and copy the given ids.
	 *
	 * @param array|mixed $ids The id or ids to be copied.
	 *
	 * @return ClipBoard
	 */
	public function copy($ids);

	/**
	 * Set the clipboard to cut mode and cut the given ids.
	 *
	 * @param array|mixed $ids The id or ids to be cut.
	 *
	 * @return ClipBoard
	 */
	public function cut($ids);

	/**
	 * Set the ids contained in the clipboard.
	 *
	 * @param array $arrIds The list of ids.
	 *
	 * @return Clipboard
	 */
	public function setContainedIds($arrIds);

	/**
	 * Retrieve the ids contained in the clipboard.
	 *
	 * @return array
	 */
	public function getContainedIds();

	/**
	 * Set the ids ignored in the clipboard as they would create a circular reference when pasting.
	 *
	 * @param array $arrIds The list of ids.
	 *
	 * @return Clipboard
	 */
	public function setCircularIds($arrIds);

	/**
	 * Retrieve the ids ignored in the clipboard as they would create a circular reference when pasting.
	 *
	 * @return array
	 */
	public function getCircularIds();
}
