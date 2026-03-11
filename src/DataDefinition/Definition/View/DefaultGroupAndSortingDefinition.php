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
 * @copyright  2013-2023 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View;

use ContaoCommunityAlliance\DcGeneral\Exception\DcGeneralInvalidArgumentException;

/**
 * This class defines a collection of grouping and sorting information for the view.
 *
 * @api
 */
class DefaultGroupAndSortingDefinition implements GroupAndSortingDefinitionInterface
{
    /**
     * The information stored.
     *
     * @var GroupAndSortingInformationInterface[]
     */
    protected array $information = [];

    /**
     * The name of the definition.
     *
     * @var string
     */
    protected string $name = '';

    /**
     * {@inheritDoc}
     */
    #[\Override]
    public function add($index = -1)
    {
        $information = new DefaultGroupAndSortingInformation();

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
    #[\Override]
    public function delete($index)
    {
        unset($this->information[$index]);
        $this->information = \array_values($this->information);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    #[\Override]
    public function getCount()
    {
        return \count($this->information);
    }

    /**
     * {@inheritDoc}
     *
     * @throws DcGeneralInvalidArgumentException When the given offset does not exist.
     */
    #[\Override]
    public function get($index)
    {
        if (!isset($this->information[$index])) {
            throw new DcGeneralInvalidArgumentException('Offset ' . $index . ' does not exist.');
        }

        return $this->information[$index];
    }

    /**
     * {@inheritDoc}
     */
    #[\Override]
    public function getName()
    {
        return $this->name;
    }

    /**
     * {@inheritDoc}
     */
    #[\Override]
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    #[\Override]
    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->information);
    }
}
