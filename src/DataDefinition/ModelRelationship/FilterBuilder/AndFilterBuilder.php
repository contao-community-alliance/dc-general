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

namespace ContaoCommunityAlliance\DcGeneral\DataDefinition\ModelRelationship\FilterBuilder;

use ContaoCommunityAlliance\DcGeneral\DataDefinition\ModelRelationship\FilterBuilder;
use ContaoCommunityAlliance\DcGeneral\Exception\DcGeneralInvalidArgumentException;

/**
 * Handy helper class to generate and manipulate AND filter arrays.
 *
 * This class is intended to be only used via the FilterBuilder main class.
 */
class AndFilterBuilder extends FilterBuilderWithChildren
{
    /**
     * Create a new instance.
     *
     * @param list<BaseFilterBuilder> $children The initial children to absorb.
     *
     * @throws DcGeneralInvalidArgumentException When invalid children have been passed.
     */
    public function __construct($children = [])
    {
        parent::__construct($children);

        $this->operation = 'AND';
    }

    /**
     * Absorb the given filter builder or filter builder collection.
     *
     * @param FilterBuilder|FilterBuilderWithChildren $filters The input.
     *
     * @return FilterBuilderWithChildren
     */
    public function append($filters)
    {
        if ($filters instanceof FilterBuilder) {
            $filters = $filters->getFilter();
        }

        if ($filters instanceof AndFilterBuilder) {
            foreach ($filters as $filter) {
                $this->add(clone $filter);
            }

            return $this;
        }

        $this->andEncapsulate(clone $filters);

        return $this;
    }
}
