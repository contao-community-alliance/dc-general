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

namespace ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\Panel;

/**
 * Class DefaultLimitElementInformation.
 *
 * Default implementation of a limit definition.
 *
 * @package DcGeneral\DataDefinition\Definition\View\Panel
 */
class DefaultLimitElementInformation implements LimitElementInformationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'limit';
    }
}
