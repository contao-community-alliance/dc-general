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
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2013-2023 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View;

use ContaoCommunityAlliance\DcGeneral\Exception\DcGeneralInvalidArgumentException;

/**
 * This class defines a collection of grouping and sorting information for the view.
 *
 * @SuppressWarnings(PHPMD.LongClassName)
 */
class DefaultGroupAndSortingDefinitionCollection implements GroupAndSortingDefinitionCollectionInterface
{
    /**
     * The information stored.
     *
     * @var list<GroupAndSortingDefinitionInterface>
     */
    protected $information = [];

    /**
     * Index of the default information.
     *
     * @var int
     */
    protected $default = -1;

    /**
     * {@inheritDoc}
     */
    public function add($index = -1)
    {
        $information = new DefaultGroupAndSortingDefinition();
        $information->setName('Information ' . ($this->getCount() + 1));

        if (($index < 0) || ($this->getCount() <= $index)) {
            $this->information[] = $information;
            return $information;
        }

        \array_splice($this->information, $index, 0, [$information]);
        return $information;
    }

    /**
     * {@inheritDoc}
     */
    public function delete($index)
    {
        if ($index === $this->default) {
            $this->default = -1;
        }
        unset($this->information[$index]);
        $this->information = \array_values($this->information);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getCount()
    {
        return \count($this->information);
    }

    /**
     * {@inheritDoc}
     *
     * @throws DcGeneralInvalidArgumentException When the offset does not exist.
     */
    public function get($index = -1)
    {
        if ($index === -1) {
            return $this->getDefault();
        }

        if (!isset($this->information[$index])) {
            throw new DcGeneralInvalidArgumentException('Offset ' . $index . ' does not exist.');
        }

        return $this->information[$index];
    }

    /**
     * {@inheritDoc}
     *
     * @throws DcGeneralInvalidArgumentException When the information is neither a proper instance nor an integer.
     */
    public function markDefault($information)
    {
        if ($information instanceof GroupAndSortingDefinitionInterface) {
            $information = \array_search($information, $this->information);
        }

        if (!\is_int($information)) {
            throw new DcGeneralInvalidArgumentException('Invalid argument.');
        }

        $this->default = $information;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function hasDefault()
    {
        return (-1 !== $this->getDefaultIndex());
    }

    /**
     * {@inheritDoc}
     *
     * @throws DcGeneralInvalidArgumentException When no default has been defined.
     */
    public function getDefault()
    {
        $index = $this->getDefaultIndex();
        if (-1 === $index) {
            throw new DcGeneralInvalidArgumentException('No default sorting and grouping information defined.');
        }

        return $this->get($index);
    }

    /**
     * {@inheritDoc}
     */
    public function getDefaultIndex()
    {
        return $this->default;
    }

    /**
     * {@inheritDoc}
     */
    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->information);
    }
}
