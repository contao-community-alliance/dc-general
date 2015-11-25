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
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @copyright  2013-2015 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0
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
        return $this->getAction() .
            ($this->modelId ? $this->modelId->getSerialized() : 'null') .
            (($parentId = $this->getParentId()) ? $parentId->getSerialized() : 'null');
    }
}
