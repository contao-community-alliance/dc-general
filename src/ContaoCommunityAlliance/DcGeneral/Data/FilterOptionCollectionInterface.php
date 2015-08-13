<?php
/**
 * PHP version 5
 *
 * @package    generalDriver
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Data;

/**
 * This represents an iterable collection of Model elements.
 */
interface FilterOptionCollectionInterface extends \IteratorAggregate, \Countable
{
    /**
     * Append a key => value pair.
     *
     * @param string $filterKey   The key of the filter option. Needed for the system.
     *
     * @param string $filterValue The readable value for humans.
     *
     * @return FilterOptionCollectionInterface
     */
    public function add($filterKey, $filterValue);
}
