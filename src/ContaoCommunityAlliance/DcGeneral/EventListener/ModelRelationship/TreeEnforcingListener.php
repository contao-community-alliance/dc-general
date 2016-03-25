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

use ContaoCommunityAlliance\DcGeneral\Controller\ControllerInterface;
use ContaoCommunityAlliance\DcGeneral\Data\ModelId;
use ContaoCommunityAlliance\DcGeneral\Data\ModelInterface;
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
        $mode        = $environment->getDataDefinition()->getBasicDefinition()->getMode();

        if (BasicDefinitionInterface::MODE_HIERARCHICAL !== $mode) {
            return;
        }

        $input      = $environment->getInputProvider();
        $controller = $environment->getController();
        $model      = $event->getModel();

        if ($input->hasParameter('into')) {
            $this->handleInto($input, $controller, $model);
        } elseif ($input->hasParameter('after')) {
            $this->handleAfter($input, $controller, $model);
        }

        // Also enforce the parent condition of the parent provider (if any).
        if ($input->hasParameter('pid')) {
            $parent = $controller->fetchModelFromProvider($input->getParameter('pid'));
            $controller->setParent($model, $parent);
        }
    }

    /**
     * Handle paste into.
     *
     * @param InputProviderInterface $input      The input provider.
     *
     * @param ControllerInterface    $controller The controller.
     *
     * @param ModelInterface         $model      The model.
     *
     * @return void
     */
    private function handleInto(InputProviderInterface $input, ControllerInterface $controller, ModelInterface $model)
    {
        $into = ModelId::fromSerialized($input->getParameter('pid'));

        // If we have a null, it means insert into the tree root.
        if ($into->getId() == 0) {
            $controller->setRootModel($model);
        } else {
            $parent = $controller->fetchModelFromProvider($into);
            $controller->setParent($model, $parent);
        }
    }

    /**
     * Handle paste after.
     *
     * @param InputProviderInterface $input      The input provider.
     *
     * @param ControllerInterface    $controller The controller.
     *
     * @param ModelInterface         $model      The model.
     *
     * @return void
     */
    private function handleAfter(InputProviderInterface $input, ControllerInterface $controller, ModelInterface $model)
    {
        $after   = ModelId::fromSerialized($input->getParameter('after'));
        $sibling = $controller->fetchModelFromProvider($after);

        if (!$sibling || $controller->isRootModel($sibling)) {
            $controller->setRootModel($model);
        } else {
            $parent = $controller->searchParentOf($sibling);
            $controller->setParent($model, $parent);
        }
    }
}
