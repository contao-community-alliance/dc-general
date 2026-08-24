<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2025 Contao Community Alliance.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    contao-community-alliance/dc-general
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2013-2025 Contao Community Alliance.
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
use Exception;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use ReflectionProperty;

/**
 * This class tests the DefaultDataProvider class.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
#[AllowMockObjectsWithoutExpectations]
#[CoversClass(DefaultDataProvider::class)]
final class DefaultDataProviderTest extends TestCase
{
    /**
     * Mock the Contao database.
     */
    private function mockDatabase(): Database&MockObject
    {
        return $this
            ->getMockBuilder(Database::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['listFields'])
            ->getMock();
    }

    private function mockConnection(): Connection&MockObject
    {
        return $this
            ->getMockBuilder(Connection::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['createSchemaManager'])
            ->getMock();
    }

    /**
     * getMockBuilder(AbstractSchemaManager::class)->onlyMethods(...)->getMock() fails to compile
     * under DBAL 4 - AbstractSchemaManager grew several new `abstract protected` methods there and
     * PHPUnit's generator does not stub them, so the generated double is left non-instantiable.
     * getMockForAbstractClass() takes a different code path that does stub every abstract method.
     */
    private function mockSchemaManager(Table $schemaTable): AbstractSchemaManager&MockObject
    {
        $schemaManager = $this
            ->getMockBuilder(AbstractSchemaManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $schemaManager->method('introspectTable')->willReturn($schemaTable);

        return $schemaManager;
    }

    /**
     * Mock the default provider.
     */
    private function mockDefaultProvider(): DefaultDataProvider
    {
        $schemaTable = $this
            ->getMockBuilder(Table::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['hasColumn'])
            ->getMock();
        $schemaTable->method('hasColumn')->willReturn(false);

        $schemaManager = $this->mockSchemaManager($schemaTable);

        $connection = $this->mockConnection();
        $connection->method('createSchemaManager')->willReturn($schemaManager);
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

    public function testSetBaseConfigNoSource(): void
    {
        $dataProvider = new DefaultDataProvider();

        try {
            $dataProvider->setBaseConfig([]);
        } catch (Exception $exception) {
            self::assertInstanceOf(DcGeneralRuntimeException::class, $exception);
            self::assertSame('Missing table name.', $exception->getMessage());
        }
    }

    public function testSetBaseConfigDeprecatedDatabase(): void
    {
        $dataProvider = new DefaultDataProvider();
        $database     = $this->mockDatabase();

        $schemaTable = $this
            ->getMockBuilder(Table::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['hasColumn'])
            ->getMock();
        $schemaTable->method('hasColumn')->willReturn(false);

        $schemaManager = $this->mockSchemaManager($schemaTable);

        $connection = $this->mockConnection();
        $connection->method('createSchemaManager')->willReturn($schemaManager);

        $reflection = new ReflectionProperty(Database::class, 'resConnection');

        $reflection->setValue($database, $connection);

        $dataProvider->setBaseConfig(
            [
                'source'   => 'tl_dummy',
                'database' => $database
            ]
        );

        $reflection = new ReflectionProperty(DefaultDataProvider::class, 'connection');
        self::assertInstanceOf(Connection::class, $reflection->getValue($dataProvider));

        $reflection = new ReflectionProperty(DefaultDataProvider::class, 'source');
        self::assertSame('tl_dummy', $reflection->getValue($dataProvider));

        $reflection = new ReflectionProperty(DefaultDataProvider::class, 'idProperty');
        self::assertSame('id', $reflection->getValue($dataProvider));

        self::assertNull($dataProvider->getTimeStampProperty());
        self::assertNull($dataProvider->getIdGenerator());
    }

    public function testSetBaseConfigInvalidConnection(): void
    {
        $dataProvider = new DefaultDataProvider();

        try {
            $dataProvider->setBaseConfig(
                [
                    'source'   => 'tl_dummy',
                    'database' => '\Invalid\Connection'
                ]
            );
        } catch (Exception $exception) {
            self::assertInstanceOf(DcGeneralRuntimeException::class, $exception);
            self::assertSame('Invalid database connection.', $exception->getMessage());
        }
    }

    public function testSetBaseConfigForGetDefaultConnection(): void
    {
        $schemaTable = $this
            ->getMockBuilder(Table::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['hasColumn'])
            ->getMock();
        $schemaTable->method('hasColumn')->willReturn(false);

        $schemaManager = $this->mockSchemaManager($schemaTable);

        $connection = $this->mockConnection();
        $connection->method('createSchemaManager')->willReturn($schemaManager);

        $dataProvider = $this
            ->getMockBuilder(DefaultDataProvider::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getDefaultConnection'])
            ->getMock();
        $dataProvider->method('getDefaultConnection')->willReturn($connection);

        $dataProvider->setBaseConfig(
            [
                'source'   => 'tl_dummy'
            ]
        );

        $reflection = new ReflectionProperty(DefaultDataProvider::class, 'connection');
        self::assertInstanceOf(Connection::class, $reflection->getValue($dataProvider));
    }

    public function testSetBaseConfig(): void
    {
        $connection = $this->mockConnection();

        $idGenerator = $this->getMockBuilder(IdGeneratorInterface::class)->getMock();

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

        $reflection = new ReflectionProperty(DefaultDataProvider::class, 'connection');

        self::assertEquals('tl_something', $dataProvider->getEmptyModel()->getProviderName());
        self::assertEquals($connection, $reflection->getValue($dataProvider));
        self::assertEquals('idField', $dataProvider->getIdProperty());
        self::assertEquals('lastChanged', $dataProvider->getTimeStampProperty());
        self::assertSame($idGenerator, $dataProvider->getIdGenerator());
    }

    public function testGetEmptyConfig(): void
    {
        $provider = $this->mockDefaultProvider();
        self::assertInstanceOf(ConfigInterface::class, $provider->getEmptyConfig());
    }

    public function testGetEmptyModel(): void
    {
        $provider = $this->mockDefaultProvider();
        self::assertInstanceOf(ModelInterface::class, $provider->getEmptyModel());
    }

    public function testGetEmptyCollection(): void
    {
        $provider = $this->mockDefaultProvider();
        self::assertInstanceOf(CollectionInterface::class, $provider->getEmptyCollection());
    }

    public function testGetDefaultConnection(): void
    {
        self::markTestSkipped('This method is not testable.');
    }
}
