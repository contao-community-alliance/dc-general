<?php

/**
 * PHP version 5
 * @package    generalDriver
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Test\DataDefinition\Definition\View;

use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\Command;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\CommandCollection;
use ContaoCommunityAlliance\DcGeneral\Test\TestCase;

class CommandCollectionTest extends TestCase
{
    protected function assertIndexIs($expected, $array, $command)
    {
        $this->assertSame($expected, array_search($command, array_values($array)));
    }

    public function testAddOne()
    {
        $collection = new CommandCollection();

        $command = new Command();
        $command->setName('test');

        $collection->addCommand($command);

        $this->assertTrue($collection->hasCommand($command));
        $this->assertTrue($collection->hasCommandNamed('test'));
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

        $this->assertTrue($collection->hasCommand($command1));
        $this->assertTrue($collection->hasCommandNamed('test1'));
        $this->assertTrue($collection->hasCommand($command2));
        $this->assertTrue($collection->hasCommandNamed('test2'));

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

        $this->setExpectedException('ContaoCommunityAlliance\DcGeneral\Exception\DcGeneralInvalidArgumentException');
        $collection->addCommand($command2, $command1);

        $this->assertTrue($collection->hasCommand($command1));
        $this->assertTrue($collection->hasCommandNamed('test1'));
        $this->assertFalse($collection->hasCommand($command2));
        $this->assertFalse($collection->hasCommandNamed('test2'));

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

        $collection->addCommands(array($command1, $command2));

        $this->assertTrue($collection->hasCommand($command1));
        $this->assertTrue($collection->hasCommandNamed('test1'));
        $this->assertTrue($collection->hasCommand($command2));
        $this->assertTrue($collection->hasCommandNamed('test2'));
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
        $collection->addCommands(array($command1, $command2), $command3);

        $this->assertTrue($collection->hasCommand($command1));
        $this->assertTrue($collection->hasCommandNamed('test1'));
        $this->assertTrue($collection->hasCommand($command2));
        $this->assertTrue($collection->hasCommandNamed('test2'));
        $this->assertTrue($collection->hasCommand($command3));
        $this->assertTrue($collection->hasCommandNamed('test3'));
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

        $this->setExpectedException('ContaoCommunityAlliance\DcGeneral\Exception\DcGeneralInvalidArgumentException');
        $collection->addCommands(array($command1, $command2), $command3);

        $this->assertTrue($collection->hasCommand($command1));
        $this->assertTrue($collection->hasCommandNamed('test1'));
        $this->assertTrue($collection->hasCommand($command2));
        $this->assertTrue($collection->hasCommandNamed('test2'));
        $this->assertFalse($collection->hasCommand($command3));
        $this->assertFalse($collection->hasCommandNamed('test3'));

        $this->assertIndexIs(0, $collection->getCommands(), $command1);
        $this->assertIndexIs(1, $collection->getCommands(), $command2);
        $this->assertIndexIs(false, $collection->getCommands(), $command3);
    }
}
