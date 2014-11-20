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

use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\IdSerializer;

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
     * @var IdSerializer
     */
    private $parentId;

    /**
     * The id of the model.
     *
     * @var IdSerializer
     */
    private $modelId;

    function __construct($action, IdSerializer $parentId = null, IdSerializer $modelId)
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
