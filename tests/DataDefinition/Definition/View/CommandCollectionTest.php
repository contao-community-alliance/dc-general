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
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2013-2019 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Test\DataDefinition\Definition\View;

use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\Command;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\CommandCollection;
use ContaoCommunityAlliance\DcGeneral\Test\TestCase;
use ContaoCommunityAlliance\DcGeneral\Exception\DcGeneralInvalidArgumentException;

/**
 * @covers \ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\CommandCollection
 */
class CommandCollectionTest extends TestCase
{
    protected function assertIndexIs($expected, $array, $command)
    {
        self::assertSame($expected, \array_search($command, \array_values($array)));
    }

    public function testAddOne()
    {
        $collection = new CommandCollection();

        $command = new Command();
        $command->setName('test');

        $collection->addCommand($command);

        self::assertTrue($collection->hasCommand($command));
        self::assertTrue($collection->hasCommandNamed('test'));
        $this->assertIndexIs(0, $collection->getCommands(), $command);
    }

    public function testAddOneBefore()
    {
        $collection = new CommandCollection();

        $command1 = new Command();
        $command1->setName('test1');
        $command2 = new Command();
        $command2->setName('test2');

        $collection->addCommand($command1);
        $collection->addCommand($command2, $command1);

        self::assertTrue($collection->hasCommand($command1));
        self::assertTrue($collection->hasCommandNamed('test1'));
        self::assertTrue($collection->hasCommand($command2));
        self::assertTrue($collection->hasCommandNamed('test2'));

        $this->assertIndexIs(1, $collection->getCommands(), $command1);
        $this->assertIndexIs(0, $collection->getCommands(), $command2);
    }

    public function testAddOneBeforeNonExistant()
    {
        $collection = new CommandCollection();

        $command1 = new Command();
        $command1->setName('test1');
        $command2 = new Command();
        $command2->setName('test2');

        $this->expectException(DcGeneralInvalidArgumentException::class);

        $collection->addCommand($command2, $command1);

        self::assertTrue($collection->hasCommand($command1));
        self::assertTrue($collection->hasCommandNamed('test1'));
        self::assertFalse($collection->hasCommand($command2));
        self::assertFalse($collection->hasCommandNamed('test2'));

        $this->assertIndexIs(0, $collection->getCommands(), $command1);
        $this->assertIndexIs(false, $collection->getCommands(), $command2);
    }

    public function testAddTwo()
    {
        $collection = new CommandCollection();

        $command1 = new Command();
        $command1->setName('test1');
        $command2 = new Command();
        $command2->setName('test2');

        $collection->addCommands([$command1, $command2]);

        self::assertTrue($collection->hasCommand($command1));
        self::assertTrue($collection->hasCommandNamed('test1'));
        self::assertTrue($collection->hasCommand($command2));
        self::assertTrue($collection->hasCommandNamed('test2'));
        $this->assertIndexIs(0, $collection->getCommands(), $command1);
        $this->assertIndexIs(1, $collection->getCommands(), $command2);
    }

    public function testAddTwoBefore()
    {
        $collection = new CommandCollection();

        $command1 = new Command();
        $command1->setName('test1');
        $command2 = new Command();
        $command2->setName('test2');
        $command3 = new Command();
        $command3->setName('test3');

        $collection->addCommand($command3);
        $collection->addCommands([$command1, $command2], $command3);

        self::assertTrue($collection->hasCommand($command1));
        self::assertTrue($collection->hasCommandNamed('test1'));
        self::assertTrue($collection->hasCommand($command2));
        self::assertTrue($collection->hasCommandNamed('test2'));
        self::assertTrue($collection->hasCommand($command3));
        self::assertTrue($collection->hasCommandNamed('test3'));
        $this->assertIndexIs(0, $collection->getCommands(), $command1);
        $this->assertIndexIs(1, $collection->getCommands(), $command2);
        $this->assertIndexIs(2, $collection->getCommands(), $command3);
    }

    public function testAddTwoBeforeNonExistant()
    {
        $collection = new CommandCollection();

        $command1 = new Command();
        $command1->setName('test1');
        $command2 = new Command();
        $command2->setName('test2');
        $command3 = new Command();
        $command3->setName('test3');

        $this->expectException(DcGeneralInvalidArgumentException::class);

        $collection->addCommands([$command1, $command2], $command3);

        self::assertTrue($collection->hasCommand($command1));
        self::assertTrue($collection->hasCommandNamed('test1'));
        self::assertTrue($collection->hasCommand($command2));
        self::assertTrue($collection->hasCommandNamed('test2'));
        self::assertFalse($collection->hasCommand($command3));
        self::assertFalse($collection->hasCommandNamed('test3'));

        $this->assertIndexIs(0, $collection->getCommands(), $command1);
        $this->assertIndexIs(1, $collection->getCommands(), $command2);
        $this->assertIndexIs(false, $collection->getCommands(), $command3);
    }
}
