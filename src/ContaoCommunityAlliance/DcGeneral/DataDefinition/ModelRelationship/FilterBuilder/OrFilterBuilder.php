<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2015 Contao Community Alliance.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    contao-community-alliance/dc-general
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @copyright  2013-2015 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/LICENSE LGPL-3.0
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\DataDefinition\ModelRelationship\FilterBuilder;

use ContaoCommunityAlliance\DcGeneral\Exception\DcGeneralInvalidArgumentException;

/**
 * Handy helper class to generate and manipulate OR filter arrays.
 *
 * This class is intended to be only used via the FilterBuilder main class.
 */
class OrFilterBuilder extends FilterBuilderWithChildren
{
    /**
     * Create a new instance.
     *
     * @param array $children The initial children to absorb.
     *
     * @throws DcGeneralInvalidArgumentException When invalid children have been passed.
     */
    public function __construct($children = array())
    {
        parent::__construct($children);

        $this->operation = 'OR';
    }
}
