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

namespace ContaoCommunityAlliance\DcGeneral\Test\DataDefinition\Palette;

use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Condition\Palette\DefaultPaletteCondition;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Legend;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Palette;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Property;
use ContaoCommunityAlliance\DcGeneral\Test\TestCase;

class PaletteTest extends TestCase
{
	public function testClone()
	{
		$palette = new Palette();

		$condition = new DefaultPaletteCondition();
		$palette->setCondition($condition);

		$legend = new Legend('legend');
		$palette->addLegend($legend);
		$legend->addProperty(new Property('prop1'));
		$legend->addProperty(new Property('prop2'));

		$palette2 = clone $palette;

		$this->assertNotSame($palette, $palette2);

		$condition2 = $palette2->getCondition();
		$this->assertInstanceOf('DcGeneral\DataDefinition\Palette\Condition\Palette\DefaultPaletteCondition', $condition2);
		$this->assertNotSame($condition, $condition2);

		$legend2 = $palette2->getLegend('legend');

		$this->assertNotNull($legend2);
		$this->assertNotSame($legend, $legend2);
		$this->assertSame($legend->getName(), $legend2->getName());

		$properties1 = $legend->getProperties();
		$properties2 = $legend2->getProperties();
		$this->assertNotSame($properties1[0], $properties2[0]);
		$this->assertSame($properties1[0]->getName(), $properties2[0]->getName());
		$this->assertNotSame($properties1[1], $properties2[1]);
		$this->assertSame($properties1[1]->getName(), $properties2[1]->getName());
	}
}
