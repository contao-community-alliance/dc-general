<?php
/**
 * PHP version 5
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
 * This interface defines a grouping and sorting information for the view.
 */
interface GroupAndSortingDefinitionInterface extends \IteratorAggregate
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
     * @return GroupAndSortingInformationInterface
     */
    public function add($index = -1);

    /**
     * Delete an information from the collection.
     *
     * @param int $index Remove the information with the given index.
     *
     * @return GroupAndSortingDefinitionInterface
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
     * @param int $index Position of the information.
     *
     * @return GroupAndSortingInformationInterface
     */
    public function get($index);

    /**
     * Set the name of the definition.
     *
     * @param string $name The name.
     *
     * @return GroupAndSortingDefinitionInterface
     */
    public function setName($name);

    /**
     * Retrieve the name of the definition.
     *
     * @return string
     */
    public function getName();
}
