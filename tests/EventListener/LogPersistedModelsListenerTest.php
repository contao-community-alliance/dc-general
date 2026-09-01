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
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2013-2026 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

declare(strict_types=1);

namespace ContaoCommunityAlliance\DcGeneral\Test\EventListener;

use ContaoCommunityAlliance\DcGeneral\Data\ModelInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\ContainerInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\DataProviderInformationInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\DataProviderDefinitionInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\LoggingInformationInterface;
use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;
use ContaoCommunityAlliance\DcGeneral\Event\PostDeleteModelEvent;
use ContaoCommunityAlliance\DcGeneral\Event\PostDuplicateModelEvent;
use ContaoCommunityAlliance\DcGeneral\Event\PostPersistModelEvent;
use ContaoCommunityAlliance\DcGeneral\EventListener\LogPersistedModelsListener;
use ContaoCommunityAlliance\DcGeneral\Test\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Psr\Log\LoggerInterface;

/**
 * Tests for LogPersistedModelsListener - see ".claude/dcg-systemlog.md" for the background.
 */
#[CoversClass(LogPersistedModelsListener::class)]
class LogPersistedModelsListenerTest extends TestCase
{
    private function mockModel(string $providerName, string $id): ModelInterface
    {
        $model = $this->createMock(ModelInterface::class);
        $model->method('getProviderName')->willReturn($providerName);
        $model->method('getId')->willReturn($id);

        return $model;
    }

    /**
     * What CreateHandler actually passes as the original model on a create -
     * DataProviderInterface::getEmptyModel(), not a literal null.
     */
    private function mockEmptyModel(): ModelInterface
    {
        $model = $this->createMock(ModelInterface::class);
        $model->method('getId')->willReturn(null);

        return $model;
    }

    /**
     * @param bool|null $loggingEnabled Null = the provider information does not implement
     *                                  LoggingInformationInterface at all ("not configured").
     */
    private function mockEnvironment(string $providerName, ?bool $loggingEnabled): EnvironmentInterface
    {
        if (null === $loggingEnabled) {
            $information = $this->createMock(DataProviderInformationInterface::class);
        } else {
            $information = $this->createMockForIntersectionOfInterfaces(
                [DataProviderInformationInterface::class, LoggingInformationInterface::class]
            );
            $information->method('isLoggingEnabled')->willReturn($loggingEnabled);
        }

        $providerDefinition = $this->createMock(DataProviderDefinitionInterface::class);
        $providerDefinition->method('hasInformation')->with($providerName)->willReturn(true);
        $providerDefinition->method('getInformation')->with($providerName)->willReturn($information);

        $definition = $this->createMock(ContainerInterface::class);
        $definition->method('getDataProviderDefinition')->willReturn($providerDefinition);

        $environment = $this->createMock(EnvironmentInterface::class);
        $environment->method('getDataDefinition')->willReturn($definition);

        return $environment;
    }

    public function testLogsTheCreationOfANewRecord(): void
    {
        $model       = $this->mockModel('tl_test', '5');
        $environment = $this->mockEnvironment('tl_test', true);

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())->method('info')->with('A new entry "tl_test.id=5" has been created');

        (new LogPersistedModelsListener($logger))->onPersist(new PostPersistModelEvent($environment, $model, null));
    }

    public function testLogsTheCreationOfANewRecordWhenTheOriginalModelIsAnEmptyPlaceholder(): void
    {
        // The real path (CreateHandler): the original model is never a literal null, only unset.
        $model       = $this->mockModel('tl_test', '5');
        $environment = $this->mockEnvironment('tl_test', true);

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())->method('info')->with('A new entry "tl_test.id=5" has been created');

        (new LogPersistedModelsListener($logger))
            ->onPersist(new PostPersistModelEvent($environment, $model, $this->mockEmptyModel()));
    }

    public function testDoesNotLogAnEditOfAnExistingRecord(): void
    {
        $model         = $this->mockModel('tl_test', '5');
        $originalModel = $this->mockModel('tl_test', '5');
        $environment   = $this->mockEnvironment('tl_test', true);

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->never())->method('info');

        (new LogPersistedModelsListener($logger))
            ->onPersist(new PostPersistModelEvent($environment, $model, $originalModel));
    }

    public function testLogsDuplicationWithBothIds(): void
    {
        $model       = $this->mockModel('tl_test', '9');
        $source      = $this->mockModel('tl_test', '5');
        $environment = $this->mockEnvironment('tl_test', true);

        $logger = $this->createMock(LoggerInterface::class);
        $logger
            ->expects($this->once())
            ->method('info')
            ->with('A new entry "tl_test.id=9" has been created by duplicating record "tl_test.id=5"');

        (new LogPersistedModelsListener($logger))
            ->onDuplicate(new PostDuplicateModelEvent($environment, $model, $source));
    }

    public function testLogsDeletion(): void
    {
        $model       = $this->mockModel('tl_test', '5');
        $environment = $this->mockEnvironment('tl_test', true);

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())->method('info')->with('DELETE FROM tl_test WHERE id=5');

        (new LogPersistedModelsListener($logger))->onDelete(new PostDeleteModelEvent($environment, $model));
    }

    public function testStaysSilentWhenLoggingIsDisabledForTheProvider(): void
    {
        $model       = $this->mockModel('tl_test', '5');
        $environment = $this->mockEnvironment('tl_test', false);

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->never())->method('info');

        (new LogPersistedModelsListener($logger))->onDelete(new PostDeleteModelEvent($environment, $model));
    }

    public function testLogsWhenTheProviderInformationDoesNotImplementTheLoggingInterface(): void
    {
        // "Not configured" counts as enabled - a foreign DataProviderInformationInterface
        // implementation must not silently lose logging Contao's own tables always had.
        $model       = $this->mockModel('tl_test', '5');
        $environment = $this->mockEnvironment('tl_test', null);

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())->method('info');

        (new LogPersistedModelsListener($logger))->onDelete(new PostDeleteModelEvent($environment, $model));
    }
}
