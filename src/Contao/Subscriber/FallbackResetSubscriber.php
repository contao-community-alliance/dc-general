<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2019 Contao Community Alliance.
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
 * @copyright  2013-2019 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Contao\Subscriber;

use ContaoCommunityAlliance\DcGeneral\Data\ConfigInterface;
use ContaoCommunityAlliance\DcGeneral\Data\ModelManipulator;
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
        return [
            PostPersistModelEvent::NAME => ['handlePostPersistModelEvent', -200],
            PostDuplicateModelEvent::NAME => ['handlePostDuplicateModelEvent', -200]
        ];
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

        foreach (\array_keys($model->getPropertiesAsArray()) as $propertyName) {
            if (!$properties->hasProperty($propertyName)) {
                continue;
            }
            $property = $properties->getProperty($propertyName);
            $extra    = (array) $property->getExtra();
            if (\array_key_exists('fallback', $extra) && (true === $extra['fallback'])) {
                // BC Layer - use old reset fallback methodology until it get's removed.
                if (null === ($config = $this->determineFilterConfig($event))) {
                    // @codingStandardsIgnoreStart
                    @\trigger_error(
                        'DataProviderInterface::resetFallback is deprecated - ' .
                        'Please specify proper parent child relationship',
                        E_USER_DEPRECATED
                    );
                    // @codingStandardsIgnoreEnd

                    $dataProvider->resetFallback($propertyName);
                }

                // If value is empty, no need to reset the fallback.
                if (!$model->getProperty($propertyName)) {
                    continue;
                }

                $models = $dataProvider->fetchAll($config);

                foreach ($models as $resetModel) {
                    if ($model->getId() === $resetModel->getId()) {
                        continue;
                    }
                    $resetModel->setProperty($propertyName, ModelManipulator::sanitizeValue($property, null));
                    $dataProvider->save($resetModel);
                }
            }
        }
    }

    /**
     * Determine the filter config to use.
     *
     * @param AbstractModelAwareEvent $event The event.
     *
     * @return ConfigInterface|null
     */
    private function determineFilterConfig(AbstractModelAwareEvent $event)
    {
        $environment  = $event->getEnvironment();
        $model        = $event->getModel();
        $dataProvider = $environment->getDataProvider($model->getProviderName());
        $definition   = $environment->getDataDefinition();
        $relationship = $definition->getModelRelationshipDefinition();

        $root = $relationship->getRootCondition();
        if (null !== $root && $root->matches($model)) {
            return $dataProvider->getEmptyConfig()->setFilter($root->getFilterArray());
        }

        $parentFilter = $relationship->getChildCondition(
            $definition->getBasicDefinition()->getParentDataProvider(),
            $model->getProviderName()
        );

        if (null !== $parentFilter) {
            $parentConfig   = $dataProvider->getEmptyConfig()->setFilter($parentFilter->getInverseFilterFor($model));
            $parentProvider = $environment->getDataProvider($parentFilter->getSourceName());
            $parent         = $parentProvider->fetchAll($parentConfig)->get(0);
            return $dataProvider->getEmptyConfig()->setFilter($parentFilter->getFilter($parent));
        }

        // Trigger BC layer in handleFallback().
        if ($root === null && \count($relationship->getChildConditions()) == 0) {
            return null;
        }

        return $dataProvider->getEmptyConfig();
    }
}
