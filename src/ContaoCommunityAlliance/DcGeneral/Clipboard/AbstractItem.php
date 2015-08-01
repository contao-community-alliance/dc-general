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
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function equals(ItemInterface $item)
    {
        // It is exactly the same item.
        if ($this === $item) {
            return true;
        }

        // The actions are not equal.
        if ($this->getAction() !== $item->getAction()) {
            return false;
        }

        // One has a parent ID, the other not.
        if (($this->getParentId() && !$item->getParentId()) || (!$this->getParentId() && $item->getParentId())) {
            return false;
        }

        // The parent IDs are not equal.
        if ($this->getParentId() && !$this->getParentId()->equals($item->getParentId())) {
            return false;
        }

        // One has a model ID, the other not.
        if (($this->getModelId() && !$item->getModelId()) || (!$this->getModelId() && $item->getModelId())) {
            return false;
        }

        // Both have no model id and the data provider is different.
        if (!($this->getModelId() || $item->getModelId())) {
            return ($this->getDataProviderName() === $item->getDataProviderName());
        }

        // The model IDs are not equal
        return $this->getModelId()->equals($item->getModelId());
    }
}
