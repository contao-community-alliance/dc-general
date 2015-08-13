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

namespace ContaoCommunityAlliance\DcGeneral\Test\DataDefinition\ModelRelationship;

use ContaoCommunityAlliance\DcGeneral\Data\DefaultModel;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\ModelRelationship\ParentChildCondition;
use ContaoCommunityAlliance\DcGeneral\Test\TestCase;

class ParentChildConditionTest extends TestCase
{
	public function testMatches()
	{
		$parent = new DefaultModel();
		$parent->setId(1);

		$child = new DefaultModel();
		$child->setPropertyRaw('pid', 1);

		$condition = new ParentChildCondition();
		$condition->setFilterArray(array(array(
			'local'     => 'id',
			'operation' => '=',
			'remote'    => 'pid'
		)));

		$this->assertTrue($condition->matches($parent, $child));
	}

	public function testMatchesRemoteValue()
	{
		$parent = new DefaultModel();
		$parent->setId(1);

		$child = new DefaultModel();
		$child->setPropertyRaw('pid', 1);
		$child->setId(2);

		$condition = new ParentChildCondition();
		$condition->setFilterArray(array(array(
			'local'        => 'id',
			'operation'    => '=',
			'remote_value' => '2'
		)));

		$this->assertTrue($condition->matches($parent, $child));
	}
}
