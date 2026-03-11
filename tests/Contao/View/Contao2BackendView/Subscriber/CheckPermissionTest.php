<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2025 Contao Community Alliance.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    contao-community-alliance/dc-general
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2013-2025 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Test\Contao\View\Contao2BackendView\Subscriber;

use Contao\CoreBundle\Security\ContaoCorePermissions;
use ContaoCommunityAlliance\DcGeneral\Contao\RequestScopeDeterminator;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Subscriber\CheckPermission;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\ConditionChainInterface;
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
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\SecurityBundle\Security;

/**
 * This tests the CheckPermission subscriber.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
#[AllowMockObjectsWithoutExpectations]
#[CoversMethod(CheckPermission::class, 'getSubscribedEvents')]
#[CoversMethod(CheckPermission::class, 'checkPermissionForProperties')]
#[CoversMethod(CheckPermission::class, 'getVisibilityConditionChain')]
final class CheckPermissionTest extends TestCase
{
    public function testGetSubscribedEvents(): void
    {
        $events = CheckPermission::getSubscribedEvents();

        self::assertCount(1, $events);
        self::assertEquals([BuildDataDefinitionEvent::NAME], array_keys($events));
        self::assertCount(4, $events[BuildDataDefinitionEvent::NAME]);
        self::assertEquals('checkPermissionForProperties', $events[BuildDataDefinitionEvent::NAME][0][0]);
        self::assertEquals('checkPermissionIsCreatable', $events[BuildDataDefinitionEvent::NAME][1][0]);
        self::assertEquals('checkPermissionIsEditable', $events[BuildDataDefinitionEvent::NAME][2][0]);
        self::assertEquals('checkPermissionIsDeletable', $events[BuildDataDefinitionEvent::NAME][3][0]);
    }

    /**
     * This tests the checkPermissionForProperties method.
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testCheckPermissionForProperties(): void
    {
        $property11 = new Property('property11');
        $property12 = new Property('property12');
        $property12->setVisibleCondition(
            $prop12chain = $this->getMockBuilder(PropertyConditionInterface::class)->getMock()
        );
        $propertyNotExist = new Property('property13');
        $property21       = new Property('property21');
        $property21->setVisibleCondition(
            $prop21chain = new PropertyConditionChain([], ConditionChainInterface::OR_CONJUNCTION)
        );
        $property22 = new Property('property22');
        $property22->setVisibleCondition($prop22chain = new PropertyConditionChain());
        $palette1 = $this->getMockBuilder(PaletteInterface::class)->getMock();
        $palette2 = $this->getMockBuilder(PaletteInterface::class)->getMock();

        $palettes = [$palette1, $palette2];

        $container = $this->getMockBuilder(ContainerInterface::class)->getMock();

        $properties = $this->getMockBuilder(PropertiesDefinitionInterface::class)->getMock();

        $palettesDefinition = $this->getMockBuilder(PalettesDefinitionInterface::class)->getMock();

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

        /** @var RequestScopeDeterminator|MockObject $determinator */
        $determinator = $this
            ->getMockBuilder(RequestScopeDeterminator::class)
            ->onlyMethods(['currentScopeIsBackend'])
            ->disableOriginalConstructor()
            ->getMock();
        /** @var Security|MockObject $determinator */
        $security   = $this
            ->getMockBuilder(Security::class)
            ->onlyMethods(['isGranted'])
            ->disableOriginalConstructor()
            ->getMock();
        $subscriber = new CheckPermission($determinator, $security);
        $determinator->expects($this->once())->method('currentScopeIsBackend')->willReturn(true);
        $security
            ->expects($this->exactly(3))
            ->method('isGranted')
            ->willReturnCallback(function (string $permission, string $fieldName): bool {
                static $invocation = 0;
                $fields = ['property11', 'property21', 'property22'];
                self::assertSame(ContaoCorePermissions::USER_CAN_EDIT_FIELD_OF_TABLE, $permission);
                self::assertSame('::' . $fields[$invocation++], $fieldName);

                return false;
            });
        $subscriber->checkPermissionForProperties($event);

        self::assertInstanceOf(PropertyConditionChain::class, $property11->getVisibleCondition());
        self::assertCount(1, $property11->getVisibleCondition()->getConditions());
        self::assertInstanceOf(BooleanCondition::class, $property11->getVisibleCondition()->getConditions()[0]);
        self::assertFalse($property11->getVisibleCondition()->getConditions()[0]->getValue());

        self::assertInstanceOf(PropertyConditionChain::class, $property12->getVisibleCondition());
        self::assertSame($prop12chain, $property12->getVisibleCondition()->getConditions()[0]);
        self::assertInstanceOf(BooleanCondition::class, $property12->getVisibleCondition()->getConditions()[1]);
        self::assertTrue($property12->getVisibleCondition()->getConditions()[1]->getValue());

        self::assertInstanceOf(PropertyConditionChain::class, $property21->getVisibleCondition());
        self::assertSame($prop21chain, $property21->getVisibleCondition()->getConditions()[0]);
        self::assertInstanceOf(BooleanCondition::class, $property21->getVisibleCondition()->getConditions()[1]);
        self::assertFalse($property21->getVisibleCondition()->getConditions()[1]->getValue());

        $chain = $property21->getVisibleCondition();
        self::assertInstanceOf(PropertyConditionChain::class, $chain);
        self::assertSame($prop21chain, $chain->getConditions()[0]);
        self::assertInstanceOf(BooleanCondition::class, $chain->getConditions()[1]);
        self::assertFalse($chain->getConditions()[1]->getValue());

        self::assertInstanceOf(PropertyConditionChain::class, $property22->getVisibleCondition());
        self::assertSame($prop22chain, $property22->getVisibleCondition());
        self::assertInstanceOf(BooleanCondition::class, $property22->getVisibleCondition()->getConditions()[0]);
        self::assertFalse($property22->getVisibleCondition()->getConditions()[0]->getValue());


        self::assertNull($propertyNotExist->getVisibleCondition());
    }

    private function mockProperty($isExcluded = false): MockObject&PropertyInterface
    {
        $mock = $this
            ->getMockBuilder(PropertyInterface::class)
            ->getMock();
        $mock->expects($this->once())->method('isExcluded')->willReturn($isExcluded);

        return $mock;
    }
}
