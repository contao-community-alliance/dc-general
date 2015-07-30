<?php

/**
 * PHP version 5
 *
 * @package    generalDriver
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Tristan Lins <tristan.lins@bit3.de>
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
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
    const CREATE = 'create';

    /**
     * Item is in cut action.
     */
    const CUT = 'cut';

    /**
     * Item is in copy action.
     */
    const COPY = 'copy';

    /**
     * Item is in deep copy action.
     */
    const DEEP_COPY = 'deepcopy';

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
