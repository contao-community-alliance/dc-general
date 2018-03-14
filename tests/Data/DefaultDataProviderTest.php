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

namespace ContaoCommunityAlliance\DcGeneral\Test\Data;

use Contao\Database;
use ContaoCommunityAlliance\DcGeneral\Data\CollectionInterface;
use ContaoCommunityAlliance\DcGeneral\Data\ConfigInterface;
use ContaoCommunityAlliance\DcGeneral\Data\DefaultDataProvider;
use ContaoCommunityAlliance\DcGeneral\Data\IdGeneratorInterface;
use ContaoCommunityAlliance\DcGeneral\Data\ModelInterface;
use ContaoCommunityAlliance\DcGeneral\Test\TestCase;

/**
 * This class tests the DefaultDataProvider class.
 */
class DefaultDataProviderTest extends TestCase
{
    /**
     * Mock the Contao database.
     *
     * @return \PHPUnit_Framework_MockObject_MockObject|Database
     */
    private function mockDatabase()
    {
        return $this
            ->getMockBuilder('Contao\Database')
            ->disableOriginalConstructor()
            ->setMethods(['__destruct', 'listFields'])
            ->getMockForAbstractClass();
    }

    /**
     * Mock the default provider.
     *
     * @return DefaultDataProvider
     */
    private function mockDefaultProvider()
    {
        $database = $this->mockDatabase();
        $database->method('listFields')->willReturn([]);

        $dataProvider = new DefaultDataProvider();

        $dataProvider->setBaseConfig([
                'source'   => 'tl_something',
                'database' => $database,
            ]
        );

        return $dataProvider;
    }

    /**
     * Test that setting the base config works.
     *
     * @return void
     */
    public function testSetBaseConfig()
    {
        $database = $this->mockDatabase();
        $database->method('listFields')->willReturn([
                [
                    'name' => 'idField',
                    'type' => 'field',
                ],
                [
                    'name' => 'lastChanged',
                    'type' => 'field',
                ],
                [
                    'name' => 'idField',
                    'type' => 'index',
                ],
            ]
        );

        $idGenerator = $this->getMockForAbstractClass(IdGeneratorInterface::class);

        $dataProvider = new DefaultDataProvider();

        $dataProvider->setBaseConfig([
                'source'            => 'tl_something',
                'database'          => $database,
                'idProperty'        => 'idField',
                'timeStampProperty' => 'lastChanged',
                'idGenerator'       => $idGenerator
            ]
        );

        $reflection = new \ReflectionProperty(DefaultDataProvider::class, 'objDatabase');
        $reflection->setAccessible(true);

        $this->assertEquals('tl_something', $dataProvider->getEmptyModel()->getProviderName());
        $this->assertEquals($database, $reflection->getValue($dataProvider));
        $this->assertEquals('idField', $dataProvider->getIdProperty());
        $this->assertEquals('lastChanged', $dataProvider->getTimeStampProperty());
        $this->assertSame($idGenerator, $dataProvider->getIdGenerator());
    }

    /**
     * Test that creating an empty config works.
     *
     * @return void
     */
    public function testGetEmptyConfig()
    {
        $provider = $this->mockDefaultProvider();
        $this->assertInstanceOf(ConfigInterface::class, $provider->getEmptyConfig());
    }

    /**
     * Test that creating an empty model works.
     *
     * @return void
     */
    public function testGetEmptyModel()
    {
        $provider = $this->mockDefaultProvider();
        $this->assertInstanceOf(ModelInterface::class, $provider->getEmptyModel());
    }

    /**
     * Test that creating an empty model works.
     *
     * @return void
     */
    public function testGetEmptyCollection()
    {
        $provider = $this->mockDefaultProvider();
        $this->assertInstanceOf(CollectionInterface::class, $provider->getEmptyCollection());
    }
}
