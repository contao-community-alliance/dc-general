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

namespace ContaoCommunityAlliance\DcGeneral\Contao\Subscriber;

use ContaoCommunityAlliance\DcGeneral\Contao\DataDefinition\Definition\Contao2BackendViewDefinitionInterface;
use ContaoCommunityAlliance\DcGeneral\Event\PreEditModelEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * This class handles events to handle if the data container is an dynamic table.
 */
class DynamicParentTableSubscriber implements EventSubscriberInterface
{
    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     * The array keys are event names and the value can be:
     *
     *  * The method name to call (priority defaults to 0)
     *  * An array composed of the method name to call and the priority
     *  * An array of arrays composed of the method names to call and respective
     *    priorities, or 0 if unset
     *
     * For instance:
     *
     *  * array('eventName' => 'methodName')
     *  * array('eventName' => array('methodName', $priority))
     *  * array('eventName' => array(array('methodName1', $priority), array('methodName2'))
     *
     * @return array The event names to listen to.
     */
    public static function getSubscribedEvents()
    {
        return array(
            PreEditModelEvent::NAME => array('handlePrePersistModelEvent', -200),
        );
    }

    /**
     * Handle the pre persist model event.
     *
     * @param PreEditModelEvent $event The event to handle.
     *
     * @return void
     */
    public function handlePrePersistModelEvent(PreEditModelEvent $event)
    {
        $dataDefinition       = $event->getEnvironment()->getDataDefinition();
        $parentDataDefinition = $event->getEnvironment()->getParentDataDefinition();
        $basicDefinition      = $dataDefinition->getBasicDefinition();
        $backendView          = $dataDefinition->getDefinition(Contao2BackendViewDefinitionInterface::NAME);
        $propertiesDefinition = $dataDefinition->getPropertiesDefinition();

        if ((null === $parentDataDefinition)
            || (false === $basicDefinition->isDynamicParentTable())
            || (null === ($parentTablePropertyName = $backendView->getListingConfig()->getParentTablePropertyName()))
            || (false === $propertiesDefinition->hasProperty($parentTablePropertyName))
        ) {
            return;
        }

        $model = $event->getModel();
        $model->setProperty($parentTablePropertyName, $parentDataDefinition->getName());
    }
}
