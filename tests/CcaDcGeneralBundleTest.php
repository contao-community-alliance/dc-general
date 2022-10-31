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
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2013-2019 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Test;

use ContaoCommunityAlliance\DcGeneral\CcaDcGeneralBundle;
use ContaoCommunityAlliance\DcGeneral\DependencyInjection\Compiler\AddSessionBagsPass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Test the general bundle.
 *
 * @covers \ContaoCommunityAlliance\DcGeneral\CcaDcGeneralBundle
 */
class CcaDcGeneralBundleTest extends TestCase
{
    public function testBuild()
    {
        $passes    = [
            AddSessionBagsPass::class
        ];

        $container = $this->createMock(ContainerBuilder::class);
        $container
            ->expects(self::exactly(\count($passes)))
            ->method('addCompilerPass')
            ->with(
                self::callback(
                    function ($param) use ($passes) {
                        return \in_array(\get_class($param), $passes, true);
                    }
                )
            );

        $bundle = new CcaDcGeneralBundle();
        $bundle->build($container);
    }
}
