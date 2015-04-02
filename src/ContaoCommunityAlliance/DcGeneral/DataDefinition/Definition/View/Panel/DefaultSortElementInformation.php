<?php
/**
 * PHP version 5
 *
 * @package    generalDriver
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Tristan Lins <tristan.lins@bit3.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\Panel;

/**
 * Class DefaultSortElementInformation.
 *
 * Default implementation of a sort definition.
 *
 * @package DcGeneral\DataDefinition\Definition\View\Panel
 */
class DefaultSortElementInformation implements SortElementInformationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'sort';
    }
}
