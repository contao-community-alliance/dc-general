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

/**
 * {@inheritdoc}
 */
class Item implements ItemInterface
{
    /**
     * The item action.
     *
     * @var string
     */
    private $action;

    /**
     * The id of the parent model.
     *
     * @var ModelIdInterface
     */
    private $parentId;

    /**
     * The id of the model.
     *
     * @var ModelIdInterface|null
     */
    private $modelId;

    /**
     * Create a new instance.
     *
     * @param string                $action   The action being performed.
     *
     * @param ModelIdInterface|null $parentId The id of the parent model (null for no parent).
     *
     * @param ModelIdInterface|null $modelId  The id of the model the action covers (may be null for "create" only).
     *
     * @throws \InvalidArgumentException When the action is not one of create, cut, copy or deep copy.
     */
    public function __construct($action, $parentId, $modelId)
    {
        if (
            ItemInterface::CREATE !== $action
            && ItemInterface::CUT !== $action
            && ItemInterface::COPY !== $action
            && ItemInterface::DEEP_COPY !== $action
        ) {
            throw new \InvalidArgumentException(
                '$action must be one of ItemInterface::CREATE, ItemInterface::CUT or ItemInterface::COPY'
            );
        }

        if (null === $modelId && $action !== ItemInterface::CREATE) {
            throw new \InvalidArgumentException(
                '$modelId must be valid unless in create action.'
            );
        }

        $this->action   = (string) $action;
        $this->parentId = $parentId;
        $this->modelId  = $modelId;
    }

    /**
     * {@inheritdoc}
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * {@inheritdoc}
     */
    public function isCreate()
    {
        return ItemInterface::CREATE == $this->action;
    }

    /**
     * {@inheritdoc}
     */
    public function isCut()
    {
        return ItemInterface::CUT == $this->action;
    }

    /**
     * {@inheritdoc}
     */
    public function isCopy()
    {
        return ItemInterface::COPY == $this->action;
    }

    /**
     * {@inheritdoc}
     */
    public function isDeepCopy()
    {
        return ItemInterface::DEEP_COPY == $this->action;
    }

    /**
     * {@inheritdoc}
     */
    public function getParentId()
    {
        return $this->parentId;
    }

    /**
     * {@inheritdoc}
     */
    public function getModelId()
    {
        return $this->modelId;
    }

    /**
     * {@inheritdoc}
     */
    public function equals(ItemInterface $item)
    {
        // It is exactly the same item
        if ($this === $item) {
            return true;
        }

        return !(
            // The actions are not equal
            $this->getAction() !== $item->getAction()
            // One have a parent ID, the other not
            || $this->getParentId() && !$item->getParentId()
            || !$this->getParentId() && $item->getParentId()
            // The parent IDs are not equal
            || (
                $this->getParentId()
                && !$this->getParentId()->equals($item->getParentId())
            )
            // The model IDs are not equal
            || !$this->getModelId()->equals($item->getModelId())
        );
    }
}
