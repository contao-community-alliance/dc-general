<?php

/**
 * PHP version 5
 *
 * @package    generalDriver
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Clipboard;

use ContaoCommunityAlliance\DcGeneral\Data\ModelIdInterface;

/**
 * {@inheritdoc}
 */
abstract class AbstractItem implements ItemInterface
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
     * Create a new instance.
     *
     * @param string                $action   The action being performed.
     *
     * @param ModelIdInterface|null $parentId The id of the parent model (null for no parent).
     *
     * @throws \InvalidArgumentException When the action is not one of create, cut, copy or deep copy.
     */
    public function __construct($action, $parentId)
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

        $this->action   = (string) $action;
        $this->parentId = $parentId;
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

            // One have a model ID, the other not
            || $this->getModelId() && !$item->getModelId()
            || !$this->getModelId() && $item->getModelId()

            // Both has no model id.
            || (
                !($this->getModelId() || $item->getModelId())
                && ($this->getDataProviderName() !== $item->getDataProviderName())
            )

            // The model IDs are not equal
            || !$this->getModelId()->equals($item->getModelId())
        );
    }
}
