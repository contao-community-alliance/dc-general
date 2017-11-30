<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2017 Contao Community Alliance.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    contao-community-alliance/dc-general
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2013-2017 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Test\DataDefinition\Definition\Properties;

use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\Properties\DefaultProperty;
use ContaoCommunityAlliance\DcGeneral\Test\TestCase;
use ReflectionClass;

/**
 * This tests DefaultProperty
 *
 * @coversDefaultClass \ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\Properties\DefaultProperty
 */
class DefaultPropertyTest extends TestCase
{
    /**
     * Get a property.
     *
     * @param string $name The property name.
     *
     * @return DefaultProperty
     */
    private function getProperty($name)
    {
        return new DefaultProperty($name);
    }

    /**
     * Test the construct.
     *
     * @covers ::__construct
     */
    public function testConstruct()
    {
        $property = $this->getProperty('dummy');

        $reflector = new ReflectionClass(get_class($property));
        $reflector->newInstanceWithoutConstructor();
    }

    /**
     * Test the property name.
     *
     * @covers ::getName
     */
    public function testName()
    {
        $property1 = $this->getProperty('dummy-1');

        $this->assertInternalType('string', $property1->getName());
        $this->assertSame('dummy-1', $property1->getName());
        $this->assertNotSame('dummy-foo', $property1->getName());
    }

    /**
     * Test the property label.
     *
     * @covers ::setLabel
     * @covers ::getLabel
     */
    public function testLabel()
    {
        $property1 = $this->getProperty('dummy-1');
        $property2 = $this->getProperty('dummy-2');

        $property1->setLabel('test-label');

        $this->assertInternalType('string', $property1->getLabel());
        $this->assertSame('test-label', $property1->getLabel());
        $this->assertNotSame('test-label-foo', $property1->getLabel());

        $this->assertNull($property2->getLabel());
        $this->assertNotSame('test-label', $property2->getLabel());
        $this->assertNull($property2->getLabel());
    }

    /**
     * Test the property description.
     *
     * @covers ::setDescription
     * @covers ::getDescription
     */
    public function testDescription()
    {
        $property1 = $this->getProperty('dummy-1');
        $property2 = $this->getProperty('dummy-2');

        $property1->setDescription('test-description');

        $this->assertInternalType('string', $property1->getDescription());
        $this->assertSame('test-description', $property1->getDescription());
        $this->assertNotSame('test-description-foo', $property1->getDescription());

        $this->assertNull($property2->getDescription());
        $this->assertNotSame('test-description', $property2->getDescription());
        $this->assertNull($property2->getDescription());
    }

    /**
     * Test the property defaultValue.
     *
     * @covers ::setDefaultValue
     * @covers ::getDefaultValue
     */
    public function testDefaultValue()
    {
        $property1 = $this->getProperty('dummy-1');
        $property2 = $this->getProperty('dummy-2');

        $property1->setDefaultValue('test-defaultValue');

        $this->assertInternalType('string', $property1->getDefaultValue());
        $this->assertSame('test-defaultValue', $property1->getDefaultValue());
        $this->assertNotSame('test-defaultValue-foo', $property1->getDefaultValue());

        $this->assertNull($property2->getDefaultValue());
        $this->assertNotSame('test-defaultValue', $property2->getDefaultValue());
        $this->assertNull($property2->getDefaultValue());
    }

    /**
     * Test the property excluded.
     *
     * @covers ::setExcluded
     * @covers ::isExcluded
     */
    public function testExcluded()
    {
        $property1 = $this->getProperty('dummy-1');
        $property2 = $this->getProperty('dummy-2');
        $property3 = $this->getProperty('dummy-3');

        $property1->setExcluded(true);
        $property2->setExcluded(false);

        $this->assertInternalType('bool', $property1->isExcluded());
        $this->assertTrue($property1->isExcluded());
        $this->assertNotFalse($property1->isExcluded());


        $this->assertInternalType('bool', $property2->isExcluded());
        $this->assertNotTrue($property2->isExcluded());
        $this->assertFalse($property2->isExcluded());

        $this->assertNull($property3->isExcluded());
        $this->assertNotTrue($property3->isExcluded());
        $this->assertNull($property3->isExcluded());
    }

    /**
     * Test the property searchable.
     *
     * @covers ::setSearchable
     * @covers ::isSearchable
     */
    public function testSearchable()
    {
        $property1 = $this->getProperty('dummy-1');
        $property2 = $this->getProperty('dummy-2');
        $property3 = $this->getProperty('dummy-3');

        $property1->setSearchable(true);
        $property2->setSearchable(false);

        $this->assertInternalType('bool', $property1->isSearchable());
        $this->assertTrue($property1->isSearchable());
        $this->assertNotFalse($property1->isSearchable());


        $this->assertInternalType('bool', $property2->isSearchable());
        $this->assertNotTrue($property2->isSearchable());
        $this->assertFalse($property2->isSearchable());

        $this->assertNull($property3->isSearchable());
        $this->assertNotTrue($property3->isSearchable());
        $this->assertNull($property3->isSearchable());
    }

    /**
     * Test the property filterable.
     *
     * @covers ::setFilterable
     * @covers ::isFilterable
     */
    public function testFilterable()
    {
        $property1 = $this->getProperty('dummy-1');
        $property2 = $this->getProperty('dummy-2');
        $property3 = $this->getProperty('dummy-3');

        $property1->setFilterable(true);
        $property2->setFilterable(false);

        $this->assertInternalType('bool', $property1->isFilterable());
        $this->assertTrue($property1->isFilterable());
        $this->assertNotFalse($property1->isFilterable());

        $this->assertInternalType('bool', $property2->isFilterable());
        $this->assertNotTrue($property2->isFilterable());
        $this->assertFalse($property2->isFilterable());

        $this->assertNull($property3->isFilterable());
        $this->assertNotTrue($property3->isFilterable());
        $this->assertNull($property3->isFilterable());
    }

    /**
     * Test the property widgetType.
     *
     * @covers ::setWidgetType
     * @covers ::getWidgetType
     */
    public function testWidgetType()
    {
        $property1 = $this->getProperty('dummy-1');
        $property2 = $this->getProperty('dummy-2');

        $property1->setWidgetType('test-widget');

        $this->assertInternalType('string', $property1->getWidgetType());
        $this->assertSame('test-widget', $property1->getWidgetType());
        $this->assertNotSame('test-widget-foo', $property1->getWidgetType());

        $this->assertNull($property2->getWidgetType());
        $this->assertNotSame('test-widget', $property2->getWidgetType());
        $this->assertNull($property2->getWidgetType());
    }

    /**
     * Test the property options.
     *
     * @covers ::setOptions
     * @covers ::getOptions
     */
    public function testOptions()
    {
        $property1 = $this->getProperty('dummy-1');
        $property2 = $this->getProperty('dummy-2');

        $property1->setOptions(array('test-options'));

        $this->assertInternalType('array', $property1->getOptions());
        $this->assertArrayHasKey(0, $property1->getOptions());
        $this->assertArrayNotHasKey(1, $property1->getOptions());
        $this->assertContains('test-options', $property1->getOptions());
        $this->assertNotContains('test-options-foo', $property1->getOptions());

        $this->assertNull($property2->getOptions());
        $this->assertArrayNotHasKey(0, (array) $property2->getOptions());
        $this->assertArrayNotHasKey(1, (array) $property2->getOptions());
        $this->assertNotContains('test-options', (array) $property2->getOptions());
        $this->assertNotContains('test-options-foo', (array) $property2->getOptions());
    }

    /**
     * Test the property explanation.
     *
     * @covers ::setExplanation
     * @covers ::getExplanation
     */
    public function testExplanation()
    {
        $property1 = $this->getProperty('dummy-1');
        $property2 = $this->getProperty('dummy-2');

        $property1->setExplanation('test-explanation');

        $this->assertInternalType('string', $property1->getExplanation());
        $this->assertSame('test-explanation', $property1->getExplanation());
        $this->assertNotSame('test-explanation-foo', $property1->getExplanation());

        $this->assertNull($property2->getExplanation());
        $this->assertNotSame('test-explanation', $property2->getExplanation());
        $this->assertNull($property2->getExplanation());
    }

    /**
     * Test the property extra.
     *
     * @covers ::setExtra
     * @covers ::getExtra
     */
    public function testExtra()
    {
        $property1 = $this->getProperty('dummy-1');
        $property2 = $this->getProperty('dummy-2');

        $property1->setExtra(array('test-extra'));

        $this->assertInternalType('array', $property1->getExtra());
        $this->assertArrayHasKey(0, $property1->getExtra());
        $this->assertArrayNotHasKey(1, $property1->getExtra());
        $this->assertContains('test-extra', $property1->getExtra());
        $this->assertNotContains('test-extra-foo', $property1->getExtra());

        $this->assertInternalType('array', $property2->getExtra());
        $this->assertArrayNotHasKey(0, $property2->getExtra());
        $this->assertArrayNotHasKey(1, $property1->getExtra());
        $this->assertNotContains('test-extra', $property2->getExtra());
        $this->assertNotContains('test-extra-foo', $property2->getExtra());
    }
}
