<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2018 Contao Community Alliance.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    contao-community-alliance/dc-general
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2013-2018 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Test\DataDefinition\ModelRelationship;

use ContaoCommunityAlliance\DcGeneral\Data\DefaultModel;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\ModelRelationship\RootCondition;
use ContaoCommunityAlliance\DcGeneral\Test\TestCase;

/**
 * This class tests the RootCondition.
 */
class RootConditionTest extends TestCase
{
    /**
     * Test that the matches method does not match for models from another provider.
     *
     * @return void
     */
    public function testMatchesForChildFromOtherProvider()
    {
        $model = new DefaultModel();
        $model->setID(1);
        $model->setProviderName('test2-provider');
        $model->setProperty('pid', 0);

        $condition = new RootCondition();
        $condition
            ->setFilterArray([
                    [
                        'value'     => '0',
                        'operation' => '=',
                        'property'  => 'pid'
                    ]
                ]
            )
            ->setSourceName('test-provider');

        $this->assertFalse($condition->matches($model));
    }

    /**
     * Test the matches method().
     *
     * @return void
     */
    public function testMatches()
    {
        $model = new DefaultModel();
        $model->setId(1);
        $model->setProviderName('test-provider');
        $model->setProperty('pid', 0);

        $condition = new RootCondition();
        $condition
            ->setFilterArray([
                    [
                        'value'     => '0',
                        'operation' => '=',
                        'property'  => 'pid'
                    ]
                ]
            )
            ->setSourceName('test-provider');

        $this->assertTrue($condition->matches($model));
    }
}
