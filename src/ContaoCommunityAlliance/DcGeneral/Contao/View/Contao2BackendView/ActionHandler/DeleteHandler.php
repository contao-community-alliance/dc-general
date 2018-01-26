<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2018 Contao Community Alliance.
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
 * @copyright  2013-2018 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\ActionHandler;

use ContaoCommunityAlliance\Contao\Bindings\ContaoEvents;
use ContaoCommunityAlliance\Contao\Bindings\Events\Controller\RedirectEvent;
use ContaoCommunityAlliance\Contao\Bindings\Events\System\LogEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Exception\EditOnlyModeException;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Exception\NotDeletableException;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\ViewHelpers;
use ContaoCommunityAlliance\DcGeneral\Data\DefaultCollection;
use ContaoCommunityAlliance\DcGeneral\Data\ModelId;
use ContaoCommunityAlliance\DcGeneral\Data\ModelIdInterface;
use ContaoCommunityAlliance\DcGeneral\Data\ModelInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\DefaultModelRelationshipDefinition;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\ModelRelationship\ParentChildConditionInterface;
use ContaoCommunityAlliance\DcGeneral\Event\PostDeleteModelEvent;
use ContaoCommunityAlliance\DcGeneral\Event\PreDeleteModelEvent;
use ContaoCommunityAlliance\DcGeneral\Exception\DcGeneralRuntimeException;
use ContaoCommunityAlliance\DcGeneral\View\ActionHandler\AbstractEnvironmentAwareHandler;

/**
 * Class DeleteHandler handles the delete action.
 */
class DeleteHandler extends AbstractEnvironmentAwareHandler
{
    /**
     * Check if is it allowed to delete a record.
     *
     * @param ModelIdInterface $modelId  The model id.
     * @param bool             $redirect If true it redirects to error page instead of throwing an exception.
     *
     * @return void
     *
     * @throws NotDeletableException If deletion is disabled.
     */
    protected function guardIsDeletable(ModelIdInterface $modelId, $redirect = false)
    {
        if ($this->getEnvironment()->getDataDefinition()->getBasicDefinition()->isDeletable()) {
            return;
        }

        if ($redirect) {
            $this->getEnvironment()->getEventDispatcher()->dispatch(
                ContaoEvents::SYSTEM_LOG,
                new LogEvent(
                    sprintf(
                        'Table "%s" is not deletable',
                        'DC_General - DefaultController - delete()',
                        $this->getEnvironment()->getDataDefinition()->getName()
                    ),
                    __CLASS__ . '::delete()',
                    TL_ERROR
                )
            );

            $this->getEnvironment()->getEventDispatcher()->dispatch(
                ContaoEvents::CONTROLLER_REDIRECT,
                new RedirectEvent('contao/main.php?act=error')
            );
        }

        throw new NotDeletableException($modelId->getDataProviderName());
    }

    /**
     * Fetch the model.
     *
     * @param ModelIdInterface $modelId The model id.
     *
     * @return ModelInterface
     *
     * @throws DcGeneralRuntimeException If no model are found.
     */
    protected function fetchModel(ModelIdInterface $modelId)
    {
        $dataProvider = $this->getEnvironment()->getDataProvider($modelId->getDataProviderName());
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
     * @param ModelIdInterface $modelId The model id.
     *
     * @return void
     *
     * @throws EditOnlyModeException     If the data definition is in edit only mode.
     * @throws NotDeletableException     If the data definition does not allow delete actions.
     * @throws DcGeneralRuntimeException If the model is not found.
     */
    public function delete(ModelIdInterface $modelId)
    {
        $this->guardNotEditOnly($modelId);
        $this->guardIsDeletable($modelId);

        $environment = $this->getEnvironment();
        $model       = $this->fetchModel($modelId);

        // Trigger event before the model will be deleted.
        $event = new PreDeleteModelEvent($this->getEnvironment(), $model);
        $environment->getEventDispatcher()->dispatch($event::NAME, $event);

        $dataProvider = $environment->getDataProvider($modelId->getDataProviderName());
        $dataProvider->delete($model);

        // Trigger event after the model is deleted.
        $event = new PostDeleteModelEvent($environment, $model);
        $environment->getEventDispatcher()->dispatch($event::NAME, $event);
    }

    /**
     * {@inheritdoc}
     */
    public function process()
    {
        if ($this->getEvent()->getAction()->getName() !== 'delete') {
            return;
        }

        if (false === $this->checkPermission()) {
            $this->getEvent()->stopPropagation();

            return;
        }

        $environment = $this->getEnvironment();
        $modelId     = ModelId::fromSerialized($environment->getInputProvider()->getParameter('id'));

        // Guard that we are in the preloaded environment. Otherwise checking the data definition could belong to
        // another model.
        $this->guardValidEnvironment($modelId);

        // Only edit mode is supported. Trigger an edit action.
        if ($this->isEditOnlyResponse()) {
            return;
        }

        // We want a redirect here if not deletable.
        $this->guardIsDeletable($modelId, true);
        $this->deepDelete($modelId);
        $this->delete($modelId);

        ViewHelpers::redirectHome($this->environment);
    }

    /**
     * Check permission for delete a model.
     *
     * @return bool
     */
    private function checkPermission()
    {
        $environment     = $this->getEnvironment();
        $dataDefinition  = $environment->getDataDefinition();
        $basicDefinition = $dataDefinition->getBasicDefinition();

        $modelId = ModelId::fromSerialized($environment->getInputProvider()->getParameter('id'));

        if (true === $basicDefinition->isDeletable()) {
            return true;
        }

        $this->getEvent()->setResponse(
            sprintf(
                '<div style="text-align:center; font-weight:bold; padding:40px;">
                    You have no permission for delete model %s.
                </div>',
                $modelId->getSerialized()
            )
        );

        return false;
    }

    /**
     * Delete all deep models.
     *
     * @param ModelIdInterface $modelId The Model Id.
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.LongVariableName)
     */
    protected function deepDelete(ModelIdInterface $modelId)
    {
        $environment    = $this->getEnvironment();
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
                $this->deepDelete(ModelId::fromModel($destinationChildModel));
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
                $event = new PreDeleteModelEvent($this->getEnvironment(), $childModel);
                $environment->getEventDispatcher()->dispatch($event::NAME, $event);

                $childDataProvider->delete($childModel);

                // Trigger event after the model is deleted.
                $event = new PostDeleteModelEvent($environment, $childModel);
                $environment->getEventDispatcher()->dispatch($event::NAME, $event);
            }
        }
    }
}
