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
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Tristan Lins <tristan.lins@bit3.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2013-2019 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
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
    private string $action;

    /**
     * The id of the parent model.
     *
     * @var ModelIdInterface|null
     */
    private ?ModelIdInterface $parentId;

    /**
     * Create a new instance.
     *
     * @param string                $action   The action being performed.
     * @param ModelIdInterface|null $parentId The id of the parent model (null for no parent).
     *
     * @throws \InvalidArgumentException When the action is not one of create, cut, copy or deep copy.
     */
    public function __construct(string $action, ?ModelIdInterface $parentId)
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

        $this->action   = $action;
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
        return ItemInterface::CREATE === $this->action;
    }

    /**
     * {@inheritdoc}
     */
    public function isCut()
    {
        return ItemInterface::CUT === $this->action;
    }

    /**
     * {@inheritdoc}
     */
    public function isCopy()
    {
        return ItemInterface::COPY === $this->action;
    }

    /**
     * {@inheritdoc}
     */
    public function isDeepCopy()
    {
        return ItemInterface::DEEP_COPY === $this->action;
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
        $thisParent = $this->getParentId();
        $thatParent = $item->getParentId();

        // One has a parent ID, the other not.
        if (($thisParent && !$thatParent) || (!$thisParent && $thatParent)) {
            return false;
        }

        // The parent IDs are not equal.
        if ($thisParent && !$thisParent->equals($thatParent)) {
            return false;
        }

        $thisModel = $this->getModelId();
        $thatModel = $item->getModelId();

        // One has a model ID, the other not.
        if (($thisModel && !$thatModel) || (!$thisModel && $thatModel)) {
            return false;
        }

        // Both have no model id and the data provider is different.
        if ((null === $thisModel) && (null === $thatModel)) {
            return ($this->getDataProviderName() === $item->getDataProviderName());
        }
        assert($thisModel instanceof ModelIdInterface);
        assert($thatModel instanceof ModelIdInterface);
        // The model IDs are not equal
        return $thisModel->equals($thatModel);
    }
}
