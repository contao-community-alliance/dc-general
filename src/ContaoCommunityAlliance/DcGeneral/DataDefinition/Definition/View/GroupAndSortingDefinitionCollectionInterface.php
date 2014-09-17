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
 * This interface defines a collection of grouping and sorting information for the view.
 */
interface GroupAndSortingDefinitionCollectionInterface extends \IteratorAggregate
{
    /**
     * Add a new information - optionally at the given position.
     *
     * If the given position is zero or any other positive value, the new information will get placed at the given
     * position.
     * If the index is negative or greater than the total amount of information present, the new information will get
     * placed at the end of the list.
     *
     * @param int $index Target position for the new information.
     *
     * @return GroupAndSortingDefinitionInterface
     */
    public function add($index = -1);

    /**
     * Delete an information from the collection.
     *
     * @param int $index Remove the information with the given index.
     *
     * @return GroupAndSortingDefinitionCollectionInterface
     */
    public function delete($index);

    /**
     * Retrieve the amount of information.
     *
     * @return int
     */
    public function getCount();

    /**
     * Retrieve the information at the given position.
     *
     * If the given index is out of bounds (less than zero or greater than the amount of information) an exception is
     * fired.
     *
     * @param int $index Position of the information. Default: -1 meaning the default information.
     *
     * @return GroupAndSortingDefinitionInterface
     */
    public function get($index = -1);

    /**
     * Mark an information as the default.
     *
     * @param int|GroupAndSortingDefinitionInterface $information The information to be used as default.
     *
     * @return GroupAndSortingDefinitionCollectionInterface
     */
    public function markDefault($information);

    /**
     * Check if a default information is present.
     *
     * @return bool
     */
    public function hasDefault();

    /**
     * Retrieve the default definition.
     *
     * @return GroupAndSortingDefinitionInterface
     */
    public function getDefault();

    /**
     * Retrieve the index of the default definition.
     *
     * @return int
     */
    public function getDefaultIndex();
}
