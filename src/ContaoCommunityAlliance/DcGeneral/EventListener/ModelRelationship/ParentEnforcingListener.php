<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2016 Contao Community Alliance.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    contao-community-alliance/dc-general
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @copyright  2013-2016 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\EventListener\ModelRelationship;

use ContaoCommunityAlliance\DcGeneral\Controller\ModelCollector;
use ContaoCommunityAlliance\DcGeneral\Controller\RelationshipManager;
use ContaoCommunityAlliance\DcGeneral\Data\ModelId;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\BasicDefinitionInterface;
use ContaoCommunityAlliance\DcGeneral\Event\EnforceModelRelationshipEvent;

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
        $definition  = $environment->getDataDefinition();
        $mode        = $definition->getBasicDefinition()->getMode();
        $input       = $environment->getInputProvider();

        if (BasicDefinitionInterface::MODE_PARENTEDLIST !== $mode || !$input->hasParameter('pid')) {
            return;
        }

        $model         = $event->getModel();
        $collector     = new ModelCollector($environment);
        $relationships = new RelationshipManager($definition->getModelRelationshipDefinition(), $mode);
        $parent        = $collector->getModel(ModelId::fromSerialized($input->getParameter('pid')));

        $relationships->setParent($model, $parent);
    }
}
