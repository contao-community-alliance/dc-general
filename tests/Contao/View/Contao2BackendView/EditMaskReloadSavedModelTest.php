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

namespace ContaoCommunityAlliance\DcGeneral\Test\Contao\View\Contao2BackendView;

use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\EditMask;
use ContaoCommunityAlliance\DcGeneral\Data\ConfigInterface;
use ContaoCommunityAlliance\DcGeneral\Data\DataProviderInterface;
use ContaoCommunityAlliance\DcGeneral\Data\DefaultConfig;
use ContaoCommunityAlliance\DcGeneral\Data\ModelInterface;
use ContaoCommunityAlliance\DcGeneral\Test\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * A version has to be taken from the record as it is stored - values derived while saving (aliases, combined values)
 * are not part of the model that has been edited.
 */
#[CoversClass(EditMask::class)]
final class EditMaskReloadSavedModelTest extends TestCase
{
    public function testReturnsTheModelLoadedFromTheDataProvider(): void
    {
        $edited = $this->createModel('28');
        $stored = $this->createModel('28');

        $config   = null;
        $provider = $this->createStub(DataProviderInterface::class);
        $provider->method('getEmptyConfig')->willReturn(DefaultConfig::init());
        $provider->method('fetch')->willReturnCallback(
            static function (ConfigInterface $given) use (&$config, $stored): ModelInterface {
                $config = $given;

                return $stored;
            }
        );

        self::assertSame($stored, $this->reload($provider, $edited));
        self::assertSame('28', $config?->getId());
    }

    public function testKeepsTheModelWhenTheRecordCanNotBeFound(): void
    {
        $edited   = $this->createModel('28');
        $provider = $this->createStub(DataProviderInterface::class);
        $provider->method('getEmptyConfig')->willReturn(DefaultConfig::init());
        $provider->method('fetch')->willReturn(null);

        self::assertSame($edited, $this->reload($provider, $edited));
    }

    public function testKeepsAModelWithoutId(): void
    {
        $edited   = $this->createModel(null);
        $provider = $this->createMock(DataProviderInterface::class);
        $provider->expects(self::never())->method('fetch');

        self::assertSame($edited, $this->reload($provider, $edited));
    }

    private function reload(DataProviderInterface $provider, ModelInterface $model): ModelInterface
    {
        $editMask = (new \ReflectionClass(EditMask::class))->newInstanceWithoutConstructor();
        $method   = new \ReflectionMethod(EditMask::class, 'reloadSavedModel');

        return $method->invoke($editMask, $provider, $model);
    }

    private function createModel(?string $id): ModelInterface
    {
        $model = $this->createStub(ModelInterface::class);
        $model->method('getId')->willReturn($id);

        return $model;
    }
}
