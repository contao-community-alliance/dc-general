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
