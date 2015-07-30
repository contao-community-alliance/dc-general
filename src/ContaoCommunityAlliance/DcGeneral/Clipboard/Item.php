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

use ContaoCommunityAlliance\DcGeneral\Data\ModelIdInterface;

/**
 * {@inheritdoc}
 */
class Item extends AbstractItem
{
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
        parent::__construct($action, $parentId);

        if (!$modelId instanceof ModelIdInterface) {
            throw new \InvalidArgumentException('Invalid $modelId given.');
        }

        $this->modelId = $modelId;
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
    public function getDataProviderName()
    {
        return $this->modelId->getDataProviderName();
    }

    /**
     * {@inheritdoc}
     */
    public function getClipboardId()
    {
        return $this->modelId->getSerialized();
    }
}
