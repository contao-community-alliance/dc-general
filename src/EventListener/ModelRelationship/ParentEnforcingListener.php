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
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2013-2023 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\EventListener\ModelRelationship;

use ContaoCommunityAlliance\DcGeneral\Controller\ModelCollector;
use ContaoCommunityAlliance\DcGeneral\Controller\RelationshipManager;
use ContaoCommunityAlliance\DcGeneral\Data\ModelId;
use ContaoCommunityAlliance\DcGeneral\Data\ModelInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\ContainerInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\BasicDefinitionInterface;
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

        $definition  = $environment->getDataDefinition();
        assert($definition instanceof ContainerInterface);

        $mode = $definition->getBasicDefinition()->getMode();

        $input = $environment->getInputProvider();
        assert($input instanceof InputProviderInterface);

        if (BasicDefinitionInterface::MODE_PARENTEDLIST !== $mode || !$input->hasParameter('pid')) {
            return;
        }

        $model = $event->getModel();

        $parent = (new ModelCollector($environment))->getModel(ModelId::fromSerialized($input->getParameter('pid')));
        assert($parent instanceof ModelInterface);

        (new RelationshipManager($definition->getModelRelationshipDefinition(), $mode))->setParent($model, $parent);
    }
}
