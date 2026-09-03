<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2026 Contao Community Alliance.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    contao-community-alliance/dc-general
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2013-2026 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral;

use ContaoCommunityAlliance\DcGeneral\DependencyInjection\Compiler\AddSessionBagsPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * This class holds everything together.
 *
 * @api
 *
 * @psalm-suppress DeprecatedInterface Bundle implements the deprecated BundleInterface under
 *     Symfony 8, but Symfony\Component\DependencyInjection\Kernel\AbstractBundle is not a drop-in
 *     replacement here: its getContainerExtension() does not do the classic reflection-based
 *     lookup of a "<Namespace>\DependencyInjection\<Name>Extension" class that
 *     CcaDcGeneralExtension relies on, so swapping the base class silently stops that extension
 *     (and all its services) from ever loading.
 */
class CcaDcGeneralBundle extends Bundle
{
    /**
     * {@inheritDoc}
     */
    #[\Override]
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container->addCompilerPass(new AddSessionBagsPass());
    }
}
