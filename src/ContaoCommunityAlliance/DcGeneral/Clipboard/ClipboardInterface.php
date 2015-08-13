<?php
/**
 * PHP version 5
 *
 * @package    generalDriver
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Tristan Lins <tristan.lins@bit3.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Clipboard;

use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;

/**
 * Interface ClipboardInterface.
 *
 * This interface describes the internal clipboard of DcGeneral.
 * The implementing class will store the values persistent to the input provider stored in the environment.
 *
 * @package DcGeneral\Clipboard
 */
interface ClipboardInterface
{
    /**
     * Clipboard is in copy mode.
     */
    const MODE_COPY = 'copy';

    /**
     * Clipboard is in cut mode.
     */
    const MODE_CUT = 'cut';

    /**
     * Clipboard is in create mode.
     */
    const MODE_CREATE = 'create';

    /**
     * Load the content of the clipboard from the input provider stored in the environment.
     *
     * @param EnvironmentInterface $objEnvironment The environment where the input provider will retrieve the
     *                                             values from.
     *
     * @return ClipboardInterface
     */
    public function loadFrom($objEnvironment);

    /**
     * Save the content of the clipboard to the input provider stored in the environment.
     *
     * @param EnvironmentInterface $objEnvironment The environment where the input provider will store the
     *                                             values to.
     *
     * @return ClipboardInterface
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
     * Set the clipboard to create mode for a child of the given parent data set.
     *
     * @param string $parentId The id of the parent data set.
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
     * Retrieve the current mode of the clipboard.
     *
     * @return string Either cut|paste|mode
     */
    public function getMode();

    /**
     * Retrieve the id of the parent item (if any).
     *
     * This is only valid in create mode.
     *
     * @return null|string
     */
    public function getParent();
}
