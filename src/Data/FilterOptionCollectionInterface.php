<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2017 Contao Community Alliance.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    contao-community-alliance/dc-general
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2013-2017 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0
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
