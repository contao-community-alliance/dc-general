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
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @copyright  2013-2015 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\ActionHandler;

use ContaoCommunityAlliance\Contao\Bindings\ContaoEvents;
use ContaoCommunityAlliance\Contao\Bindings\Events\Controller\RedirectEvent;
use ContaoCommunityAlliance\Contao\Bindings\Events\System\LogEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Exception\EditOnlyModeException;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Exception\NotDeletableException;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\ViewHelpers;
use ContaoCommunityAlliance\DcGeneral\Data\ModelId;
use ContaoCommunityAlliance\DcGeneral\Data\ModelIdInterface;
use ContaoCommunityAlliance\DcGeneral\Data\ModelInterface;
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
     * Delete all children.
     *
     * @return void
     */
    protected function deleteChildren()
    {
        // Not yet impl.
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

        $this->deleteChildren();

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

        $this->delete($modelId);

        ViewHelpers::redirectHome($this->environment);
    }
}
