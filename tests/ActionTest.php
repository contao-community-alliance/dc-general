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
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2013-2018 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Test;

use ContaoCommunityAlliance\DcGeneral\Action;
use PHPUnit\Framework\TestCase;

/**
 * Test for the action
 *
 * @covers \ContaoCommunityAlliance\DcGeneral\Action
 */
class ActionTest extends TestCase
{
    public function testAction()
    {
        $arguments = ['foo', 'bar'];

        $action = new Action('action', $arguments);

        $this->assertIsString($action->getName());
        $this->assertSame('action', $action->getName());
        $this->assertIsArray($action->getArguments());
        $this->assertSame(['foo', 'bar'], $action->getArguments());
    }
}
