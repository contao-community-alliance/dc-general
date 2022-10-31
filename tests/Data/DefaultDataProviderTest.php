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

namespace ContaoCommunityAlliance\DcGeneral\Test\Data;

use Contao\Database;
use ContaoCommunityAlliance\DcGeneral\Data\CollectionInterface;
use ContaoCommunityAlliance\DcGeneral\Data\ConfigInterface;
use ContaoCommunityAlliance\DcGeneral\Data\DefaultDataProvider;
use ContaoCommunityAlliance\DcGeneral\Data\IdGeneratorInterface;
use ContaoCommunityAlliance\DcGeneral\Data\ModelInterface;
use ContaoCommunityAlliance\DcGeneral\Exception\DcGeneralRuntimeException;
use ContaoCommunityAlliance\DcGeneral\Test\TestCase;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\AbstractSchemaManager;
use Doctrine\DBAL\Schema\Table;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * This class tests the DefaultDataProvider class.
 *
 * @covers \ContaoCommunityAlliance\DcGeneral\Data\DefaultDataProvider
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DefaultDataProviderTest extends TestCase
{
    /**
     * Mock the Contao database.
     *
     * @return MockObject|Database
     */
    private function mockDatabase()
    {
        return $this
            ->getMockBuilder(Database::class)
            ->disableOriginalConstructor()
            ->setMethods(['__destruct', 'listFields'])
            ->getMockForAbstractClass();
    }

    private function mockConnection()
    {
        return $this
            ->getMockBuilder(Connection::class)
            ->disableOriginalConstructor()
            ->setMethods(['getSchemaManager'])
            ->getMock();
    }

    /**
     * Mock the default provider.
     *
     * @return DefaultDataProvider
     */
    private function mockDefaultProvider()
    {
        $schemaTable = $this
            ->getMockBuilder(Table::class)
            ->disableOriginalConstructor()
            ->setMethods(['hasColumn'])
            ->getMock();
        $schemaTable->method('hasColumn')->willReturn(false);

        $schemaManager = $this
            ->getMockBuilder(AbstractSchemaManager::class)
            ->disableOriginalConstructor()
            ->setMethods(['listTableDetails'])
            ->getMockForAbstractClass();
        $schemaManager->method('listTableDetails')->willReturn($schemaTable);

        $connection = $this->mockConnection();
        $connection->method('getSchemaManager')->willReturn($schemaManager);
        //$database->method('listFields')->willReturn([]);

        $dataProvider = new DefaultDataProvider();

        $dataProvider->setBaseConfig(
            [
                'source'     => 'tl_something',
                'connection' => $connection
            ]
        );

        return $dataProvider;
    }

    public function testSetBaseConfigNoSource()
    {
        $dataProvider = new DefaultDataProvider();

        try {
            $dataProvider->setBaseConfig([]);
        } catch (\Exception $exception) {
            self::assertInstanceOf(DcGeneralRuntimeException::class, $exception);
            self::assertSame($exception->getMessage(), 'Missing table name.');
        }
    }

    public function testSetBaseConfigDeprecatedDatabase()
    {
        $dataProvider = new DefaultDataProvider();
        $database     = $this->mockDatabase();

        $schemaTable = $this
            ->getMockBuilder(Table::class)
            ->disableOriginalConstructor()
            ->setMethods(['hasColumn'])
            ->getMock();
        $schemaTable->method('hasColumn')->willReturn(false);

        $schemaManager = $this
            ->getMockBuilder(AbstractSchemaManager::class)
            ->disableOriginalConstructor()
            ->setMethods(['listTableDetails'])
            ->getMockForAbstractClass();
        $schemaManager->method('listTableDetails')->willReturn($schemaTable);

        $connection = $this->mockConnection();
        $connection->method('getSchemaManager')->willReturn($schemaManager);

        $reflection = new \ReflectionProperty(Database::class, 'resConnection');
        $reflection->setAccessible(true);

        $reflection->setValue($database, $connection);

        $dataProvider->setBaseConfig(
            [
                'source'   => 'tl_dummy',
                'database' => $database
            ]
        );

        $reflection = new \ReflectionProperty(DefaultDataProvider::class, 'connection');
        $reflection->setAccessible(true);
        self::assertInstanceOf(Connection::class, $reflection->getValue($dataProvider));

        $reflection = new \ReflectionProperty(DefaultDataProvider::class, 'source');
        $reflection->setAccessible(true);
        self::assertSame('tl_dummy', $reflection->getValue($dataProvider));

        $reflection = new \ReflectionProperty(DefaultDataProvider::class, 'idProperty');
        $reflection->setAccessible(true);
        self::assertSame('id', $reflection->getValue($dataProvider));

        self::assertFalse($dataProvider->getTimeStampProperty());
        self::assertNull($dataProvider->getIdGenerator());
    }

    public function testSetBaseConfigInvalidConnection()
    {
        $dataProvider = new DefaultDataProvider();

        try {
            $dataProvider->setBaseConfig(
                [
                    'source'   => 'tl_dummy',
                    'database' => '\Invalid\Connection'
                ]
            );
        } catch (\Exception $exception) {
            self::assertInstanceOf(DcGeneralRuntimeException::class, $exception);
            self::assertSame($exception->getMessage(), 'Invalid database connection.');
        }
    }

    public function testSetBaseConfigForGetDefaultConnection()
    {
        $schemaTable = $this
            ->getMockBuilder(Table::class)
            ->disableOriginalConstructor()
            ->setMethods(['hasColumn'])
            ->getMock();
        $schemaTable->method('hasColumn')->willReturn(false);

        $schemaManager = $this
            ->getMockBuilder(AbstractSchemaManager::class)
            ->disableOriginalConstructor()
            ->setMethods(['listTableDetails'])
            ->getMockForAbstractClass();
        $schemaManager->method('listTableDetails')->willReturn($schemaTable);

        $connection = $this->mockConnection();
        $connection->method('getSchemaManager')->willReturn($schemaManager);

        $dataProvider = $this
            ->getMockBuilder(DefaultDataProvider::class)
            ->disableOriginalConstructor()
            ->setMethods(['getDefaultConnection'])
            ->getMock();
        $dataProvider->method('getDefaultConnection')->willReturn($connection);

        $dataProvider->setBaseConfig(
            [
                'source'   => 'tl_dummy'
            ]
        );

        $reflection = new \ReflectionProperty(DefaultDataProvider::class, 'connection');
        $reflection->setAccessible(true);
        self::assertInstanceOf(Connection::class, $reflection->getValue($dataProvider));
    }

    /**
     * Test that setting the base config works.
     *
     * @return void
     */
    public function testSetBaseConfig()
    {
        $connection = $this->mockConnection();

        $idGenerator = $this->getMockForAbstractClass(IdGeneratorInterface::class);

        $dataProvider = new DefaultDataProvider();

        $dataProvider->setBaseConfig(
            [
                'source'            => 'tl_something',
                'connection'        => $connection,
                'idProperty'        => 'idField',
                'timeStampProperty' => 'lastChanged',
                'idGenerator'       => $idGenerator
            ]
        );

        $reflection = new \ReflectionProperty(DefaultDataProvider::class, 'connection');
        $reflection->setAccessible(true);

        self::assertEquals('tl_something', $dataProvider->getEmptyModel()->getProviderName());
        self::assertEquals($connection, $reflection->getValue($dataProvider));
        self::assertEquals('idField', $dataProvider->getIdProperty());
        self::assertEquals('lastChanged', $dataProvider->getTimeStampProperty());
        self::assertSame($idGenerator, $dataProvider->getIdGenerator());
    }

    /**
     * Test that creating an empty config works.
     *
     * @return void
     */
    public function testGetEmptyConfig()
    {
        $provider = $this->mockDefaultProvider();
        self::assertInstanceOf(ConfigInterface::class, $provider->getEmptyConfig());
    }

    /**
     * Test that creating an empty model works.
     *
     * @return void
     */
    public function testGetEmptyModel()
    {
        $provider = $this->mockDefaultProvider();
        self::assertInstanceOf(ModelInterface::class, $provider->getEmptyModel());
    }

    /**
     * Test that creating an empty model works.
     *
     * @return void
     */
    public function testGetEmptyCollection()
    {
        $provider = $this->mockDefaultProvider();
        self::assertInstanceOf(CollectionInterface::class, $provider->getEmptyCollection());
    }

    /**
     * @covers \ContaoCommunityAlliance\DcGeneral\Data\DefaultDataProvider::getDefaultConnection
     */
    public function testGetDefaultConnection()
    {
        self::markTestSkipped('This method is not testable.');
    }
}
