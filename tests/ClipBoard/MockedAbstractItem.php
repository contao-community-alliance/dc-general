<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2017 Contao Community Alliance.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    contao-community-alliance/dc-general
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2013-2017 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Test\ClipBoard;

use ContaoCommunityAlliance\DcGeneral\Clipboard\AbstractItem;
use ContaoCommunityAlliance\DcGeneral\Data\ModelId;
use ContaoCommunityAlliance\DcGeneral\Data\ModelIdInterface;

/**
 * Mocked abstract item to test its methods.
 *
 * @package ContaoCommunityAlliance\DcGeneral\Test\ClipBoard
 */
class MockedAbstractItem extends AbstractItem
{
    /**
     * The model id.
     *
     * @var ModelIdInterface
     */
    private $modelId;

    /**
     * The provider name.
     *
     * @var string
     */
    private $providerName;

    /**
     * MockedAbstractItem constructor.
     *
     * @param string                       $action                The clipboard action name.
     * @param ModelIdInterface|null        $parentId              The parent id.
     * @param ModelIdInterface|string|null $modelIdOrProviderName The model id or provider name.
     */
    public function __construct($action, ModelIdInterface $parentId = null, $modelIdOrProviderName = null)
    {
        parent::__construct($action, $parentId);

        if ($modelIdOrProviderName instanceof ModelIdInterface) {
            $this->modelId      = $modelIdOrProviderName;
        } else {
            $this->providerName = $modelIdOrProviderName;
        }
    }

    /**
     * Retrieve the id of the model from this item.
     *
     * @return ModelId|null
     */
    public function getModelId()
    {
        return $this->modelId;
    }

    /**
     * Retrieve the provider name of the model from this item.
     *
     * @return string
     */
    public function getDataProviderName()
    {
        if ($this->modelId) {
            return $this->modelId->getDataProviderName();
        }

        return $this->providerName;
    }

    /**
     * Get the id which identifies the item in the clipboard.
     *
     * @return string
     */
    public function getClipboardId()
    {
        if ($this->modelId) {
            return $this->getAction() .
            $this->modelId->getSerialized() .
            (($parentId = $this->getParentId()) ? $parentId->getSerialized() : 'null');
        } else {
            return $this->getAction() .
            $this->getDataProviderName() .
            (($parentId = $this->getParentId()) ? $parentId->getSerialized() : 'null');
        }
    }
}
