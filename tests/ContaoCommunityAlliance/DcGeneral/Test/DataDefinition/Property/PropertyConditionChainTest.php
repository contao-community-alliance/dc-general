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

namespace ContaoCommunityAlliance\DcGeneral\Test\DataDefinition\Property;

use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Condition\Property\PropertyConditionChain;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Condition\Property\PropertyValueCondition;
use ContaoCommunityAlliance\DcGeneral\Test\DataDefinition\AbstractConditionChainTestBase;

class PropertyConditionChainTest extends AbstractConditionChainTestBase
{
	public function testClone()
	{
		$condition = new PropertyConditionChain();

		$condition->addCondition(new PropertyValueCondition('propname', '0'));
		$condition->addCondition(new PropertyValueCondition('propname2', '1'));

		$this->assertCloneMatch($condition);
	}
}
