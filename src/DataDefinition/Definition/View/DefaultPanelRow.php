<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2023 Contao Community Alliance.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    contao-community-alliance/dc-general
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Tristan Lins <tristan.lins@bit3.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2013-2023 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View;

use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\Panel\ElementInformationInterface;
use ContaoCommunityAlliance\DcGeneral\Exception\DcGeneralInvalidArgumentException;

/**
 * Default implementation of a panel row.
 */
class DefaultPanelRow implements PanelRowInterface
{
    /**
     * The contained elements.
     *
     * @var list<ElementInformationInterface>
     */
    protected $elements = [];

    /**
     * {@inheritDoc}
     */
    public function getElements()
    {
        $names = [];
        foreach ($this as $element) {
            /** @var ElementInformationInterface $element */
            $names[] = $element->getName();
        }

        return $names;
    }

    /**
     * {@inheritDoc}
     */
    public function addElement(ElementInformationInterface $element, $index = -1)
    {
        if ($this->hasElement($element)) {
            return $this;
        }

        if (($index < 0) || ($this->getCount() <= $index)) {
            $this->elements[] = $element;
            return $this;
        }

        \array_splice($this->elements, $index, 0, [$element]);
        return $this;
    }

    /**
     * {@inheritDoc}
     *
     * @SuppressWarnings(PHPMD.LongVariable)
     */
    public function deleteElement($indexOrNameOrInstance)
    {
        if ($indexOrNameOrInstance instanceof ElementInformationInterface) {
            \array_filter(
                $this->elements,
                function ($element) use ($indexOrNameOrInstance) {
                    /** @var ElementInformationInterface $element */

                    return $element === $indexOrNameOrInstance;
                }
            );
        } elseif (\is_string($indexOrNameOrInstance)) {
            foreach ($this as $index => $element) {
                /** @var ElementInformationInterface $element */
                if ($indexOrNameOrInstance === $element->getName()) {
                    unset($this->elements[$index]);
                    break;
                }
            }
        } else {
            unset($this->elements[$indexOrNameOrInstance]);
        }

        return $this;
    }

    /**
     * {@inheritDoc}
     *
     * @throws DcGeneralInvalidArgumentException When an invalid value for the element name has been passed.
     */
    public function hasElement($instanceOrName)
    {
        if ($instanceOrName instanceof ElementInformationInterface) {
            return \in_array($instanceOrName, $this->elements);
        }

        foreach ($this as $element) {
            /** @var ElementInformationInterface $element */
            if ($instanceOrName === $element->getName()) {
                return true;
            }
        }

        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function getCount()
    {
        return \count($this->elements);
    }

    /**
     * {@inheritDoc}
     *
     * @throws DcGeneralInvalidArgumentException When an invalid value for the element name has been passed or the
     *                                           index is out of bounds.
     */
    public function getElement($indexOrName)
    {
        if (\is_string($indexOrName)) {
            foreach ($this as $element) {
                /** @var ElementInformationInterface $element */
                if ($indexOrName === $element->getName()) {
                    return $element;
                }
            }
            throw new DcGeneralInvalidArgumentException('Value out of bounds: ' . $indexOrName . '.');
        }

        if (!isset($this->elements[$indexOrName])) {
            throw new DcGeneralInvalidArgumentException('Value out of bounds: ' . $indexOrName . '.');
        }

        return $this->elements[$indexOrName];
    }

    /**
     * {@inheritDoc}
     */
    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->elements);
    }
}
