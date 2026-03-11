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

use ContaoCommunityAlliance\DcGeneral\Data\ModelId;
use ContaoCommunityAlliance\DcGeneral\Data\ModelInterface;
use ContaoCommunityAlliance\DcGeneral\Test\TestCase;
use ContaoCommunityAlliance\DcGeneral\Exception\DcGeneralInvalidArgumentException;
use ContaoCommunityAlliance\DcGeneral\Exception\DcGeneralRuntimeException;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * This class tests the ModelId class.
 */

#[AllowMockObjectsWithoutExpectations]
#[CoversMethod(ModelInterface::class, 'getId')]
#[CoversMethod(ModelInterface::class, 'getProviderName')]
#[CoversMethod(ModelId::class, 'fromModel')]
#[CoversMethod(ModelId::class, 'fromSerialized')]
#[CoversMethod(ModelId::class, 'getSerialized')]
final class ModelIdTest extends TestCase
{
    /**
     * Mock a model instance which will return the given values.
     *
     * @param mixed $modelId      The value to use as model id.
     * @param mixed $dataProvider The value to use as data provider.
     *
     * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
     *
     * @return ModelInterface
     */
    private function mockModel(mixed $modelId, mixed $dataProvider): ModelInterface
    {
        $mock = $this
            ->getMockBuilder(ModelInterface::class)
            ->getMock();
        $mock
            ->method('getId')
            ->willReturn($modelId);
        $mock
            ->method('getProviderName')
            ->willReturn($dataProvider);

        return $mock;
    }

    /**
     * Build a list of invalid models.
     *
     * @return array
     */
    public static function modelProvider(): array
    {
        $exception = DcGeneralInvalidArgumentException::class;
        return [
            [static fn (ModelIdTest $test) => $test->mockModel(10, 'tl_page')],
            [static fn (ModelIdTest $test) => $test->mockModel(null, 'tl_page')],
            [static fn (ModelIdTest $test) => $test->mockModel(null, null), $exception],
            [static fn (ModelIdTest $test) => $test->mockModel(10, null), $exception],
            [static fn (ModelIdTest $test) => $test->mockModel(10, ''), $exception],
            [static fn (ModelIdTest $test) => $test->mockModel(10, 0), $exception],
        ];
    }

    /**
     * Test that the ModelId class cannot be instantiated with invalid values.
     *
     * @param callable    $modelFactory The model factory to create the model to instantiate from.
     * @param string|null $exception    The name of the expected exception class.
     */
    #[Dataprovider('modelProvider')]
    public function testInstantiationFromModel(callable $modelFactory, ?string $exception = null): void
    {
        if (null !== $exception) {
            $this->expectException($exception);
        }
        $model = $modelFactory($this);

        $modelId = ModelId::fromModel($model);

        self::assertEquals($model->getId(), $modelId->getId());
        self::assertEquals($model->getProviderName(), $modelId->getDataProviderName());
    }

    /**
     * Data provider for the valid id test.
     */
    public static function idProvider(): array
    {
        $exception = DcGeneralRuntimeException::class;
        return [
            ['tl_page::1'],
            ['tl_page:1', $exception],
            [':1', $exception],
            ['1', $exception],
        ];
    }

    /**
     * Test valid model ids.
     *
     * @param string      $testId    The id to test.
     * @param string|null $exception The name of the expected exception class.
     */
    #[DataProvider(methodName: 'idProvider')]
    public function testValidIds(string $testId, ?string $exception = null): void
    {
        if (null !== $exception) {
            $this->expectException($exception);
        }

        self::assertEquals($testId, ModelId::fromSerialized($testId)->getSerialized());
    }
}
