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
 * Interface ElementInformationInterface.
 *
 * This interface describes a generic panel element information.
 *
 * @package DcGeneral\DataDefinition\Definition\View\Panel
 */
interface ElementInformationInterface
{
    /**
     * The name of the element.
     *
     * @return string
     */
    public function getName();
}
