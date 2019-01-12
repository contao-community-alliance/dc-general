<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2019 Contao Community Alliance.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    contao-community-alliance/dc-general
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Tristan Lins <tristan.lins@bit3.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2013-2019 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Panel;

use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\GroupAndSortingDefinitionInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\GroupAndSortingInformationInterface;

/**
 * This interface describes a sort panel element.
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
