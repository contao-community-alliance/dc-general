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
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2013-2017 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0
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
use ContaoCommunityAlliance\DcGeneral\Data\DefaultCollection;
use ContaoCommunityAlliance\DcGeneral\Data\ModelId;
use ContaoCommunityAlliance\DcGeneral\Data\ModelIdInterface;
use ContaoCommunityAlliance\DcGeneral\Data\ModelInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\DefaultModelRelationshipDefinition;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\ModelRelationship\ParentChildConditionInterface;
use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;
use ContaoCommunityAlliance\DcGeneral\Event\ActionEvent;
use ContaoCommunityAlliance\DcGeneral\Event\PostDeleteModelEvent;
use ContaoCommunityAlliance\DcGeneral\Event\PreDeleteModelEvent;
use ContaoCommunityAlliance\DcGeneral\Exception\DcGeneralRuntimeException;
use ContaoCommunityAlliance\DcGeneral\View\ActionHandler\ActionGuardTrait;
use ContaoCommunityAlliance\DcGeneral\View\ActionHandler\CallActionTrait;

/**
 * Class DeleteHandler handles the delete action.
 */
class DeleteHandler
{
    use ActionGuardTrait;
    use CallActionTrait;
    use RequestScopeDeterminatorAwareTrait;

    /**
     * DeleteHandler constructor.
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
        if (!$this->scopeDeterminator->currentScopeIsBackend()) {
            return;
        }

        if ($event->getAction()->getName() !== 'delete') {
            return;
        }

        if (true !== ($response = $this->checkPermission($event->getEnvironment()))) {
            $event->setResponse($response);
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
     */
    protected function guardIsDeletable(EnvironmentInterface $environment, ModelIdInterface $modelId, $redirect = false)
    {
        if ($environment->getDataDefinition()->getBasicDefinition()->isDeletable()) {
            return;
        }

        if ($redirect) {
            $environment->getEventDispatcher()->dispatch(
                ContaoEvents::SYSTEM_LOG,
                new LogEvent(
                    sprintf(
                        'Table "%s" is not deletable',
                        'DC_General - DefaultController - delete()',
                        $environment->getDataDefinition()->getName()
                    ),
                    __CLASS__ . '::delete()',
                    TL_ERROR
                )
            );

            $environment->getEventDispatcher()->dispatch(
                ContaoEvents::CONTROLLER_REDIRECT,
                new RedirectEvent('contao/main.php?act=error')
            );
        }

        throw new NotDeletableException($modelId->getDataProviderName());
    }

    /**
     * Fetch the model.
     *
     * @param EnvironmentInterface $environment The environment.
     * @param ModelIdInterface     $modelId     The model id.
     *
     * @return ModelInterface
     */
    protected function fetchModel(EnvironmentInterface $environment, ModelIdInterface $modelId)
    {
        $dataProvider = $environment->getDataProvider($modelId->getDataProviderName());
        $model        = $dataProvider->fetch($dataProvider->getEmptyConfig()->setId($modelId->getId()));

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
        $this->guardNotEditOnly($environment->getDataDefinition(), $modelId);
        $this->guardIsDeletable($environment, $modelId);

        $model = $this->fetchModel($environment, $modelId);

        // Trigger event before the model will be deleted.
        $event = new PreDeleteModelEvent($environment, $model);
        $environment->getEventDispatcher()->dispatch($event::NAME, $event);

        $dataProvider = $environment->getDataProvider($modelId->getDataProviderName());
        $dataProvider->delete($model);

        // Trigger event after the model is deleted.
        $event = new PostDeleteModelEvent($environment, $model);
        $environment->getEventDispatcher()->dispatch($event::NAME, $event);
    }

    /**
     * Process the action.
     *
     * @param EnvironmentInterface $environment The environment.
     *
     * @return string
     */
    protected function process(EnvironmentInterface $environment)
    {
        $modelId = ModelId::fromSerialized($environment->getInputProvider()->getParameter('id'));

        // Guard that we are in the preloaded environment. Otherwise checking the data definition could belong to
        // another model.
        $this->guardValidEnvironment($environment->getDataDefinition(), $modelId);

        // Only edit mode is supported. Trigger an edit action.
        if ($environment->getDataDefinition()->getBasicDefinition()->isEditOnlyMode()) {
            return $this->callAction($environment, 'edit');
        }

        // We want a redirect here if not deletable.
        $this->guardIsDeletable($environment, $modelId, true);
        $this->deepDelete($environment, $modelId);
        $this->delete($environment, $modelId);

        ViewHelpers::redirectHome($environment);
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
        $dataDefinition  = $environment->getDataDefinition();
        $basicDefinition = $dataDefinition->getBasicDefinition();

        $modelId = ModelId::fromSerialized($environment->getInputProvider()->getParameter('id'));

        if (true === $basicDefinition->isDeletable()) {
            return true;
        }

        return sprintf(
            '<div style="text-align:center; font-weight:bold; padding:40px;">
                You have no permission for delete model %s.
            </div>',
            $modelId->getSerialized()
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
        $dataDefinition = $environment->getDataDefinition();
        /** @var DefaultModelRelationshipDefinition $relationships */
        $relationships = $dataDefinition->getDefinition('model-relationships');

        $childConditions = $relationships->getChildConditions($modelId->getDataProviderName());

        // delete child element before delete parent element
        /** @var ParentChildConditionInterface $childCondition */
        foreach ($childConditions as $childCondition) {
            $destinationChildConditions = $relationships->getChildConditions($childCondition->getDestinationName());
            if (empty($destinationChildConditions)) {
                continue;
            }

            $dataProvider                 = $environment->getDataProvider($modelId->getDataProviderName());
            $model                        = $dataProvider->fetch(
                $dataProvider->getEmptyConfig()->setId($modelId->getId())
            );
            $destinationChildDataProvider = $environment->getDataProvider($childCondition->getDestinationName());

            $filters = $childCondition->getFilter($model);
            /** @var DefaultCollection $destinationChildModels */
            $destinationChildModels = $destinationChildDataProvider->fetchAll(
                $dataProvider->getEmptyConfig()->setFilter($filters)
            );
            if ($destinationChildModels->count() < 1) {
                continue;
            }

            foreach ($destinationChildModels as $destinationChildModel) {
                $this->deepDelete($environment, ModelId::fromModel($destinationChildModel));
            }
        }

        foreach ($childConditions as $childCondition) {
            $dataProvider      = $environment->getDataProvider($modelId->getDataProviderName());
            $model             = $dataProvider->fetch($dataProvider->getEmptyConfig()->setId($modelId->getId()));
            $childDataProvider = $environment->getDataProvider($childCondition->getDestinationName());

            $filters = $childCondition->getFilter($model);
            /** @var DefaultCollection $childModels */
            $childModels = $childDataProvider->fetchAll($dataProvider->getEmptyConfig()->setFilter($filters));
            if ($childModels->count() < 1) {
                continue;
            }

            foreach ($childModels as $childModel) {
                // Trigger event before the model will be deleted.
                $event = new PreDeleteModelEvent($environment, $childModel);
                $environment->getEventDispatcher()->dispatch($event::NAME, $event);

                $childDataProvider->delete($childModel);

                // Trigger event after the model is deleted.
                $event = new PostDeleteModelEvent($environment, $childModel);
                $environment->getEventDispatcher()->dispatch($event::NAME, $event);
            }
        }
    }
}
