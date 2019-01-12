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
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2013-2019 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Test\DataDefinition\Palette;

use ContaoCommunityAlliance\DcGeneral\Data\DefaultModel;
use ContaoCommunityAlliance\DcGeneral\Data\PropertyValueBag;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Condition\Palette\PaletteConditionChain;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Condition\Palette\PropertyValueCondition;
use ContaoCommunityAlliance\DcGeneral\Test\DataDefinition\AbstractConditionChainTestBase;

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
