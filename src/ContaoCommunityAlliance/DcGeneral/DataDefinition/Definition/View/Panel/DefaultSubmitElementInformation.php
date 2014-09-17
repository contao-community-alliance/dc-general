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
 * Class DefaultSubmitElementInformation.
 *
 * Default implementation for a submit panel element information.
 *
 * @package DcGeneral\DataDefinition\Definition\View\Panel
 */
class DefaultSubmitElementInformation implements SubmitElementInformationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'submit';
    }
}
