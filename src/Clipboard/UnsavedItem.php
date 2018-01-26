<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2018 Contao Community Alliance.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    contao-community-alliance/dc-general
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2013-2018 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
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
     * @var string
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
    public function getDataProviderName()
    {
        return $this->providerName;
    }

    /**
     * {@inheritdoc}
     */
    public function getClipboardId()
    {
        return $this->getAction() .
            $this->getDataProviderName() .
            (($parentId = $this->getParentId()) ? $parentId->getSerialized() : 'null');
    }
}
