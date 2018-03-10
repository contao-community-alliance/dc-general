<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2018 Contao Community Alliance.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    contao-community-alliance/dc-general
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Tristan Lins <tristan.lins@bit3.de>
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2013-2018 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
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
     * If an instance with the same clipboard id has already been added it will get overwritten.
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
     * Remove an item from the clipboard by its clipboard id.
     *
     * @param string $clipboardId The clipboard id.
     *
     * @return static
     */
    public function removeByClipboardId($clipboardId);

    /**
     * Determine if an item exist.
     *
     * @param ItemInterface $item The item.
     *
     * @return bool
     */
    public function has(ItemInterface $item);

    /**
     * Determine if an item for the model id exist.
     *
     * @param ModelIdInterface $modelId The model id.
     *
     * @return bool
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
}
