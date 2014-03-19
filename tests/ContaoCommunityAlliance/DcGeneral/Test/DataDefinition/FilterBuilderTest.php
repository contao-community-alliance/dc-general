<?php

/**
 * PHP version 5
 * @package    DcGeneral
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Tristan Lins <tristan.lins@bit3.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Test\DataDefinition;

use ContaoCommunityAlliance\DcGeneral\DataDefinition\ModelRelationship\FilterBuilder;
use ContaoCommunityAlliance\DcGeneral\Test\TestCase;

class FilterBuilderTest extends TestCase
{
	public function testEmpty()
	{
		$builder = new FilterBuilder();

		$this->assertEquals(array(), $builder->getAllAsArray());
	}

	public function testNoOp()
	{
		$filter = array(array('operation' => '=', 'property' => 'prop', 'value' => '1'));

		$builder = new FilterBuilder($filter, true);

		$this->assertEquals($filter, $builder->getAllAsArray());
	}

	public function testAddAnd()
	{
		$filter = array(array('operation' => '=', 'property' => 'prop', 'value' => '1'));
		$result = array_merge(
			$filter,
			array(array('operation' => '=', 'property' => 'prop2', 'value' => '2'))
		);

		$builder = new FilterBuilder($filter, true);

		$builder->getFilter()->andPropertyEquals('prop2', '2');

		$this->assertEquals($result, $builder->getAllAsArray());
	}
}
