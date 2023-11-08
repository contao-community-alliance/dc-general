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
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Tristan Lins <tristan.lins@bit3.de>
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2013-2019 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Clipboard;

use ContaoCommunityAlliance\DcGeneral\Data\ModelIdInterface;

use function base64_decode;
use function base64_encode;
use function serialize;
use function unserialize;

/**
 * Class Clipboard.
 *
 * Default implementation of the clipboard.
 *
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class Clipboard implements ClipboardInterface
{
    /**
     * The item collection (indexed by clipboard ids).
     *
     * @var array<string, ItemInterface>
     */
    private array $items = [];

    /**
     * The item collection (indexed by model ids).
     *
     * @var array<string, array<string, ItemInterface>>
     */
    private array $itemsByModelId = [];

    /**
     * {@inheritDoc}
     */
    public function loadFrom($environment)
    {
        if (null === $session = $environment->getSessionStorage()) {
            return $this;
        }

        $data = $session->get('CLIPBOARD');

        if ($data) {
            $this->items = unserialize(base64_decode($data), ['allowed_classes' => true]);
            foreach ($this->items as $item) {
                if ($modelId = $item->getModelId()) {
                    $this->itemsByModelId[$modelId->getSerialized()][$item->getClipboardId()] = $item;
                }
            }
        }

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function saveTo($environment)
    {
        if (null === $session = $environment->getSessionStorage()) {
            return $this;
        }

        $data = base64_encode(serialize($this->items));
        $session->set('CLIPBOARD', $data);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function push(ItemInterface $item)
    {
        $clipboardId = $item->getClipboardId();

        $this->items[$clipboardId] = $item;

        if ($modelId = $item->getModelId()) {
            $this->itemsByModelId[$modelId->getSerialized()][$clipboardId] = $item;
        }

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function remove(ItemInterface $item)
    {
        $clipboardId = $item->getClipboardId();

        unset($this->items[$clipboardId]);

        if ($modelId = $item->getModelId()) {
            unset($this->itemsByModelId[$modelId->getSerialized()][$clipboardId]);
        }

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function removeById(ModelIdInterface $modelId)
    {
        $serializedId = $modelId->getSerialized();
        if (!empty($this->itemsByModelId[$serializedId])) {
            foreach ($this->itemsByModelId[$serializedId] as $item) {
                unset($this->items[$item->getClipboardId()]);
            }

            unset($this->itemsByModelId[$serializedId]);
        }

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function removeByClipboardId($clipboardId)
    {
        if (isset($this->items[$clipboardId])) {
            if ($modelId = $this->items[$clipboardId]->getModelId()) {
                unset($this->itemsByModelId[$modelId->getSerialized()][$clipboardId]);
            }
            unset($this->items[$clipboardId]);
        }

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function has(ItemInterface $item)
    {
        $clipboardId = $item->getClipboardId();

        if (!isset($this->items[$clipboardId])) {
            return false;
        }

        $existingItem = $this->items[$clipboardId];

        return $existingItem->equals($item);
    }

    /**
     * {@inheritDoc}
     */
    public function hasId(ModelIdInterface $modelId)
    {
        return !empty($this->itemsByModelId[$modelId->getSerialized()]);
    }

    /**
     * {@inheritDoc}
     */
    public function fetch(FilterInterface $filter)
    {
        $items = [];

        foreach ($this->items as $item) {
            if ($filter->accepts($item)) {
                $items[] = $item;
            }
        }

        return $items;
    }

    /**
     * {@inheritDoc}
     */
    public function isEmpty(FilterInterface $filter)
    {
        foreach ($this->items as $item) {
            if ($filter->accepts($item)) {
                return false;
            }
        }

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function isNotEmpty(FilterInterface $filter)
    {
        return !$this->isEmpty($filter);
    }

    /**
     * {@inheritDoc}
     */
    public function clear()
    {
        $this->items = [];

        return $this;
    }
}
