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

/**
 * This class defines a grouping and sorting information for the view.
 *
 * @api
 */
class DefaultGroupAndSortingInformation implements GroupAndSortingInformationInterface
{
    /**
     * The property to use.
     *
     * @var string
     */
    protected string $property = '';

    /**
     * The sorting method to use.
     *
     * @var string
     */
    protected string $sorting = GroupAndSortingInformationInterface::SORT_ASC;

    /**
     * The grouping to be applied.
     *
     * @var string
     */
    protected string $grouping = GroupAndSortingInformationInterface::GROUP_NONE;

    /**
     * The grouping length (used when grouping mode is char).
     *
     * @var int
     */
    protected int $groupingLength = 0;

    /**
     * Flag determining if this information is for manual sorting.
     *
     * @var bool
     */
    protected bool $manualSorting = false;

    /**
     * {@inheritDoc}
     */
    #[\Override]
    public function setProperty($property)
    {
        $this->property = $property;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    #[\Override]
    public function getProperty()
    {
        return $this->property;
    }

    /**
     * {@inheritDoc}
     */
    #[\Override]
    public function setGroupingMode($value)
    {
        $this->grouping = $value;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    #[\Override]
    public function getGroupingMode()
    {
        return $this->grouping;
    }

    /**
     * {@inheritDoc}
     */
    #[\Override]
    public function setGroupingLength($value)
    {
        $this->groupingLength = $value;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    #[\Override]
    public function getGroupingLength(): int
    {
        return $this->groupingLength;
    }

    /**
     * {@inheritDoc}
     */
    #[\Override]
    public function setSortingMode($value)
    {
        $this->sorting = $value;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    #[\Override]
    public function getSortingMode()
    {
        return $this->sorting;
    }

    /**
     * {@inheritDoc}
     */
    #[\Override]
    public function setManualSorting($value = true)
    {
        $this->manualSorting = $value;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    #[\Override]
    public function isManualSorting()
    {
        return $this->manualSorting;
    }
}
