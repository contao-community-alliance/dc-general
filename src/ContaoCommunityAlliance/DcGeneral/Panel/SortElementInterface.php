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

namespace ContaoCommunityAlliance\DcGeneral\Panel;

use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\GroupAndSortingDefinitionInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\GroupAndSortingInformationInterface;

/**
 * This interface describes a sort panel element.
 *
 * @package DcGeneral\Panel
 */
interface SortElementInterface extends PanelElementInterface
{
    /**
     * Set the selected definition for sorting.
     *
     * @param string $name The name of the definition to mark as selected.
     *
     * @return SearchElementInterface
     */
    public function setSelected($name);

    /**
     * Return the name of the currently selected definition.
     *
     * @return string
     */
    public function getSelected();

    /**
     * Return the currently selected definition.
     *
     * @return GroupAndSortingDefinitionInterface|GroupAndSortingInformationInterface[]
     */
    public function getSelectedDefinition();
}
