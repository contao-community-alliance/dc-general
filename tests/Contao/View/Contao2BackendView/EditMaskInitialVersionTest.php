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
use ContaoCommunityAlliance\DcGeneral\Data\DataProviderInterface;
use ContaoCommunityAlliance\DcGeneral\Data\DefaultConfig;
use ContaoCommunityAlliance\DcGeneral\Data\ModelInterface;
use ContaoCommunityAlliance\DcGeneral\Test\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Contao compares two versions - the state before the first save has to be kept as a version of its own, otherwise
 * the first save yields a single version and there is nothing to compare.
 */
#[CoversClass(EditMask::class)]
final class EditMaskInitialVersionTest extends TestCase
{
    public function testSavesTheStoredRecordAsVersionWhenThereIsNone(): void
    {
        $stored   = $this->createStub(ModelInterface::class);
        $provider = $this->createMock(DataProviderInterface::class);
        $provider->method('getActiveVersion')->with('28')->willReturn(null);
        $provider->method('getEmptyConfig')->willReturn(DefaultConfig::init());
        $provider->method('fetch')->willReturn($stored);
        $provider->expects(self::once())->method('saveVersion')->with($stored, 'admin');

        $this->saveInitialVersion($provider, '28');
    }

    public function testKeepsExistingVersionsUntouched(): void
    {
        $provider = $this->createMock(DataProviderInterface::class);
        $provider->method('getActiveVersion')->willReturn(3);
        $provider->expects(self::never())->method('fetch');
        $provider->expects(self::never())->method('saveVersion');

        $this->saveInitialVersion($provider, '28');
    }

    public function testSavesNothingWhenTheRecordCanNotBeFound(): void
    {
        $provider = $this->createMock(DataProviderInterface::class);
        $provider->method('getActiveVersion')->willReturn(null);
        $provider->method('getEmptyConfig')->willReturn(DefaultConfig::init());
        $provider->method('fetch')->willReturn(null);
        $provider->expects(self::never())->method('saveVersion');

        $this->saveInitialVersion($provider, '28');
    }

    private function saveInitialVersion(DataProviderInterface $provider, string $modelId): void
    {
        $editMask = (new \ReflectionClass(EditMask::class))->newInstanceWithoutConstructor();
        $method   = new \ReflectionMethod(EditMask::class, 'saveInitialVersion');

        $method->invoke($editMask, $provider, $modelId, 'admin');
    }
}
