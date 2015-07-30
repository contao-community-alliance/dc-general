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
 * UnsavedItem is designed for new items being created which does not have an id yet.
 *
 * @package ContaoCommunityAlliance\DcGeneral\Clipboard
 */
class UnsavedItem extends AbstractItem
{
    /**
     * The provider name.
     *
     * @type string
     */
    private $providerName;

    /**
     * Create a new instance.
     *
     * @param string                $action       The action being performed.
     *
     * @param ModelIdInterface|null $parentId     The id of the parent model (null for no parent).
     *
     * @param string                $providerName The provider name of the item being created.
     *
     * @throws \InvalidArgumentException When the action is not create.
     */
    public function __construct($action, $parentId, $providerName)
    {
        parent::__construct($action, $parentId);

        if ($action !== ItemInterface::CREATE) {
            throw new \InvalidArgumentException('UnsavedItem is designed for create actions only.');
        }

        $this->providerName = $providerName;
    }

    /**
     * {@inheritdoc}
     */
    public function getModelId()
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getProviderName()
    {
        return $this->providerName;
    }

    /**
     * {@inheritdoc}
     */
    public function getClipboardId()
    {
        return $this->getProviderName();
    }
}
