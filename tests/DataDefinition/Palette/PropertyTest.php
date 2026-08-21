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

namespace ContaoCommunityAlliance\DcGeneral\Test\DataDefinition\Palette;

use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Condition\Property\PropertyVisibleCondition;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Legend;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Property;
use ContaoCommunityAlliance\DcGeneral\Test\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Property::class)]
final class PropertyTest extends TestCase
{
    /**
     * Two properties whose visible conditions refer to each other used to recurse until the
     * PHP engine aborted with "Infinite recursion?" - see
     * contao-community-alliance/dc-general#528.
     */
    public function testDirectCycleResolvesToInvisibleInsteadOfExhaustingTheStack(): void
    {
        $legend = new Legend('test');
        $propertyA = new Property('feld_a');
        $propertyB = new Property('feld_b');

        $propertyA->setVisibleCondition(new PropertyVisibleCondition('feld_b'));
        $propertyB->setVisibleCondition(new PropertyVisibleCondition('feld_a'));

        $legend->addProperty($propertyA);
        $legend->addProperty($propertyB);

        self::assertFalse($propertyA->isVisible(null, null, $legend));
    }

    /**
     * The cycle may close over any number of intermediate properties, not only two.
     */
    public function testIndirectCycleResolvesToInvisible(): void
    {
        $legend = new Legend('test');
        $propertyA = new Property('feld_a');
        $propertyB = new Property('feld_b');
        $propertyC = new Property('feld_c');

        $propertyA->setVisibleCondition(new PropertyVisibleCondition('feld_b'));
        $propertyB->setVisibleCondition(new PropertyVisibleCondition('feld_c'));
        $propertyC->setVisibleCondition(new PropertyVisibleCondition('feld_a'));

        $legend->addProperty($propertyA);
        $legend->addProperty($propertyB);
        $legend->addProperty($propertyC);

        self::assertFalse($propertyA->isVisible(null, null, $legend));
    }

    /**
     * The cycle guard must not leave state behind that would poison an unrelated, later
     * evaluation - neither for the properties involved in the cycle nor for others.
     */
    public function testGuardIsClearedAfterACycleWasDetected(): void
    {
        $legend = new Legend('test');
        $propertyA = new Property('feld_a');
        $propertyB = new Property('feld_b');

        $propertyA->setVisibleCondition(new PropertyVisibleCondition('feld_b'));
        $propertyB->setVisibleCondition(new PropertyVisibleCondition('feld_a'));

        $legend->addProperty($propertyA);
        $legend->addProperty($propertyB);

        self::assertFalse($propertyA->isVisible(null, null, $legend));
        // Re-evaluating afterwards must behave exactly the same, not throw due to leftover guard
        // state from the previous, unrelated call.
        self::assertFalse($propertyA->isVisible(null, null, $legend));
        self::assertFalse($propertyB->isVisible(null, null, $legend));
    }

    /**
     * A property referring to itself is the smallest possible cycle.
     */
    public function testSelfReferenceResolvesToInvisible(): void
    {
        $legend = new Legend('test');
        $propertyA = new Property('feld_a');
        $propertyA->setVisibleCondition(new PropertyVisibleCondition('feld_a'));

        $legend->addProperty($propertyA);

        self::assertFalse($propertyA->isVisible(null, null, $legend));
    }

    /**
     * Chained, non-cyclic conditions must keep working as before.
     */
    public function testNonCyclicChainStillResolves(): void
    {
        $legend = new Legend('test');
        $propertyA = new Property('feld_a');
        $propertyB = new Property('feld_b');

        $propertyA->setVisibleCondition(new PropertyVisibleCondition('feld_b'));

        $legend->addProperty($propertyA);
        $legend->addProperty($propertyB);

        self::assertTrue($propertyA->isVisible(null, null, $legend));
    }
}
