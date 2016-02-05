<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2015 Contao Community Alliance.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    contao-community-alliance/dc-general
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @copyright  2013-2015 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\EventListener\ModelRelationship;

use ContaoCommunityAlliance\DcGeneral\Data\ModelId;
use ContaoCommunityAlliance\DcGeneral\Data\ModelInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\BasicDefinitionInterface;
use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;
use ContaoCommunityAlliance\DcGeneral\Event\EnforceModelRelationshipEvent;
use ContaoCommunityAlliance\DcGeneral\InputProviderInterface;

/**
 * This class takes care of enforcing a parent child relationship on a model.
 */
class ParentEnforcingListener
{
    /**
     * Handle the enforcement request.
     *
     * @param EnforceModelRelationshipEvent $event The event to process.
     *
     * @return void
     */
    public function process(EnforceModelRelationshipEvent $event)
    {
        $environment = $event->getEnvironment();
        $mode        = $environment->getDataDefinition()->getBasicDefinition()->getMode();

        if (BasicDefinitionInterface::MODE_PARENTEDLIST !== $mode) {
            return;
        }
        $input = $environment->getInputProvider();

        if (!$parent = $this->loadParentModel($input, $environment)) {
            return;
        }

        $model      = $event->getModel();
        $definition = $environment->getDataDefinition();
        $basic      = $definition->getBasicDefinition();

        $condition = $definition
            ->getModelRelationshipDefinition()
            ->getChildCondition(
                $basic->getParentDataProvider(),
                $basic->getDataProvider()
            );

        if ($condition) {
            $condition->applyTo($parent, $model);
        }
    }

    /**
     * Load the parent model for the current list.
     *
     * @param InputProviderInterface $input       The input provider.
     *
     * @param EnvironmentInterface   $environment The environment.
     *
     * @return ModelInterface|null
     */
    protected function loadParentModel(InputProviderInterface $input, EnvironmentInterface $environment)
    {
        if (!$input->hasParameter('pid')) {
            return null;
        }

        $pid = ModelId::fromSerialized($input->getParameter('pid'));

        if (!($dataProvider = $environment->getDataProvider($pid->getDataProviderName()))) {
            return null;
        }

        if ($parent = $dataProvider->fetch($dataProvider->getEmptyConfig()->setId($pid->getId()))) {
            return $parent;
        }

        return null;
    }
}
