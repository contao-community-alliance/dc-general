<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2023 Contao Community Alliance.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    contao-community-alliance/dc-general
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     Richard Henkenjohann <richardhenkenjohann@googlemail.com>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2013-2023 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\EventListener\ModelRelationship;

use ContaoCommunityAlliance\DcGeneral\Controller\ModelCollector;
use ContaoCommunityAlliance\DcGeneral\Controller\RelationshipManager;
use ContaoCommunityAlliance\DcGeneral\Data\ModelId;
use ContaoCommunityAlliance\DcGeneral\Data\ModelIdInterface;
use ContaoCommunityAlliance\DcGeneral\Data\ModelInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\ContainerInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\BasicDefinitionInterface;
use ContaoCommunityAlliance\DcGeneral\Event\EnforceModelRelationshipEvent;
use ContaoCommunityAlliance\DcGeneral\InputProviderInterface;

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

        $definition = $environment->getDataDefinition();
        assert($definition instanceof ContainerInterface);

        $mode = $definition->getBasicDefinition()->getMode();

        if (BasicDefinitionInterface::MODE_HIERARCHICAL !== $mode) {
            return;
        }

        $input = $environment->getInputProvider();
        assert($input instanceof InputProviderInterface);

        $model         = $event->getModel();
        $collector     = new ModelCollector($environment);
        $relationships = new RelationshipManager(
            $definition->getModelRelationshipDefinition(),
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
            assert($parent instanceof ModelInterface);
            $relationships->setParent($model, $parent);
        }
    }

    /**
     * Handle paste into.
     *
     * @param ModelIdInterface    $into          The id of the new parenting model.
     * @param RelationshipManager $relationships The relationship manager.
     * @param ModelCollector      $collector     The model collector.
     * @param ModelInterface      $model         The model.
     *
     * @return void
     */
    private function handleInto(
        ModelIdInterface $into,
        RelationshipManager $relationships,
        ModelCollector $collector,
        ModelInterface $model
    ): void {
        // If we have a null, it means insert into the tree root.
        if (0 === $into->getId() || null === $parent = $collector->getModel($into)) {
            $relationships->setRoot($model);
            return;
        }

        $relationships->setParent($model, $parent);
    }

    /**
     * Handle paste after.
     *
     * @param ModelIdInterface    $after         The id of the sibling model.
     * @param RelationshipManager $relationships The relationship manager.
     * @param ModelCollector      $collector     The model collector.
     * @param ModelInterface      $model         The model.
     *
     * @return void
     */
    private function handleAfter(
        ModelIdInterface $after,
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
        assert($parent instanceof ModelInterface);

        $relationships->setParent($model, $parent);
    }
}
