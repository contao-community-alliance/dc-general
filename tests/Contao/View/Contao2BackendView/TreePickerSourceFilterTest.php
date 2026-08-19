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

use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\TreePicker;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\ContainerInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\DefaultContainer;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\DefaultBasicDefinition;
use ContaoCommunityAlliance\DcGeneral\DcGeneral;
use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;
use ContaoCommunityAlliance\DcGeneral\Test\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use ReflectionClass;

/**
 * This tests that the picker only offers what the caller allows.
 *
 * The picker builds its own container for the source table. Without being told otherwise it
 * offers every record in it, even when the list beside it shows a filtered selection.
 */
#[CoversClass(TreePicker::class)]
final class TreePickerSourceFilterTest extends TestCase
{
    /**
     * The ids the caller allows have to end up as a filter on the target container.
     *
     * @return void
     */
    public function testLimitsToTheAllowedIds(): void
    {
        $definition = $this->buildPicker(['3', '7'])->getEnvironment()->getDataDefinition();

        self::assertInstanceOf(ContainerInterface::class, $definition);
        self::assertSame(
            [['property' => 'id', 'operation' => 'IN', 'values' => ['3', '7']]],
            $definition->getBasicDefinition()->getAdditionalFilter('tl_member')
        );
    }

    /**
     * Without the option nothing is filtered - the picker keeps offering the whole table.
     *
     * @return void
     */
    public function testLeavesTheContainerAloneWithoutTheOption(): void
    {
        $definition = $this->buildPicker(null)->getEnvironment()->getDataDefinition();

        self::assertInstanceOf(ContainerInterface::class, $definition);
        self::assertFalse($definition->getBasicDefinition()->hasAdditionalFilter('tl_member'));
    }

    /**
     * An empty list means "nothing matches", not "no filter at all".
     *
     * The caller asked for a filter and it came out empty. Offering the whole table instead
     * would be the opposite answer to the question, so the filter has to be set either way.
     *
     * @return void
     */
    public function testAnEmptyListMatchesNothing(): void
    {
        $definition = $this->buildPicker([])->getEnvironment()->getDataDefinition();

        self::assertInstanceOf(ContainerInterface::class, $definition);
        self::assertSame(
            [['property' => 'id', 'operation' => 'IN', 'values' => []]],
            $definition->getBasicDefinition()->getAdditionalFilter('tl_member')
        );
    }

    /**
     * The ids are matched against whatever the field declared as its id property.
     *
     * @return void
     */
    public function testUsesTheConfiguredIdProperty(): void
    {
        $definition = $this->buildPicker(['first'], 'alias')->getEnvironment()->getDataDefinition();

        self::assertInstanceOf(ContainerInterface::class, $definition);
        self::assertSame(
            [['property' => 'alias', 'operation' => 'IN', 'values' => ['first']]],
            $definition->getBasicDefinition()->getAdditionalFilter('tl_member')
        );
    }

    /**
     * Gaps in the list must not turn the values into an object when they get encoded.
     *
     * Callers hand over whatever their own query produced, and that is rarely a clean list.
     *
     * @return void
     */
    public function testKeepsTheValuesAList(): void
    {
        $definition = $this->buildPicker([5 => '3', 9 => '7'])->getEnvironment()->getDataDefinition();

        self::assertInstanceOf(ContainerInterface::class, $definition);
        self::assertSame(
            [['property' => 'id', 'operation' => 'IN', 'values' => ['3', '7']]],
            $definition->getBasicDefinition()->getAdditionalFilter('tl_member')
        );
    }

    /**
     * Build a picker around a bare container and let it apply its source filter.
     *
     * The widget is created without its constructor - all it needs here is the magic setters
     * Contao provides and the container the filter is meant to land on.
     *
     * @param array<int, string>|null $sourceFilter The ids to hand over, null to omit the option.
     * @param string|null             $idProperty   The property the ids refer to.
     *
     * @return DcGeneral
     */
    private function buildPicker(?array $sourceFilter, ?string $idProperty = null): DcGeneral
    {
        $container = new DefaultContainer('tl_member');
        $container->setBasicDefinition(new DefaultBasicDefinition());

        $environment = $this->createMock(EnvironmentInterface::class);
        $environment->method('getDataDefinition')->willReturn($container);

        $itemContainer = $this->createMock(DcGeneral::class);
        $itemContainer->method('getEnvironment')->willReturn($environment);

        $reflection = new ReflectionClass(TreePicker::class);
        $picker     = $reflection->newInstanceWithoutConstructor();

        $picker->sourceName = 'tl_member';
        if (null !== $idProperty) {
            $picker->idProperty = $idProperty;
        }
        if (null !== $sourceFilter) {
            $picker->sourceFilter = $sourceFilter;
        }

        $property = $reflection->getProperty('itemContainer');
        $property->setValue($picker, $itemContainer);

        $method = $reflection->getMethod('applySourceFilter');
        $method->invoke($picker);

        return $itemContainer;
    }
}
