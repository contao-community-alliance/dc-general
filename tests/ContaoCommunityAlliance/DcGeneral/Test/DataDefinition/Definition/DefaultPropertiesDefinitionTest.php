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

namespace ContaoCommunityAlliance\DcGeneral\Test\DataDefinition\Definition;

use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\DefaultPropertiesDefinition;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\Properties\DefaultProperty;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\Properties\DefaultCompoundProperty;
use ContaoCommunityAlliance\DcGeneral\Test\TestCase;

/**
 * This tests DefaultPropertiesDefinition
 *
 * @coversDefaultClass \ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\DefaultPropertiesDefinition
 */
class DefaultPropertiesDefinitionTest extends TestCase
{
    /**
     * Get the default properties definition.
     *
     * @return DefaultPropertiesDefinition
     */
    private function getDefinition()
    {
        return new DefaultPropertiesDefinition();
    }

    /**
     * Get a default property.
     *
     * @param string $name The property name.
     *
     * @return DefaultProperty
     */
    private function getDefaultProperty($name)
    {
        return new DefaultProperty($name);
    }

    /**
     * Get a default compound property.
     *
     * @param string $name The property name.
     *
     * @return DefaultCompoundProperty
     */
    private function getDefaultCompoundProperty($name)
    {
        return new DefaultCompoundProperty($name);
    }

    /**
     * Test add default property.
     *
     * @covers ::addProperty
     * @covers ::getProperties
     * @covers ::hasProperty
     */
    public function testAddDefaultProperty()
    {
        $definition = $this->getDefinition();
        $property   = $this->getDefaultProperty('dummy-property');

        $this->assertSame(array(), $definition->getProperties());
        $this->assertFalse($definition->hasProperty($property->getName()));
        $this->assertFalse($definition->hasProperty('dummy-property-foo'));

        $definition->addProperty($property);

        $this->assertSame(array($property->getName() => $property), $definition->getProperties());
        $this->assertTrue($definition->hasProperty($property->getName()));
        $this->assertFalse($definition->hasProperty('dummy-property-foo'));
    }

    /**
     * Test exceptions for method add properties.
     *
     * @covers ::addProperty
     */
    public function testAddPropertyExceptionInvalidInstance()
    {
        $definition = $this->getDefinition();
        $property   = $this->getDefaultProperty('dummy-property');

        $definition->addProperty($property);

        $this->setExpectedException(
            'ContaoCommunityAlliance\DcGeneral\Exception\DcGeneralInvalidArgumentException',
            'Passed value is not an instance of PropertyInterface.'
        );
        $definition->addProperty(array());
    }

    /**
     * Test exceptions for method add property.
     *
     * @covers ::addProperty
     */
    public function testAddPropertyExceptionPropertyExists()
    {
        $definition = $this->getDefinition();
        $property   = $this->getDefaultProperty('dummy-property');

        $definition->addProperty($property);

        $this->setExpectedException(
            'ContaoCommunityAlliance\DcGeneral\Exception\DcGeneralInvalidArgumentException',
            'Property ' . $property->getName() . ' is already registered.'
        );
        $definition->addProperty($property);
    }

    /**
     * Test remove default property.
     *
     * @covers ::removeProperty
     * @covers ::addProperty
     * @covers ::getProperties
     * @covers ::hasProperty
     */
    public function testRemoveDefaultProperty()
    {
        $definition = $this->getDefinition();
        $property   = $this->getDefaultProperty('dummy-property');

        $this->assertSame(array(), $definition->getProperties());
        $this->assertFalse($definition->hasProperty($property->getName()));
        $this->assertFalse($definition->hasProperty('dummy-property-foo'));

        $definition->addProperty($property);

        $this->assertSame(array($property->getName() => $property), $definition->getProperties());
        $this->assertTrue($definition->hasProperty($property->getName()));
        $this->assertFalse($definition->hasProperty('dummy-property-foo'));

        $definition->removeProperty($property);

        $this->assertSame(array(), $definition->getProperties());
        $this->assertFalse($definition->hasProperty($property->getName()));
        $this->assertFalse($definition->hasProperty('dummy-property-foo'));

        $definition->addProperty($property);
        $definition->removeProperty($property->getName());

        $this->assertSame(array(), $definition->getProperties());
        $this->assertFalse($definition->hasProperty($property->getName()));
        $this->assertFalse($definition->hasProperty('dummy-property-foo'));
    }

    /**
     * Test exceptions for method remove property.
     *
     * @covers ::removeProperty
     */
    public function testRemovePropertyExceptionPropertyNotExists()
    {
        $definition = $this->getDefinition();
        $property   = $this->getDefaultProperty('dummy-property');

        $this->setExpectedException(
            'ContaoCommunityAlliance\DcGeneral\Exception\DcGeneralInvalidArgumentException',
            'Property ' . $property->getName() . ' is not registered.'
        );
        $definition->removeProperty($property);
    }

    /**
     * Test get default property.
     *
     * @covers ::getProperty
     * @covers ::addProperty
     * @covers ::hasProperty
     */
    public function testGetDefaultProperty()
    {
        $definition = $this->getDefinition();
        $property   = $this->getDefaultProperty('dummy-property');

        $definition->addProperty($property);

        $this->assertSame($property, $definition->getProperty($property->getName()));
    }

    /**
     * Test exceptions for method get property.
     *
     * @covers ::getProperty
     * @covers ::hasProperty
     */
    public function testGetPropertyExceptionPropertyNotRegistered()
    {
        $definition = $this->getDefinition();
        $property   = $this->getDefaultProperty('dummy-property');

        $this->setExpectedException(
            'ContaoCommunityAlliance\DcGeneral\Exception\DcGeneralInvalidArgumentException',
            'Property ' . $property->getName() . ' is not registered.'
        );
        $definition->getProperty($property->getName());
    }

    /**
     * Test method get iterator.
     *
     * @covers ::getIterator
     */
    public function testGetIterator()
    {
        $definition = $this->getDefinition();

        $this->assertInstanceOf('ArrayIterator', $definition->getIterator());
    }

    /**
     * Test default compound property.
     *
     * @covers ::addProperty
     * @covers ::hasProperty
     *
     * @covers \ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\Properties\DefaultCompoundProperty::hasProperty
     * @covers \ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\Properties\DefaultCompoundProperty::getProperty
     */
    public function testDefaultCompoundProperty()
    {
        $definition = $this->getDefinition();
        $property = $this->getDefaultCompoundProperty('main');
        $subProperty = $this->getDefaultProperty('main__child');

        $property->addProperty($subProperty);

        $definition->addProperty($property);

        $this->assertTrue($definition->hasProperty($subProperty->getName()));
        $this->assertFalse($definition->hasProperty($subProperty->getName() . '__foo'));

        $this->assertSame($subProperty, $definition->getProperty($subProperty->getName()));

        $this->setExpectedException(
            'ContaoCommunityAlliance\DcGeneral\Exception\DcGeneralInvalidArgumentException',
            'Property ' . $subProperty->getName() . '__foo is not registered.'
        );
        $definition->getProperty($subProperty->getName() . '__foo');
    }
}
