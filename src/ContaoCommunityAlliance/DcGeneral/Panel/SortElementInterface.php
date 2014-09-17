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

namespace ContaoCommunityAlliance\DcGeneral\Panel;

use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\GroupAndSortingDefinitionInterface;

/**
 * This interface describes a sort panel element.
 *
 * @package DcGeneral\Panel
 */
interface SortElementInterface
    extends PanelElementInterface
{
    /**
     * Set the default flag to use when no flag has been defined for a certain property.
     *
     * @param int $intFlag The flag to use.
     *
     * @return SearchElementInterface
     *
     * @deprecated not in use anymore.
     */
    public function setDefaultFlag($intFlag);

    /**
     * Get the default flag to use when no flag has been defined for a certain property.
     *
     * @return int
     *
     * @deprecated not in use anymore.
     */
    public function getDefaultFlag();

    /**
     * Add a property for sorting.
     *
     * @param string $strPropertyName The property name to enable sorting for.
     *
     * @param int    $intFlag         The flag to use for sorting.
     *
     * @return mixed
     *
     * @deprecated not in use anymore.
     */
    public function addProperty($strPropertyName, $intFlag);

    /**
     * Retrieve the list of properties to allow search on.
     *
     * @return string[]
     *
     * @deprecated not in use anymore.
     */
    public function getPropertyNames();

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
     * @return GroupAndSortingDefinitionInterface
     */
    public function getSelectedDefinition();

    /**
     * Return the flag of the currently selected property.
     *
     * @return int
     *
     * @deprecated not in use anymore.
     */
    public function getFlag();
}
