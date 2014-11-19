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

/**
 * Class Clipboard.
 *
 * Default implementation of the clipboard.
 *
 * @package DcGeneral\Clipboard
 */
class Clipboard implements ClipboardInterface
{
    /**
     * The item collection.
     *
     * @var ItemInterface[]
     */
    private $items = array();

    /**
     * {@inheritDoc}
     */
    public function loadFrom($objEnvironment)
    {
        $data = $objEnvironment->getSessionStorage()->get('CLIPBOARD');

        if ($data) {
            // FIXME use another serialisation method
            $this->items = unserialize(base64_decode($data));
        }

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function saveTo($objEnvironment)
    {
        // FIXME use another serialisation method
        $data = base64_encode(serialize($this->items));
        $objEnvironment->getSessionStorage()->set('CLIPBOARD', $data);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function push(ItemInterface $item)
    {
        $serializedId = $item->getModelId()->getSerialized();

        $this->items[$serializedId] = $item;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function remove(ItemInterface $item)
    {
        $serializedId = $item->getModelId()->getSerialized();

        unset($this->items[$serializedId]);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function fetch($modelProviderName = null, $parentProviderName = false, $parentModelId = false)
    {
        $items = array();

        foreach ($this->items as $item) {
            if ($this->isItemAccepted($item, $modelProviderName, $parentProviderName, $parentModelId)) {
                $items[] = $item;
            }
        }

        return $items;
    }

    /**
     * {@inheritDoc}
     */
    public function isEmpty($modelProviderName = null, $parentProviderName = false, $parentModelId = false)
    {
        foreach ($this->items as $item) {
            if ($this->isItemAccepted($item, $modelProviderName, $parentProviderName, $parentModelId)) {
                return false;
            }
        }

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function isNotEmpty($modelProviderName = null, $parentProviderName = false, $parentModelId = false)
    {
        return !$this->isEmpty($modelProviderName, $parentProviderName, $parentModelId);
    }

    /**
     * {@inheritDoc}
     */
    public function clear()
    {
        $this->items = array();

        return $this;
    }

    /**
     * Determine if an item is accepted by the filter settings.
     *
     * @param ItemInterface    $item               The item object.
     * @param string|null      $modelProviderName  The model provider name.
     * @param string|null|bool $parentProviderName The parent model provider name.
     * @param string|null|bool $parentModelId      The parent model id.
     *
     * @return bool
     */
    private function isItemAccepted(
        ItemInterface $item,
        $modelProviderName = null,
        $parentProviderName = false,
        $parentModelId = false
    ) {
        if (null !== $modelProviderName) {
            if ($modelProviderName !== $item->getModelId()->getDataProviderName()) {
                // items model provider name, does not match the required name.
                return false;
            }
        }
        if (false !== $parentProviderName) {
            $parentId = $item->getParentId();
            if ($parentId) {
                if ($parentId->getDataProviderName() !== $parentProviderName) {
                    // items parent data provider name does not match the required name.
                    return false;
                }

                if (false !== $parentModelId) {
                    if ($parentId->getId() !== $parentModelId) {
                        // items parent id does not match the required id.
                        return false;
                    }
                }
            } else {
                if (null !== $parentProviderName) {
                    // item has no parent, but a parent provider name is required.
                    return false;
                }

                if (false !== $parentModelId) {
                    // items has no parent, but a parent id is required.
                    return false;
                }
            }
        }

        return true;
    }

    // ************************************************** DEPRECATED **************************************************

    /**
     * The ids contained.
     *
     * @var array
     *
     * @deprecated
     */
    protected $arrIds = array();

    /**
     * The ids that will create a circular reference and therefore shall get ignored for pasting.
     *
     * @var array
     *
     * @deprecated
     */
    protected $arrCircularIds = array();

    /**
     * The current mode the clipboard is in.
     *
     * @var string
     *
     * @deprecated
     */
    protected $mode;

    /**
     * The id of the parent element for create mode.
     *
     * @var string
     *
     * @deprecated
     */
    protected $parentId;

    /**
     * {@inheritDoc}
     *
     * @deprecated
     */
    public function isCut()
    {
        return $this->mode == self::MODE_CUT;
    }

    /**
     * {@inheritDoc}
     *
     * @deprecated
     */
    public function isCopy()
    {
        return $this->mode == self::MODE_COPY;
    }

    /**
     * {@inheritDoc}
     *
     * @deprecated
     */
    public function isCreate()
    {
        return $this->mode == self::MODE_CREATE;
    }

    /**
     * {@inheritDoc}
     *
     * @deprecated
     */
    public function copy($ids)
    {
        $this->mode = self::MODE_COPY;

        if (is_array($ids) || ($ids === null)) {
            $this->setContainedIds($ids);
        } else {
            $this->setContainedIds(array($ids));
        }

        return $this;
    }

    /**
     * {@inheritDoc}
     *
     * @deprecated
     */
    public function cut($ids)
    {
        $this->mode = self::MODE_CUT;

        if (is_array($ids) || ($ids === null)) {
            $this->setContainedIds($ids);
        } else {
            $this->setContainedIds(array($ids));
        }

        return $this;
    }

    /**
     * {@inheritDoc}
     *
     * @deprecated
     */
    public function create($parentId)
    {
        $this->mode = self::MODE_CREATE;

        $this->setContainedIds(array(null));
        $this->parentId = $parentId;

        return $this;
    }

    /**
     * {@inheritDoc}
     *
     * @deprecated
     */
    public function setContainedIds($arrIds)
    {
        $this->arrIds = $arrIds;

        return $this;
    }

    /**
     * {@inheritDoc}
     *
     * @deprecated
     */
    public function getContainedIds()
    {
        return $this->arrIds;
    }

    /**
     * {@inheritDoc}
     *
     * @deprecated
     */
    public function setCircularIds($arrIds)
    {
        $this->arrCircularIds = (array) $arrIds;

        return $this;
    }

    /**
     * {@inheritDoc}
     *
     * @deprecated
     */
    public function getCircularIds()
    {
        return $this->arrCircularIds;
    }

    /**
     * {@inheritdoc}
     *
     * @deprecated
     */
    public function getMode()
    {
        return $this->mode;
    }

    /**
     * {@inheritDoc}
     *
     * @deprecated
     */
    public function getParent()
    {
        return $this->isCreate() ? $this->parentId : null;
    }
}
