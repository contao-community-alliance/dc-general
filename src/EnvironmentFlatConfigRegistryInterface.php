<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2021 Contao Community Alliance.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    contao-community-alliance/dc-general
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2013-2021 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

declare(strict_types=1);

namespace ContaoCommunityAlliance\DcGeneral;

use ContaoCommunityAlliance\DcGeneral\Config\BaseConfigRegistryInterface;

/**
 * The environment interface for the flat config registry.
 */
interface EnvironmentFlatConfigRegistryInterface extends EnvironmentInterface
{
    /**
     * Set the base config registry to use.
     *
     * @param BaseConfigRegistryInterface $baseConfigRegistry The input provider to use.
     *
     * @return EnvironmentFlatConfigRegistryInterface
     */
    public function setFlatConfigRegistry(
        BaseConfigRegistryInterface $baseConfigRegistry
    ): EnvironmentFlatConfigRegistryInterface;

    /**
     * Retrieve the base config registry.
     *
     * @return BaseConfigRegistryInterface
     */
    public function getFlatConfigRegistry(): ?BaseConfigRegistryInterface;
}
