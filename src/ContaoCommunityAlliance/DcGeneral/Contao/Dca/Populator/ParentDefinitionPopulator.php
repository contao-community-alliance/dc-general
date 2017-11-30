<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2017 Contao Community Alliance.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    contao-community-alliance/dc-general
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2013-2017 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Contao\Dca\Populator;

use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;
use ContaoCommunityAlliance\DcGeneral\EnvironmentPopulator\AbstractEventDrivenEnvironmentPopulator;
use ContaoCommunityAlliance\DcGeneral\Factory\DcGeneralFactory;

/**
 * Class ParentDefinitionPopulator.
 *
 * This class reacts to the PopulateEnvironmentEvent and populate the parent data definition.
 */
class ParentDefinitionPopulator extends AbstractEventDrivenEnvironmentPopulator
{
    const PRIORITY = 0;

    /**
     * Create a parent data definition, if parent data provider defined.
     *
     * @param EnvironmentInterface $environment The environment to populate.
     *
     * @return void
     *
     * @internal
     */
    public function populateController(EnvironmentInterface $environment)
    {
        $definition = $environment->getDataDefinition();

        if (!$definition->getBasicDefinition()->getParentDataProvider()) {
            return;
        }

        $factory          = new DcGeneralFactory();
        $parentDefinition = $factory
            ->setEventDispatcher($environment->getEventDispatcher())
            ->setTranslator($environment->getTranslator())
            ->setContainerName($definition->getBasicDefinition()->getParentDataProvider())
            ->createDcGeneral()
            ->getEnvironment()
            ->getDataDefinition();

        $environment->setParentDataDefinition($parentDefinition);
    }

    /**
     * {@inheritDoc}
     */
    public function populate(EnvironmentInterface $environment)
    {
        $this->populateController($environment);
    }
}
