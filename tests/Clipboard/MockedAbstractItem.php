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
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2013-2019 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Test\Clipboard;

use ContaoCommunityAlliance\DcGeneral\Clipboard\AbstractItem;
use ContaoCommunityAlliance\DcGeneral\Data\ModelId;
use ContaoCommunityAlliance\DcGeneral\Data\ModelIdInterface;

/**
 * Mocked abstract item to test its methods.
 */
final class MockedAbstractItem extends AbstractItem
{
    /**
     * The model id.
     */
    private ?ModelIdInterface $modelId = null;

    /**
     * The provider name.
     */
    private string|null|ModelIdInterface $providerName;

    /**
     * MockedAbstractItem constructor.
     *
     * @param string                       $action                The clipboard action name.
     * @param ModelIdInterface|null        $parentId              The parent id.
     * @param ModelIdInterface|string|null $modelIdOrProviderName The model id or provider name.
     *
     * @SuppressWarnings(PHPMD.LongVariable)
     */
    public function __construct(
        string $action,
        ?ModelIdInterface $parentId = null,
        ModelIdInterface|string|null $modelIdOrProviderName = null
    ) {
        parent::__construct($action, $parentId);

        if ($modelIdOrProviderName instanceof ModelIdInterface) {
            $this->modelId      = $modelIdOrProviderName;
        } else {
            $this->providerName = $modelIdOrProviderName;
        }
    }

    /**
     * Retrieve the id of the model from this item.
     */
    public function getModelId(): ModelId|ModelIdInterface|null
    {
        return $this->modelId;
    }

    /**
     * Retrieve the provider name of the model from this item.
     */
    public function getDataProviderName(): ModelIdInterface|string|null
    {
        if ($this->modelId) {
            return $this->modelId->getDataProviderName();
        }

        return $this->providerName;
    }

    /**
     * Get the id which identifies the item in the clipboard.
     */
    public function getClipboardId(): string
    {
        if ($this->modelId) {
            return $this->getAction() .
                $this->modelId->getSerialized() .
                (($parentId = $this->getParentId()) ? $parentId->getSerialized() : 'null');
        }
        return $this->getAction() .
               $this->getDataProviderName() .
               (($parentId = $this->getParentId()) ? $parentId->getSerialized() : 'null');
    }
}
