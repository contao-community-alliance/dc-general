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

use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\Properties\DefaultCompoundProperty;

/**
 * This tests DefaultCompoundProperty
 *
 * @coversDefaultClass \ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\Properties\DefaultCompoundProperty
 */
class DefaultCompoundPropertyTest extends DefaultPropertyTest
{
    /**
     * Get a property.
     *
     * @param string $name The compound property name.
     *
     * @return DefaultCompoundProperty
     */
    private function getProperty($name)
    {
        return new DefaultCompoundProperty($name);
    }

    /**
     * Test for clear the properties.
     *
     * @covers ::clearProperties
     * @covers ::addProperty
     * @covers ::getProperties
     * @covers ::hasProperty
     */
    public function testClearPropertiesCollection()
    {
        $property = $this->getProperty('dummy');

        $this->assertSame(array(), $property->getProperties());

        $subProperty = $this->getProperty('dummy-collection-property');

        $property->addProperty($subProperty);

        $this->assertSame(array($subProperty->getName() => $subProperty), $property->getProperties());
        $this->assertArrayHasKey('dummy-collection-property', $property->getProperties());
        $this->assertArrayNotHasKey('dummy-collection-property-foo', $property->getProperties());
        $this->assertTrue($property->hasProperty('dummy-collection-property'));
        $this->assertFalse($property->hasProperty('dummy-collection-property-foo'));

        $property->clearProperties();
        $this->assertSame(array(), $property->getProperties());
        $this->assertArrayNotHasKey('dummy-collection', $property->getProperties());
        $this->assertFalse($property->hasProperty('dummy-collection-property'));
        $this->assertFalse($property->hasProperty('dummy-collection-property-foo'));
    }

    /**
     * Test for get the properties.
     *
     * @covers ::getProperties
     * @covers ::addProperty
     * @covers ::hasProperty
     * @covers ::clearProperties
     */
    public function testGetPropertiesFromCollection()
    {
        $property = $this->getProperty('dummy');

        $this->assertSame(array(), $property->getProperties());

        $subProperty = $this->getProperty('dummy-collection-property');

        $property->addProperty($subProperty);

        $this->assertSame(array($subProperty->getName() => $subProperty), $property->getProperties());
        $this->assertArrayHasKey('dummy-collection-property', $property->getProperties());
        $this->assertArrayNotHasKey('dummy-collection-property-foo', $property->getProperties());
        $this->assertTrue($property->hasProperty('dummy-collection-property'));
        $this->assertFalse($property->hasProperty('dummy-collection-property-foo'));

        $property->clearProperties();
        $this->assertSame(array(), $property->getProperties());
        $this->assertArrayNotHasKey('dummy-collection-property', $property->getProperties());
        $this->assertFalse($property->hasProperty('dummy-collection-property'));
        $this->assertFalse($property->hasProperty('dummy-collection-property-foo'));
    }

    /**
     * Test for get the properties.
     *
     * @covers ::setProperties
     * @covers ::addProperty
     * @covers ::getProperties
     * @covers ::clearProperties
     * @covers ::hasProperty
     */
    public function testSetPropertiesInCollection()
    {
        $property = $this->getProperty('dummy');

        $this->assertSame(array(), $property->getProperties());

        $subProperty1 = $this->getProperty('dummy-collection-property-1');
        $subProperty2 = $this->getProperty('dummy-collection-property-2');

        $property->addProperty($subProperty1);

        $this->assertSame(array($subProperty1->getName() => $subProperty1), $property->getProperties());
        $this->assertArrayHasKey($subProperty1->getName(), $property->getProperties());
        $this->assertArrayNotHasKey($subProperty2->getName(), $property->getProperties());
        $this->assertTrue($property->hasProperty($subProperty1->getName()));
        $this->assertFalse($property->hasProperty($subProperty2->getName()));

        $property->setProperties(array($subProperty2));

        $this->assertSame(array($subProperty2->getName() => $subProperty2), $property->getProperties());
        $this->assertArrayNotHasKey($subProperty1->getName(), $property->getProperties());
        $this->assertArrayHasKey($subProperty2->getName(), $property->getProperties());
        $this->assertFalse($property->hasProperty($subProperty1->getName()));
        $this->assertTrue($property->hasProperty($subProperty2->getName()));
    }

    /**
     * Test get property.
     *
     * @covers ::getProperty
     * @covers ::addProperty
     * @covers ::hasProperty
     */
    public function testGetPropertyFromCollection()
    {
        $property    = $this->getProperty('dummy-property');
        $subProperty = $this->getProperty('dummy-collection-property');

        $property->addProperty($subProperty);

        $this->assertSame($subProperty, $property->getProperty($subProperty->getName()));
    }

    /**
     * Test get property.
     *
     * @expectedException \ContaoCommunityAlliance\DcGeneral\Exception\DcGeneralInvalidArgumentException
     *
     * @covers ::getProperty
     * @covers ::hasProperty
     */
    public function testGetPropertyFromCollectionNotRegisteredException()
    {
        $property    = $this->getProperty('dummy-property');
        $subProperty = $this->getProperty('dummy-collection-property');

        $this->setExpectedException(
            'ContaoCommunityAlliance\DcGeneral\Exception\DcGeneralInvalidArgumentException',
            'Property ' . $subProperty->getName() . ' is not registered.'
        );
        $property->getProperty($subProperty->getName());
    }

    /**
     * Test for get the properties.
     *
     * @covers ::removeProperty
     * @covers ::addProperty
     * @covers ::getProperties
     * @covers ::hasProperty
     */
    public function testRemovePropertyFromCollection()
    {
        $property = $this->getProperty('dummy');

        $this->assertSame(array(), $property->getProperties());

        $subProperty1 = $this->getProperty('dummy-collection-property-1');
        $subProperty2 = $this->getProperty('dummy-collection-property-2');

        $property->addProperty($subProperty1);
        $property->addProperty($subProperty2);

        $this->assertSame(
            array($subProperty1->getName() => $subProperty1, $subProperty2->getName() => $subProperty2),
            $property->getProperties()
        );
        $this->assertArrayHasKey($subProperty1->getName(), $property->getProperties());
        $this->assertArrayHasKey($subProperty2->getName(), $property->getProperties());
        $this->assertArrayNotHasKey('dummy-collection-property-foo', $property->getProperties());
        $this->assertTrue($property->hasProperty($subProperty1->getName()));
        $this->assertTrue($property->hasProperty($subProperty2->getName()));
        $this->assertFalse($property->hasProperty('dummy-collection-property-foo'));

        $property->removeProperty($subProperty1);

        $this->assertSame(
            array($subProperty2->getName() => $subProperty2),
            $property->getProperties()
        );
        $this->assertArrayNotHasKey($subProperty1->getName(), $property->getProperties());
        $this->assertArrayHasKey($subProperty2->getName(), $property->getProperties());
        $this->assertArrayNotHasKey('dummy-collection-property-foo', $property->getProperties());
        $this->assertFalse($property->hasProperty($subProperty1->getName()));
        $this->assertTrue($property->hasProperty($subProperty2->getName()));
        $this->assertFalse($property->hasProperty('dummy-collection-property-foo'));

        $property->removeProperty($subProperty2);

        $this->assertSame(array(), $property->getProperties());
        $this->assertArrayNotHasKey($subProperty1->getName(), $property->getProperties());
        $this->assertArrayNotHasKey($subProperty2->getName(), $property->getProperties());
        $this->assertArrayNotHasKey('dummy-collection-property-foo', $property->getProperties());
        $this->assertFalse($property->hasProperty($subProperty1->getName()));
        $this->assertFalse($property->hasProperty($subProperty2->getName()));
        $this->assertFalse($property->hasProperty('dummy-collection-property-foo'));
    }
}
