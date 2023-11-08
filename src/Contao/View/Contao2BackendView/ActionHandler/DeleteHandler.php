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
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2013-2023 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\ActionHandler;

use ContaoCommunityAlliance\Contao\Bindings\ContaoEvents;
use ContaoCommunityAlliance\Contao\Bindings\Events\Controller\RedirectEvent;
use ContaoCommunityAlliance\Contao\Bindings\Events\System\LogEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\RequestScopeDeterminator;
use ContaoCommunityAlliance\DcGeneral\Contao\RequestScopeDeterminatorAwareTrait;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Exception\EditOnlyModeException;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Exception\NotDeletableException;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\ViewHelpers;
use ContaoCommunityAlliance\DcGeneral\Data\DataProviderInterface;
use ContaoCommunityAlliance\DcGeneral\Data\DefaultCollection;
use ContaoCommunityAlliance\DcGeneral\Data\ModelId;
use ContaoCommunityAlliance\DcGeneral\Data\ModelIdInterface;
use ContaoCommunityAlliance\DcGeneral\Data\ModelInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\ContainerInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\DefaultModelRelationshipDefinition;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\ModelRelationship\ParentChildConditionInterface;
use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;
use ContaoCommunityAlliance\DcGeneral\Event\ActionEvent;
use ContaoCommunityAlliance\DcGeneral\Event\PostDeleteModelEvent;
use ContaoCommunityAlliance\DcGeneral\Event\PreDeleteModelEvent;
use ContaoCommunityAlliance\DcGeneral\Exception\DcGeneralRuntimeException;
use ContaoCommunityAlliance\DcGeneral\InputProviderInterface;
use ContaoCommunityAlliance\DcGeneral\View\ActionHandler\ActionGuardTrait;
use ContaoCommunityAlliance\DcGeneral\View\ActionHandler\CallActionTrait;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Class DeleteHandler handles the delete action.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DeleteHandler
{
    use ActionGuardTrait;
    use CallActionTrait;
    use RequestScopeDeterminatorAwareTrait;

    /**
     * PasteHandler constructor.
     *
     * @param RequestScopeDeterminator $scopeDeterminator The request mode determinator.
     */
    public function __construct(RequestScopeDeterminator $scopeDeterminator)
    {
        $this->setScopeDeterminator($scopeDeterminator);
    }

    /**
     * Handle the event to process the action.
     *
     * @param ActionEvent $event The action event.
     *
     * @return void
     */
    public function handleEvent(ActionEvent $event)
    {
        if (!$this->getScopeDeterminator()->currentScopeIsBackend()) {
            return;
        }

        if ('delete' !== $event->getAction()->getName()) {
            return;
        }

        if (true !== ($response = $this->checkPermission($event->getEnvironment()))) {
            $event->setResponse((string) $response);
            $event->stopPropagation();

            return;
        }

        $response = $this->process($event->getEnvironment());
        $event->setResponse($response);
    }

    /**
     * Check if is it allowed to delete a record.
     *
     * @param EnvironmentInterface $environment The environment.
     * @param ModelIdInterface     $modelId     The model id.
     * @param bool                 $redirect    If true it redirects to error page instead of throwing an exception.
     *
     * @return void
     *
     * @throws NotDeletableException If table can´t delete.
     */
    protected function guardIsDeletable(EnvironmentInterface $environment, ModelIdInterface $modelId, $redirect = false)
    {
        $dataDefinition = $environment->getDataDefinition();
        assert($dataDefinition instanceof ContainerInterface);

        if ($dataDefinition->getBasicDefinition()->isDeletable()) {
            return;
        }

        if (false === $redirect) {
            throw new NotDeletableException($modelId->getDataProviderName());
        }

        $eventDispatcher = $environment->getEventDispatcher();
        assert($eventDispatcher instanceof EventDispatcherInterface);

        $eventDispatcher->dispatch(
            new LogEvent(
                \sprintf(
                    'Table "%s" is not deletable DC_General - DefaultController - delete()',
                    $dataDefinition->getName()
                ),
                __CLASS__ . '::delete()',
                'ERROR'
            ),
            ContaoEvents::SYSTEM_LOG
        );

        $eventDispatcher->dispatch(new RedirectEvent('contao?act=error'), ContaoEvents::CONTROLLER_REDIRECT);
    }

    /**
     * Fetch the model.
     *
     * @param EnvironmentInterface $environment The environment.
     * @param ModelIdInterface     $modelId     The model id.
     *
     * @return ModelInterface
     *
     * @throws DcGeneralRuntimeException If model is not found.
     */
    protected function fetchModel(EnvironmentInterface $environment, ModelIdInterface $modelId)
    {
        $dataProvider = $environment->getDataProvider($modelId->getDataProviderName());
        assert($dataProvider instanceof DataProviderInterface);

        $model = $dataProvider->fetch($dataProvider->getEmptyConfig()->setId($modelId->getId()));

        if (!$model || !$model->getId()) {
            throw new DcGeneralRuntimeException(
                'Could not load model with id ' . $modelId->getSerialized()
            );
        }

        return $model;
    }

    /**
     * Delete an model.
     *
     * @param EnvironmentInterface $environment Environment.
     * @param ModelIdInterface     $modelId     The model id.
     *
     * @return void
     *
     * @throws EditOnlyModeException     If the data definition is in edit only mode.
     * @throws NotDeletableException     If the data definition does not allow delete actions.
     * @throws DcGeneralRuntimeException If the model is not found.
     */
    public function delete(EnvironmentInterface $environment, ModelIdInterface $modelId)
    {
        $definition = $environment->getDataDefinition();
        assert($definition instanceof ContainerInterface);

        $this->guardNotEditOnly($definition, $modelId);
        $this->guardIsDeletable($environment, $modelId);

        $model = $this->fetchModel($environment, $modelId);

        // Trigger event before the model will be deleted.
        $preDeleteEvent = new PreDeleteModelEvent($environment, $model);
        $dispatcher     = $environment->getEventDispatcher();
        assert($dispatcher instanceof EventDispatcherInterface);

        $dispatcher->dispatch($preDeleteEvent, $preDeleteEvent::NAME);

        $dataProvider = $environment->getDataProvider($modelId->getDataProviderName());
        assert($dataProvider instanceof DataProviderInterface);
        $dataProvider->delete($model);

        // Trigger event after the model is deleted.
        $postDeleteEvent = new PostDeleteModelEvent($environment, $model);
        $dispatcher->dispatch($postDeleteEvent, $postDeleteEvent::NAME);
    }

    /**
     * Process the action.
     *
     * @param EnvironmentInterface $environment The environment.
     *
     * @return string|null
     */
    protected function process(EnvironmentInterface $environment)
    {
        $inputProvider = $environment->getInputProvider();
        assert($inputProvider instanceof InputProviderInterface);

        $dataDefinition = $environment->getDataDefinition();
        assert($dataDefinition instanceof ContainerInterface);

        $modelId = ModelId::fromSerialized($inputProvider->getParameter('id'));

        // Guard that we are in the preloaded environment. Otherwise, checking the data definition could belong to
        // another model.
        $this->guardValidEnvironment($dataDefinition, $modelId);

        // Only edit mode is supported. Trigger an edit action.
        if ($dataDefinition->getBasicDefinition()->isEditOnlyMode()) {
            return $this->callAction($environment, 'edit');
        }

        // We want a redirect here if not deletable.
        $this->guardIsDeletable($environment, $modelId, true);
        $this->deepDelete($environment, $modelId);
        $this->delete($environment, $modelId);

        if ('delete' === $inputProvider->getParameter('mode')) {
            return null;
        }

        ViewHelpers::redirectHome($environment);

        return null;
    }

    /**
     * Check permission for delete a model.
     *
     * @param EnvironmentInterface $environment The environment.
     *
     * @return string|bool
     */
    private function checkPermission(EnvironmentInterface $environment)
    {
        $definition = $environment->getDataDefinition();
        assert($definition instanceof ContainerInterface);

        if (true === $definition->getBasicDefinition()->isDeletable()) {
            return true;
        }

        $inputProvider = $environment->getInputProvider();
        assert($inputProvider instanceof InputProviderInterface);

        return \sprintf(
            '<div style="text-align:center; font-weight:bold; padding:40px;">
                You have no permission for delete model %s.
            </div>',
            ModelId::fromSerialized($inputProvider->getParameter('id'))->getSerialized()
        );
    }

    /**
     * Delete all deep models.
     *
     * @param EnvironmentInterface $environment Environment.
     * @param ModelIdInterface     $modelId     The Model Id.
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.LongVariableName)
     */
    protected function deepDelete(EnvironmentInterface $environment, ModelIdInterface $modelId)
    {
        $definition = $environment->getDataDefinition();
        assert($definition instanceof ContainerInterface);

        /** @var DefaultModelRelationshipDefinition $relationships */
        $relationships = $definition->getDefinition('model-relationships');

        $childConditions = $relationships->getChildConditions($modelId->getDataProviderName());

        // delete child element before delete parent element
        /** @var ParentChildConditionInterface $childCondition */
        foreach ($childConditions as $childCondition) {
            $destinationChildConditions = $relationships->getChildConditions($childCondition->getDestinationName());
            if (empty($destinationChildConditions)) {
                continue;
            }

            $dataProvider = $environment->getDataProvider($modelId->getDataProviderName());
            assert($dataProvider instanceof DataProviderInterface);

            $model = $dataProvider->fetch($dataProvider->getEmptyConfig()->setId($modelId->getId()));
            assert($model instanceof ModelInterface);

            $destinationChildDataProvider = $environment->getDataProvider($childCondition->getDestinationName());
            assert($destinationChildDataProvider instanceof DataProviderInterface);


            /** @var DefaultCollection $destinationChildModels */
            $destinationChildModels = $destinationChildDataProvider->fetchAll(
                $dataProvider->getEmptyConfig()->setFilter($childCondition->getFilter($model))
            );
            if ($destinationChildModels->count() < 1) {
                continue;
            }

            foreach ($destinationChildModels as $destinationChildModel) {
                $this->deepDelete($environment, ModelId::fromModel($destinationChildModel));
            }
        }

        foreach ($childConditions as $childCondition) {
            $dataProvider = $environment->getDataProvider($modelId->getDataProviderName());
            assert($dataProvider instanceof DataProviderInterface);

            $model = $dataProvider->fetch($dataProvider->getEmptyConfig()->setId($modelId->getId()));
            assert($model instanceof ModelInterface);

            $childDataProvider = $environment->getDataProvider($childCondition->getDestinationName());
            assert($childDataProvider instanceof DataProviderInterface);

            $filters = $childCondition->getFilter($model);
            /** @var DefaultCollection $childModels */
            $childModels = $childDataProvider->fetchAll($dataProvider->getEmptyConfig()->setFilter($filters));
            if ($childModels->count() < 1) {
                continue;
            }

            foreach ($childModels as $childModel) {
                if (null === ($dispatcher = $environment->getEventDispatcher())) {
                    continue;
                }

                // Trigger event before the model will be deleted.
                $preDeleteEvent = new PreDeleteModelEvent($environment, $childModel);
                $dispatcher->dispatch($preDeleteEvent, $preDeleteEvent::NAME);

                $childDataProvider->delete($childModel);

                // Trigger event after the model is deleted.
                $postDeleteEvent = new PostDeleteModelEvent($environment, $childModel);
                $dispatcher->dispatch($postDeleteEvent, $postDeleteEvent::NAME);
            }
        }
    }
}
