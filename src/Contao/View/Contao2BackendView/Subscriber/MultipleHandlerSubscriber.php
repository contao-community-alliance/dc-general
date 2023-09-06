<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2021 Contao Community Alliance.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    contao-community-alliance/dc-general
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2013-2021 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Subscriber;

use ContaoCommunityAlliance\DcGeneral\Contao\DataDefinition\Definition\Contao2BackendViewDefinitionInterface;
use ContaoCommunityAlliance\DcGeneral\Contao\RequestScopeDeterminator;
use ContaoCommunityAlliance\DcGeneral\Contao\RequestScopeDeterminatorAwareTrait;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\BuildWidgetEvent;
use ContaoCommunityAlliance\DcGeneral\Data\ModelId;
use ContaoCommunityAlliance\DcGeneral\Data\ModelIdInterface;
use ContaoCommunityAlliance\DcGeneral\DcGeneralEvents;
use ContaoCommunityAlliance\DcGeneral\Event\ActionEvent;
use MenAtWork\MultiColumnWizardBundle\Event\GetOptionsEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * The multiple handler subscriber provides functions for edit/override all.
 */
class MultipleHandlerSubscriber implements EventSubscriberInterface
{
    use RequestScopeDeterminatorAwareTrait;

    /**
     * MultipleHandlerSubscriber constructor.
     *
     * @param RequestScopeDeterminator $scopeDeterminator The request scope determinator.
     */
    public function __construct(RequestScopeDeterminator $scopeDeterminator)
    {
        $this->scopeDeterminator = $scopeDeterminator;
    }

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
     *  * array('eventName' => array(array('methodName1', $priority), array('methodName2')))
     *
     * @return array The event names to listen to
     */
    public static function getSubscribedEvents()
    {
        $listeners = [
            DcGeneralEvents::ACTION => [
                ['prepareGlobalAllButton', 9999],
                ['deactivateGlobalButton', 9999]
            ],
            BuildWidgetEvent::NAME  => ['handleOriginalWidget', 9999]
        ];

        if (\class_exists(GetOptionsEvent::class)) {
            $listeners[GetOptionsEvent::NAME] = ['handleOriginalOptions', 9999];
        }

        return $listeners;
    }

    /**
     * Prepare the global all button.
     *
     * @param ActionEvent $event The event.
     *
     * @return void
     */
    public function prepareGlobalAllButton(ActionEvent $event)
    {
        if (!$this->getScopeDeterminator()->currentScopeIsBackend() || ('showAll' !== $event->getAction()->getName())) {
            return;
        }

        $dataDefinition = $event->getEnvironment()->getDataDefinition();
        $backendView    = $dataDefinition->getDefinition(Contao2BackendViewDefinitionInterface::NAME);
        $globalCommands = $backendView->getGlobalCommands();

        if ($globalCommands->hasCommandNamed('all')) {
            $allCommand = $globalCommands->getCommandNamed('all');

            $parameters = $allCommand->getParameters();
            $parameters->offsetSet('select', 'models');
        }
    }

    /**
     * Deactivate global button their are not useful.
     *
     * @param ActionEvent $event The event.
     *
     * @return void
     */
    public function deactivateGlobalButton(ActionEvent $event)
    {
        $allowedAction = ['selectModelAll', 'selectPropertyAll', 'editAll', 'overrideAll'];
        if (
            !$this->getScopeDeterminator()->currentScopeIsBackend()
            || !\in_array($event->getAction()->getName(), $allowedAction)
        ) {
            return;
        }

        $dataDefinition = $event->getEnvironment()->getDataDefinition();
        $backendView    = $dataDefinition->getDefinition(Contao2BackendViewDefinitionInterface::NAME);
        $globalCommands = $backendView->getGlobalCommands();

        $allowedButton = ['close_all_button'];
        if ('selectModelAll' !== $event->getAction()->getName()) {
            $allowedButton[] = 'back_button';
        }

        foreach ($globalCommands->getCommands() as $command) {
            if (\in_array($command->getName(), $allowedButton)) {
                continue;
            }

            $command->setDisabled(true);
        }
    }

    /**
     * Handle the original widget options.
     *
     * @param GetOptionsEvent $event The event.
     *
     * @return void
     */
    public function handleOriginalOptions(GetOptionsEvent $event)
    {
        $environment = $event->getEnvironment();

        if (
            !$this->getScopeDeterminator()->currentScopeIsBackend()
            || ('select' !== $environment->getInputProvider()->getParameter('act'))
            || ('edit' !== $environment->getInputProvider()->getParameter('select'))
        ) {
            return;
        }

        $model = $event->getModel();

        if (
            !($propertyName = $this->getOriginalPropertyName($event->getPropertyName(), ModelId::fromModel($model)))
            || !$model->getProperty($propertyName)
        ) {
            return;
        }

        $originalWidget       = clone $event->getWidget();
        $originalWidget->id   = $propertyName;
        $originalWidget->name = $propertyName;

        $originalOptionsEvent =
            new GetOptionsEvent(
                $propertyName,
                $event->getSubPropertyName(),
                $event->getEnvironment(),
                $model,
                $originalWidget,
                $event->getOptions()
            );

        $environment->getEventDispatcher()->dispatch($originalOptionsEvent, GetOptionsEvent::NAME);

        $event->setOptions($originalOptionsEvent->getOptions());

        $event->stopPropagation();
    }

    /**
     * Handle the original widget.
     *
     * @param BuildWidgetEvent $event The build widget event.
     *
     * @return void
     */
    public function handleOriginalWidget(BuildWidgetEvent $event)
    {
        $environment = $event->getEnvironment();

        if (
            !$this->getScopeDeterminator()->currentScopeIsBackend()
            || ('select' !== $environment->getInputProvider()->getParameter('act'))
            || ('edit' !== $environment->getInputProvider()->getParameter('select'))
        ) {
            return;
        }

        $properties = $environment->getDataDefinition()->getPropertiesDefinition();

        $this->findModelIdByPropertyName($event);

        $model   = $event->getModel();
        $modelId = ModelId::fromModel($model);

        $originalPropertyName = $this->getOriginalPropertyName($event->getProperty()->getName(), $modelId);
        if (
            (null === $originalPropertyName)
            || ((null !== $originalPropertyName) && (false === $properties->hasProperty($originalPropertyName)))
        ) {
            return;
        }

        $originalProperty = $properties->getProperty($originalPropertyName);

        $originalExtra = $copiedExtra = $originalProperty->getExtra();

        if (!empty($originalExtra['orderField'])) {
            $orderId = \str_replace('::', '____', $modelId->getSerialized()) . '_' . $copiedExtra['orderField'];

            $copiedExtra['orderField'] = $orderId;

            $isChanged = $model->getMeta($model::IS_CHANGED);
            $model->setProperty($orderId, $model->getProperty($originalExtra['orderField']));
            $model->setMeta($model::IS_CHANGED, $isChanged);
        }

        $originalProperty->setExtra($copiedExtra);

        $originalEvent =
            new BuildWidgetEvent($environment, $model, $originalProperty);

        $environment->getEventDispatcher()->dispatch($originalEvent, BuildWidgetEvent::NAME);

        $originalEvent->getWidget()->id   = $event->getProperty()->getName();
        $originalEvent->getWidget()->name =
            \str_replace('::', '____', $modelId->getSerialized()) . '_[' . $originalPropertyName . ']';

        $originalEvent->getWidget()->tl_class = '';

        $event->setWidget($originalEvent->getWidget());

        $originalProperty->setExtra($originalExtra);

        $event->stopPropagation();
    }

    /**
     * Find the model id by property name, if model id not set.
     *
     * @param BuildWidgetEvent $event The event.
     *
     * @return void
     */
    private function findModelIdByPropertyName(BuildWidgetEvent $event)
    {
        if (null !== $event->getModel()->getId()) {
            return;
        }

        $environment    = $event->getEnvironment();
        $dataDefinition = $environment->getDataDefinition();
        $inputProvider  = $environment->getInputProvider();
        $sessionStorage = $environment->getSessionStorage();

        $session = $sessionStorage->get($dataDefinition->getName() . '.' . $inputProvider->getParameter('mode'));

        $model = null;
        foreach ($session['models'] as $sessionModel) {
            $model = $sessionModel;

            if (0 !== \strpos($event->getProperty()->getName(), \str_replace('::', '____', $sessionModel))) {
                continue;
            }

            break;
        }

        $modelId = ModelId::fromSerialized($model);

        $event->getModel()->setId($modelId->getId());
    }

    /**
     * Get the original property name.
     *
     * @param string           $propertyName The property name.
     * @param ModelIdInterface $modelId      The model id.
     *
     * @return string|null
     */
    private function getOriginalPropertyName($propertyName, ModelIdInterface $modelId)
    {
        $originalPropertyName =
            \trim(\substr($propertyName, \strlen(\str_replace('::', '____', $modelId->getSerialized()) . '_')), '[]');

        return $originalPropertyName ?: null;
    }
}
