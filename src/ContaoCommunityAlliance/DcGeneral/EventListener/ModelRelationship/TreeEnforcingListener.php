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

use ContaoCommunityAlliance\DcGeneral\Controller\ModelCollector;
use ContaoCommunityAlliance\DcGeneral\Controller\RelationshipManager;
use ContaoCommunityAlliance\DcGeneral\Data\ModelId;
use ContaoCommunityAlliance\DcGeneral\Data\ModelInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\BasicDefinitionInterface;
use ContaoCommunityAlliance\DcGeneral\Event\EnforceModelRelationshipEvent;

/**
 * This class takes care of enforcing a tree relationship on a model.
 */
class TreeEnforcingListener
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

        if (BasicDefinitionInterface::MODE_HIERARCHICAL !== $mode) {
            return;
        }

        $input         = $environment->getInputProvider();
        $model         = $event->getModel();
        $collector     = new ModelCollector($environment);
        $relationships = new RelationshipManager(
            $environment->getDataDefinition()->getModelRelationshipDefinition(),
            $mode
        );

        if ($input->hasParameter('into')) {
            $this->handleInto(
                ModelId::fromSerialized($input->getParameter('into')),
                $relationships,
                $collector,
                $model
            );
        } elseif ($input->hasParameter('after')) {
            $this->handleAfter(
                ModelId::fromSerialized($input->getParameter('after')),
                $relationships,
                $collector,
                $model
            );
        }

        // Also enforce the parent condition of the parent provider (if any).
        if ($input->hasParameter('pid')) {
            $parent = $collector->getModel($input->getParameter('pid'));
            $relationships->setParent($model, $parent);
        }
    }

    /**
     * Handle paste into.
     *
     * @param ModelId             $into          The id of the new parenting model.
     *
     * @param RelationshipManager $relationships The relationship manager.
     *
     * @param ModelCollector      $collector     The model collector.
     *
     * @param ModelInterface      $model         The model.
     *
     * @return void
     */
    private function handleInto(
        ModelId $into,
        RelationshipManager $relationships,
        ModelCollector $collector,
        ModelInterface $model
    ) {
        // If we have a null, it means insert into the tree root.
        if (0 == $into->getId()) {
            $relationships->setRoot($model);
            return;
        }

        $parent = $collector->getModel($into);
        $relationships->setParent($model, $parent);
    }

    /**
     * Handle paste after.
     *
     * @param ModelId             $after         The id of the sibling model.
     *
     * @param RelationshipManager $relationships The relationship manager.
     *
     * @param ModelCollector      $collector     The model collector.
     *
     * @param ModelInterface      $model         The model.
     *
     * @return void
     */
    private function handleAfter(
        ModelId $after,
        RelationshipManager $relationships,
        ModelCollector $collector,
        ModelInterface $model
    ) {
        $sibling = $collector->getModel($after);

        if (!$sibling || $relationships->isRoot($sibling)) {
            $relationships->setRoot($model);
            return;
        }

        $parent = $collector->searchParentOf($sibling);
        $relationships->setParent($model, $parent);
    }
}
