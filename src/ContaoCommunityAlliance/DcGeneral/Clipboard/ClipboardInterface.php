<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2015 Contao Community Alliance.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    contao-community-alliance/dc-general
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Tristan Lins <tristan.lins@bit3.de>
 * @copyright  2013-2015 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/LICENSE LGPL-3.0
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Clipboard;

use ContaoCommunityAlliance\DcGeneral\Data\ModelIdInterface;
use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;

/**
 * Interface ClipboardInterface.
 *
 * This interface describes the internal clipboard of DcGeneral.
 * The implementing class will store the values persistent to the input provider stored in the environment.
 */
interface ClipboardInterface
{
    /**
     * Load the content of the clipboard from the input provider stored in the environment.
     *
     * @param EnvironmentInterface $objEnvironment The environment where the input provider will retrieve the
     *                                             values from.
     *
     * @return static
     */
    public function loadFrom($objEnvironment);

    /**
     * Save the content of the clipboard to the input provider stored in the environment.
     *
     * @param EnvironmentInterface $objEnvironment The environment where the input provider will store the
     *                                             values to.
     *
     * @return static
     */
    public function saveTo($objEnvironment);

    /**
     * Push an item to the clipboard.
     *
     * @param ItemInterface $item The item.
     *
     * @return static
     */
    public function push(ItemInterface $item);

    /**
     * Remove an item from the clipboard.
     *
     * @param ItemInterface $item The item.
     *
     * @return static
     */
    public function remove(ItemInterface $item);

    /**
     * Remove an item from the clipboard.
     *
     * @param ModelIdInterface $modelId The model id.
     *
     * @return static
     */
    public function removeById(ModelIdInterface $modelId);

    /**
     * Determine if an item exist.
     *
     * @param ItemInterface $item The item.
     *
     * @return static
     */
    public function has(ItemInterface $item);

    /**
     * Determine if an item for the model id exist.
     *
     * @param ModelIdInterface $modelId The model id.
     *
     * @return static
     */
    public function hasId(ModelIdInterface $modelId);

    /**
     * Get all items from the clipboard.
     *
     * @param FilterInterface|null $filter An item filter.
     *
     * @return ItemInterface[]
     */
    public function fetch(FilterInterface $filter);

    /**
     * Determine if the clipboard is empty.
     *
     * @param FilterInterface|null $filter An item filter.
     *
     * @return bool
     */
    public function isEmpty(FilterInterface $filter);

    /**
     * Determine if the clipboard is not empty.
     *
     * @param FilterInterface|null $filter An item filter.
     *
     * @return bool
     */
    public function isNotEmpty(FilterInterface $filter);

    /**
     * Clear the complete clipboard.
     *
     * @return static
     */
    public function clear();

    // ************************************************** DEPRECATED **************************************************

    /**
     * Clipboard is in copy mode.
     *
     * @deprecated
     */
    const MODE_COPY = 'copy';

    /**
     * Clipboard is in cut mode.
     *
     * @deprecated
     */
    const MODE_CUT = 'cut';

    /**
     * Clipboard is in create mode.
     *
     * @deprecated
     */
    const MODE_CREATE = 'create';

    /**
     * Determine if the content in the clipboard shall be cut.
     *
     * @return bool
     *
     * @deprecated
     */
    public function isCut();

    /**
     * Determine if the content in the clipboard shall be copied.
     *
     * @return bool
     *
     * @deprecated
     */
    public function isCopy();

    /**
     * Determine if the content in the clipboard is a new item to be created.
     *
     * @return bool
     *
     * @deprecated
     */
    public function isCreate();

    /**
     * Set the clipboard to copy mode and copy the given ids.
     *
     * @param array|mixed $ids The id or ids to be copied.
     *
     * @return ClipboardInterface
     *
     * @deprecated
     */
    public function copy($ids);

    /**
     * Set the clipboard to cut mode and cut the given ids.
     *
     * @param array|mixed $ids The id or ids to be cut.
     *
     * @return ClipboardInterface
     *
     * @deprecated
     */
    public function cut($ids);

    /**
     * Set the clipboard to create mode for a child of the given parent data set.
     *
     * @param string $parentId The id of the parent data set.
     *
     * @return ClipboardInterface
     *
     * @deprecated
     */
    public function create($parentId);

    /**
     * Set the ids contained in the clipboard.
     *
     * @param array $arrIds The list of ids.
     *
     * @return ClipboardInterface
     *
     * @deprecated
     */
    public function setContainedIds($arrIds);

    /**
     * Retrieve the ids contained in the clipboard.
     *
     * @return array
     *
     * @deprecated
     */
    public function getContainedIds();

    /**
     * Set the ids ignored in the clipboard as they would create a circular reference when pasting.
     *
     * @param array $arrIds The list of ids.
     *
     * @return ClipboardInterface
     *
     * @deprecated
     */
    public function setCircularIds($arrIds);

    /**
     * Retrieve the ids ignored in the clipboard as they would create a circular reference when pasting.
     *
     * @return array
     *
     * @deprecated
     */
    public function getCircularIds();

    /**
     * Retrieve the current mode of the clipboard.
     *
     * @return string Either cut|paste|mode
     *
     * @deprecated
     */
    public function getMode();

    /**
     * Retrieve the id of the parent item (if any).
     *
     * This is only valid in create mode.
     *
     * @return null|string
     *
     * @deprecated
     */
    public function getParent();
}
