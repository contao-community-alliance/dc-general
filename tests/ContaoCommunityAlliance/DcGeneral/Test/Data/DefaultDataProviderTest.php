<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2015 Contao Community Alliance.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    contao-community-alliance/dc-general
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @copyright  2013-2015 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Test\Data;

use Contao\Database;
use ContaoCommunityAlliance\DcGeneral\Data\DefaultDataProvider;
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
            ->setMethods(array('__destruct'))
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
        $database->method('list_fields')->willReturn(array());

        $dataProvider = new DefaultDataProvider();

        $dataProvider->setBaseConfig(array(
            'source'            => 'tl_something',
            'database'          => $database,
        ));

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
        $database->method('list_fields')->willReturn(
            array(
                array(
                    'name' => 'idField',
                    'type' => 'field',
                ),
                array(
                    'name' => 'lastChanged',
                    'type' => 'field',
                ),
                array(
                    'name' => 'idField',
                    'type' => 'index',
                ),
            )
        );

        $idGenerator = $this->getMockForAbstractClass('ContaoCommunityAlliance\DcGeneral\Data\IdGeneratorInterface');

        $dataProvider = new DefaultDataProvider();

        $dataProvider->setBaseConfig(array(
            'source'            => 'tl_something',
            'database'          => $database,
            'idProperty'        => 'idField',
            'timeStampProperty' => 'lastChanged',
            'idGenerator'       => $idGenerator
        ));

        $reflection = new \ReflectionProperty(
            'ContaoCommunityAlliance\DcGeneral\Data\DefaultDataProvider',
            'objDatabase'
        );
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
        $this->assertInstanceOf('ContaoCommunityAlliance\DcGeneral\Data\ConfigInterface', $provider->getEmptyConfig());
    }

    /**
     * Test that creating an empty model works.
     *
     * @return void
     */
    public function testGetEmptyModel()
    {
        $provider = $this->mockDefaultProvider();
        $this->assertInstanceOf('ContaoCommunityAlliance\DcGeneral\Data\ModelInterface', $provider->getEmptyModel());
    }

    /**
     * Test that creating an empty model works.
     *
     * @return void
     */
    public function testGetEmptyCollection()
    {
        $provider = $this->mockDefaultProvider();
        $this->assertInstanceOf(
            'ContaoCommunityAlliance\DcGeneral\Data\CollectionInterface',
            $provider->getEmptyCollection()
        );
    }
}
