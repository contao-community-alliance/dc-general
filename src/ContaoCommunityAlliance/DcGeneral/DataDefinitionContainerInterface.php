<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2018 Contao Community Alliance.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    contao-community-alliance/dc-general
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2013-2018 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral;

use ContaoCommunityAlliance\DcGeneral\DataDefinition\ContainerInterface;

/**
 * This implementation describes a data definition container.
 *
 * A data definition container is the top level container where all data definitions get stored.
 */
interface DataDefinitionContainerInterface
{
    /**
     * Add or override a definition in the container.
     *
     * @param string             $name       Name of the definition.
     * @param ContainerInterface $definition The definition to store.
     *
     * @return DataDefinitionContainerInterface
     */
    public function setDefinition($name, $definition);

    /**
     * Check if a definition is contained in the container.
     *
     * @param string $name The name of the definition to retrieve.
     *
     * @return bool
     */
    public function hasDefinition($name);

    /**
     * Retrieve a definition from the container (if it exists).
     *
     * If the definition does not exist, an exception is thrown.
     *
     * @param string $name The name of the definition to retrieve.
     *
     * @return ContainerInterface
     */
    public function getDefinition($name);
}
