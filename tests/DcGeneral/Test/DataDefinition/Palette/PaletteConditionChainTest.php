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
}
