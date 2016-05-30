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
 * @copyright  2013-2016 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Contao\Subscriber;

use ContaoCommunityAlliance\DcGeneral\Event\AbstractModelAwareEvent;
use ContaoCommunityAlliance\DcGeneral\Event\PostDuplicateModelEvent;
use ContaoCommunityAlliance\DcGeneral\Event\PostPersistModelEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * This class handles events to reset the fallback of any property having the fallback extra value.
 */
class FallbackResetSubscriber implements EventSubscriberInterface
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
            PostPersistModelEvent::NAME => array('handlePostPersistModelEvent', -200),
            PostDuplicateModelEvent::NAME => array('handlePostPersistModelEvent', -200)
        );
    }

    /**
     * Handle the post persist model event.
     *
     * @param PostPersistModelEvent $event The event to handle.
     *
     * @return void
     */
    public function handlePostPersistModelEvent(PostPersistModelEvent $event)
    {
        $this->handleFallback($event);
    }

    /**
     * Handle the post duplicate model event.
     *
     * @param PostDuplicateModelEvent $event The event to handle.
     *
     * @return void
     */
    public function handlePostDuplicateModelEvent(PostDuplicateModelEvent $event)
    {
        $this->handleFallback($event);
    }

    /**
     * Process the event.
     *
     * @param AbstractModelAwareEvent $event The event.
     *
     * @return void
     */
    private function handleFallback(AbstractModelAwareEvent $event)
    {
        $model        = $event->getModel();
        $dataProvider = $event->getEnvironment()->getDataProvider($model->getProviderName());
        $properties   = $event->getEnvironment()->getDataDefinition()->getPropertiesDefinition();

        foreach (array_keys($model->getPropertiesAsArray()) as $propertyName) {
            if (!$properties->hasProperty($propertyName)) {
                continue;
            }

            $extra = (array) $properties->getProperty($propertyName)->getExtra();
            if (array_key_exists('fallback', $extra) && (true === $extra['fallback'])) {
                if (!$dataProvider->isUniqueValue($propertyName, $model->getProperty($propertyName), $model->getId())) {
                    // Reset fallback and save model again to have the correct value.
                    $dataProvider->resetFallback($propertyName);
                    $dataProvider->save($model);
                }
            }
        }
    }
}
