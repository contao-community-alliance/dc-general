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

namespace ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View;

/**
 * This class defines a grouping and sorting information for the view.
 */
class DefaultGroupAndSortingInformation implements GroupAndSortingInformationInterface
{
    /**
     * The property to use.
     *
     * @var string
     */
    protected $property;

    /**
     * The sorting method to use.
     *
     * @var string
     */
    protected $sorting = GroupAndSortingInformationInterface::SORT_ASC;

    /**
     * The grouping to be applied.
     *
     * @var string
     */
    protected $grouping = GroupAndSortingInformationInterface::GROUP_NONE;

    /**
     * The grouping length (used when grouping mode is char).
     *
     * @var int
     */
    protected $groupingLength = 0;

    /**
     * Flag determining if this information is for manual sorting.
     *
     * @var bool
     */
    protected $manualSorting;

    /**
     * {@inheritDoc}
     */
    public function setProperty($property)
    {
        $this->property = $property;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getProperty()
    {
        return $this->property;
    }

    /**
     * {@inheritDoc}
     */
    public function setGroupingMode($value)
    {
        $this->grouping = $value;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getGroupingMode()
    {
        return $this->grouping;
    }

    /**
     * {@inheritDoc}
     */
    public function setGroupingLength($value)
    {
        $this->groupingLength = $value;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getGroupingLength()
    {
        return $this->groupingLength;
    }

    /**
     * {@inheritDoc}
     */
    public function setSortingMode($value)
    {
        $this->sorting = $value;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getSortingMode()
    {
        return $this->sorting;
    }

    /**
     * {@inheritDoc}
     */
    public function setManualSorting($value = true)
    {
        $this->manualSorting = $value;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function isManualSorting()
    {
        return $this->manualSorting;
    }
}
