<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2019 Contao Community Alliance.
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
 * @copyright  2013-2019 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Clipboard;

use ContaoCommunityAlliance\DcGeneral\Data\ModelId;

/**
 * Interface ItemInterface.
 *
 * A single clipboard action item.
 *
 * The item is designed to be immutable! A mutable implementation would probably leads to unexpected behaviour.
 *
 * @package DcGeneral\Clipboard
 */
interface ItemInterface
{
    /**
     * Item is in create action.
     */
    public const CREATE = 'create';

    /**
     * Item is in cut action.
     */
    public const CUT = 'cut';

    /**
     * Item is in copy action.
     */
    public const COPY = 'copy';

    /**
     * Item is in deep copy action.
     */
    public const DEEP_COPY = 'deepcopy';

    /**
     * Retrieve the current action of the clipboard.
     *
     * @return string One of ItemInterface::CREATE, ItemInterface::CUT, ItemInterface::COPY
     */
    public function getAction();

    /**
     * Determine if the content in the clipboard is a new item to be created.
     *
     * @return bool
     */
    public function isCreate();

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
     * Determine if the content in the clipboard shall be copied with all children.
     *
     * @return bool
     */
    public function isDeepCopy();

    /**
     * Retrieve the id of the parent model from this item.
     *
     * @return ModelId|null
     */
    public function getParentId();

    /**
     * Retrieve the id of the model from this item.
     *
     * @return ModelId|null
     */
    public function getModelId();

    /**
     * Retrieve the provider name of the model from this item.
     *
     * @return string
     */
    public function getDataProviderName();

    /**
     * Get the id which identifies the item in the clipboard.
     *
     * @return string
     */
    public function getClipboardId();

    /**
     * Determine if this item, is equals to the other item.
     *
     * @param ItemInterface $item The other item.
     *
     * @return bool
     */
    public function equals(ItemInterface $item);
}
