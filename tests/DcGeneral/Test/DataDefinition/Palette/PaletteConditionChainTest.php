<?php

/**
 * PHP version 5
 * @package    generalDriver
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Tristan Lins <tristan.lins@bit3.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

namespace DcGeneral\Test\DataDefinition\Palette;

use DcGeneral\Data\DefaultModel;
use DcGeneral\Data\PropertyValueBag;
use DcGeneral\DataDefinition\Palette\Condition\Palette\PaletteConditionChain;
use DcGeneral\DataDefinition\Palette\Condition\Palette\PropertyValueCondition;
use DcGeneral\Test\DataDefinition\AbstractConditionChainTestBase;

class PaletteConditionChainTest extends AbstractConditionChainTestBase
{
	public function testClone()
	{
		$condition = new PaletteConditionChain();

		$condition->addCondition(new PropertyValueCondition('propname', '0'));
		$condition->addCondition(new PropertyValueCondition('propname2', '1'));

		$this->assertCloneMatch($condition);
	}

	public function testGetMatch()
	{
		$condition = new PaletteConditionChain();
		$condition->setConjunction(PaletteConditionChain::AND_CONJUNCTION);

		$condition->addCondition(new PropertyValueCondition('prop1', '0'));
		$condition->addCondition(new PropertyValueCondition('prop2', '1'));

		$this->assertEquals(0, $condition->getMatchCount());

		$model = new DefaultModel();
		$model->setProperty('prop1', '0');
		$model->setProperty('prop2', '1');

		$this->assertEquals(2, $condition->getMatchCount($model));

		$model->setProperty('prop2', '0');
		$this->assertEquals(0, $condition->getMatchCount($model));

		$propertyValueBag = new PropertyValueBag();
		$propertyValueBag->setPropertyValue('prop1', '0');
		$propertyValueBag->setPropertyValue('prop2', '1');

		$this->assertEquals(2, $condition->getMatchCount(null, $propertyValueBag));

		$propertyValueBag->setPropertyValue('prop2', '3');
		$this->assertEquals(0, $condition->getMatchCount(null, $propertyValueBag));
	}
}
