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

use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\ViewHelpers;
use ContaoCommunityAlliance\DcGeneral\Data\DefaultModel;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\Properties\DefaultProperty;
use ContaoCommunityAlliance\DcGeneral\DefaultEnvironment;
use ContaoCommunityAlliance\DcGeneral\Test\TestCase;
use ContaoCommunityAlliance\DcGeneral\View\Event\RenderReadablePropertyValueEvent;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\EventDispatcher\EventDispatcher;

/**
 * The readable value is printed as HTML by the show view, the group headers and the labels.
 */
#[CoversClass(ViewHelpers::class)]
final class ViewHelpersReadableValueTest extends TestCase
{
    private function readable(mixed $value, ?string $rendered = null): mixed
    {
        $dispatcher = new EventDispatcher();
        if (null !== $rendered) {
            $dispatcher->addListener(
                RenderReadablePropertyValueEvent::NAME,
                static function (RenderReadablePropertyValueEvent $event) use ($rendered): void {
                    $event->setRendered($rendered);
                }
            );
        }

        $environment = new DefaultEnvironment();
        $environment->setEventDispatcher($dispatcher);
        $model = new DefaultModel();
        $model->setProperty('title', $value);

        return ViewHelpers::getReadableFieldValue($environment, new DefaultProperty('title'), $model);
    }

    public function testEscapesPlainStrings(): void
    {
        self::assertSame(
            '&lt;img src=x onerror=alert(1)&gt; &amp; &quot;q&quot;',
            $this->readable('<img src=x onerror=alert(1)> & "q"')
        );
    }

    public function testKeepsWhatARendererHasProduced(): void
    {
        self::assertSame('<b>rendered</b>', $this->readable('raw', '<b>rendered</b>'));
    }

    public function testLeavesOtherTypesAlone(): void
    {
        self::assertSame(5, $this->readable(5));
        self::assertNull($this->readable(null));
    }
}
