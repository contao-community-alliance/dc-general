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
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @copyright  2013-2019 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Test\Contao\View\Contao2BackendView\Subscriber;

use ContaoCommunityAlliance\DcGeneral\Contao\RequestScopeDeterminator;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Subscriber\CheckPermission;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\ContainerInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\PalettesDefinitionInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\Properties\PropertyInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\PropertiesDefinitionInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Condition\Property\BooleanCondition;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Condition\Property\PropertyConditionChain;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Condition\Property\PropertyConditionInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\PaletteInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Property;
use ContaoCommunityAlliance\DcGeneral\Factory\Event\BuildDataDefinitionEvent;
use ContaoCommunityAlliance\DcGeneral\Test\TestCase;
use PHPUnit_Framework_MockObject_MockObject;

/**
 * This tests the CheckPermission subscriber.
 */
class CheckPermissionTest extends TestCase
{
    /**
     * This tests the getSubscribedEvents method.
     *
     * @covers \ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Subscriber\CheckPermission::getSubscribedEvents()
     */
    public function testGetSubscribedEvents()
    {
        $events = CheckPermission::getSubscribedEvents();

        $this->assertCount(1, $events);
        $this->assertEquals([BuildDataDefinitionEvent::NAME], array_keys($events));
        $this->assertCount(4, $events[BuildDataDefinitionEvent::NAME]);
        $this->assertEquals('checkPermissionForProperties', $events[BuildDataDefinitionEvent::NAME][0][0]);
        $this->assertEquals('checkPermissionIsCreatable', $events[BuildDataDefinitionEvent::NAME][1][0]);
        $this->assertEquals('checkPermissionIsEditable', $events[BuildDataDefinitionEvent::NAME][2][0]);
        $this->assertEquals('checkPermissionIsDeletable', $events[BuildDataDefinitionEvent::NAME][3][0]);
    }

    /**
     * This tests the checkPermissionForProperties method.
     *
     * @covers \ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Subscriber\CheckPermission::checkPermissionForProperties()
     * @covers \ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Subscriber\CheckPermission::getVisibilityConditionChain()
     */
    public function testCheckPermissionForProperties()
    {
        $property11 = new Property('property11');
        $property12 = new Property('property12');
        $property12->setVisibleCondition(
            $prop12chain = $this->getMockBuilder(PropertyConditionInterface::class)->getMockForAbstractClass()
        );
        $propertyNotExist = new Property('property13');
        $property21       = new Property('property21');
        $property21->setVisibleCondition($prop21chain = new PropertyConditionChain([], PropertyConditionChain::OR_CONJUNCTION));
        $property22 = new Property('property22');
        $property22->setVisibleCondition($prop22chain = new PropertyConditionChain());
        $palette1 = $this->getMockBuilder(PaletteInterface::class)->getMockForAbstractClass();
        $palette2 = $this->getMockBuilder(PaletteInterface::class)->getMockForAbstractClass();

        $palettes = [$palette1, $palette2];

        $container = $this->getMockBuilder(ContainerInterface::class)->getMockForAbstractClass();

        $properties = $this->getMockBuilder(PropertiesDefinitionInterface::class)->getMockForAbstractClass();

        $palettesDefinition = $this->getMockBuilder(PalettesDefinitionInterface::class)->getMockForAbstractClass();

        $container->expects($this->once())->method('getPropertiesDefinition')->willReturn($properties);
        $container->expects($this->once())->method('getPalettesDefinition')->willReturn($palettesDefinition);
        $palettesDefinition->expects($this->once())->method('getPalettes')->willReturn($palettes);
        $palette1->expects($this->once())->method('getProperties')->willReturn(
            [$property11, $property12, $propertyNotExist]
        );
        $palette2->expects($this->once())->method('getProperties')->willReturn([$property21, $property22]);
        $properties->expects($this->exactly(4))->method('getProperty')->willReturnCallback(
            function ($name) {
                switch ($name) {
                    case 'property11':
                        return $this->mockProperty(true);
                    case 'property12':
                        return $this->mockProperty(false);
                    case 'property21':
                        return $this->mockProperty(true);
                    case 'property22':
                        return $this->mockProperty(true);
                    default:
                }

                return null;
            }
        );
        $properties->expects($this->exactly(5))->method('hasProperty')->willReturnCallback(
            function ($name) {
                return \in_array($name, ['property11', 'property12', 'property21', 'property22']);
            }
        );

        $event = new BuildDataDefinitionEvent($container);

        /** @var RequestScopeDeterminator|PHPUnit_Framework_MockObject_MockObject $determinator */
        $determinator = $this
            ->getMockBuilder(RequestScopeDeterminator::class)
            ->setMethods(['currentScopeIsBackend'])
            ->disableOriginalConstructor()
            ->getMock();
        $subscriber = new CheckPermission($determinator);
        $determinator->expects($this->once())->method('currentScopeIsBackend')->willReturn(true);
        $subscriber->checkPermissionForProperties($event);

        $this->assertInstanceOf(PropertyConditionChain::class, $property11->getVisibleCondition());
        $this->assertCount(1, $property11->getVisibleCondition()->getConditions());
        $this->assertInstanceOf(BooleanCondition::class, $property11->getVisibleCondition()->getConditions()[0]);
        $this->assertFalse($property11->getVisibleCondition()->getConditions()[0]->getValue());

        $this->assertInstanceOf(PropertyConditionChain::class, $property12->getVisibleCondition());
        $this->assertSame($prop12chain,  $property12->getVisibleCondition()->getConditions()[0]);
        $this->assertInstanceOf(BooleanCondition::class, $property12->getVisibleCondition()->getConditions()[1]);
        $this->assertTrue($property12->getVisibleCondition()->getConditions()[1]->getValue());

        $this->assertInstanceOf(PropertyConditionChain::class, $property21->getVisibleCondition());
        $this->assertSame($prop21chain,  $property21->getVisibleCondition()->getConditions()[0]);
        $this->assertInstanceOf(BooleanCondition::class, $property21->getVisibleCondition()->getConditions()[1]);
        $this->assertFalse($property21->getVisibleCondition()->getConditions()[1]->getValue());

        $chain = $property21->getVisibleCondition();
        $this->assertInstanceOf(PropertyConditionChain::class, $chain);
        $this->assertSame($prop21chain,  $chain->getConditions()[0]);
        $this->assertInstanceOf(BooleanCondition::class, $chain->getConditions()[1]);
        $this->assertFalse($chain->getConditions()[1]->getValue());

        $this->assertInstanceOf(PropertyConditionChain::class, $property22->getVisibleCondition());
        $this->assertSame($prop22chain, $property22->getVisibleCondition());
        $this->assertInstanceOf(BooleanCondition::class, $property22->getVisibleCondition()->getConditions()[0]);
        $this->assertFalse($property22->getVisibleCondition()->getConditions()[0]->getValue());


        $this->assertNull($propertyNotExist->getVisibleCondition());
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|PropertyInterface
     */
    private function mockProperty($isExcluded = false)
    {
        $mock = $this
            ->getMockBuilder(PropertyInterface::class)
            ->getMockForAbstractClass();
        $mock->expects($this->once())->method('isExcluded')->willReturn($isExcluded);

        return $mock;
    }
}
