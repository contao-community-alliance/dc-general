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

namespace DcGeneral\Clipboard;

interface ClipboardInterface
{
	/**
	 * Load the content of the clipboard to the given input provider.
	 *
	 * @param \DcGeneral\EnvironmentInterface $objEnvironment
	 *
	 * @return mixed
	 */
	public function loadFrom($objEnvironment);

	/**
	 * Save the content of the clipboard to the given input provider.
	 *
	 * @param \DcGeneral\EnvironmentInterface $objEnvironment
	 *
	 * @return mixed
	 */
	public function saveTo($objEnvironment);

	/**
	 * Clear the content of the clipboard.
	 *
	 * @return ClipboardInterface
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
	 * Determine if the content in the clipboard is a new item to be created.
	 *
	 * @return bool
	 */
	public function isCreate();

	/**
	 * Set the clipboard to copy mode and copy the given ids.
	 *
	 * @param array|mixed $ids The id or ids to be copied.
	 *
	 * @return ClipboardInterface
	 */
	public function copy($ids);

	/**
	 * Set the clipboard to cut mode and cut the given ids.
	 *
	 * @param array|mixed $ids The id or ids to be cut.
	 *
	 * @return ClipboardInterface
	 */
	public function cut($ids);

	/**
	 * Set the clipboard to create mode for a child of the given parent dataset.
	 *
	 * @param mixed $parentId The id of the parent dataset.
	 *
	 * @return ClipboardInterface
	 */
	public function create($parentId);

	/**
	 * Set the ids contained in the clipboard.
	 *
	 * @param array $arrIds The list of ids.
	 *
	 * @return ClipboardInterface
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
	 * @return ClipboardInterface
	 */
	public function setCircularIds($arrIds);

	/**
	 * Retrieve the ids ignored in the clipboard as they would create a circular reference when pasting.
	 *
	 * @return array
	 */
	public function getCircularIds();

	/**
	 * @return string Either cut|paste|mode
	 */
	public function getMode();
}
