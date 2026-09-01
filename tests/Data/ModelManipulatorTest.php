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

namespace ContaoCommunityAlliance\DcGeneral\Test\Data;

use ContaoCommunityAlliance\DcGeneral\Data\DefaultModel;
use ContaoCommunityAlliance\DcGeneral\Data\ModelInterface;
use ContaoCommunityAlliance\DcGeneral\Data\ModelManipulator;
use ContaoCommunityAlliance\DcGeneral\Data\PropertyValueBag;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\DefaultPropertiesDefinition;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\Properties\DefaultProperty;
use ContaoCommunityAlliance\DcGeneral\Test\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Tests for ModelManipulator::updateModelFromPropertyBag() - specifically the "doNotSaveEmpty" eval flag, which
 * Contao's own DC_Table honours (a property keeps its stored value when an empty one arrives) but which this class
 * silently ignored, see ".claude/dcg-donotsaveempty.md".
 */
#[CoversClass(ModelManipulator::class)]
final class ModelManipulatorTest extends TestCase
{
    private function createProperties(array $extra): DefaultPropertiesDefinition
    {
        $property = new DefaultProperty('theProperty');
        $property->setExtra($extra);

        $properties = new DefaultPropertiesDefinition();
        $properties->addProperty($property);

        return $properties;
    }

    public function testDoNotSaveEmptyKeepsTheStoredValueWhenAnEmptyStringArrives(): void
    {
        $properties = $this->createProperties(['doNotSaveEmpty' => true]);
        $model      = new DefaultModel();
        $model->setPropertyRaw('theProperty', 'stored value');

        ModelManipulator::updateModelFromPropertyBag(
            $properties,
            $model,
            new PropertyValueBag(['theProperty' => ''])
        );

        self::assertSame('stored value', $model->getProperty('theProperty'));
    }

    public function testDoNotSaveEmptyDoesNotBlockANonEmptyValue(): void
    {
        $properties = $this->createProperties(['doNotSaveEmpty' => true]);
        $model      = new DefaultModel();
        $model->setPropertyRaw('theProperty', 'stored value');

        ModelManipulator::updateModelFromPropertyBag(
            $properties,
            $model,
            new PropertyValueBag(['theProperty' => 'new value'])
        );

        self::assertSame('new value', $model->getProperty('theProperty'));
    }

    public function testDoNotSaveEmptyDoesNotTreatAnEmptyArrayAsEmpty(): void
    {
        // Matches Contao's own DC_Table::save(): "is_array($varValue) || (string) $varValue !== ''" - an array
        // is never "empty" in this sense, even an empty one.
        $properties = $this->createProperties(['doNotSaveEmpty' => true]);
        $model      = new DefaultModel();
        $model->setPropertyRaw('theProperty', ['a', 'b']);

        ModelManipulator::updateModelFromPropertyBag(
            $properties,
            $model,
            new PropertyValueBag(['theProperty' => []])
        );

        self::assertSame([], $model->getProperty('theProperty'));
    }

    public function testWithoutDoNotSaveEmptyAnEmptyValueOverwritesTheStoredOne(): void
    {
        $properties = $this->createProperties([]);
        $model      = new DefaultModel();
        $model->setPropertyRaw('theProperty', 'stored value');

        ModelManipulator::updateModelFromPropertyBag(
            $properties,
            $model,
            new PropertyValueBag(['theProperty' => ''])
        );

        self::assertSame('', $model->getProperty('theProperty'));
    }

    public function testDoNotSaveEmptyLeavesTheModelUnchangedWhenTheStoredValueIsKept(): void
    {
        $properties = $this->createProperties(['doNotSaveEmpty' => true]);
        $model      = new DefaultModel();
        $model->setPropertyRaw('theProperty', 'stored value');

        ModelManipulator::updateModelFromPropertyBag(
            $properties,
            $model,
            new PropertyValueBag(['theProperty' => ''])
        );

        self::assertNotTrue($model->getMeta(ModelInterface::IS_CHANGED));
    }
}
