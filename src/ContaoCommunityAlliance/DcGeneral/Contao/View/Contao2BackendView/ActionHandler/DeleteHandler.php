<?php
/**
 * PHP version 5
 *
 * @package    generalDriver
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\ActionHandler;

use ContaoCommunityAlliance\Contao\Bindings\ContaoEvents;
use ContaoCommunityAlliance\Contao\Bindings\Events\Controller\RedirectEvent;
use ContaoCommunityAlliance\Contao\Bindings\Events\System\LogEvent;
use ContaoCommunityAlliance\DcGeneral\Action;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Exception\EditOnlyModeException;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Exception\NotDeletableException;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\ViewHelpers;
use ContaoCommunityAlliance\DcGeneral\Data\ModelId;
use ContaoCommunityAlliance\DcGeneral\Data\ModelIdInterface;
use ContaoCommunityAlliance\DcGeneral\Data\ModelInterface;
use ContaoCommunityAlliance\DcGeneral\DcGeneralEvents;
use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;
use ContaoCommunityAlliance\DcGeneral\Event\ActionEvent;
use ContaoCommunityAlliance\DcGeneral\Event\PostDeleteModelEvent;
use ContaoCommunityAlliance\DcGeneral\Event\PreDeleteModelEvent;
use ContaoCommunityAlliance\DcGeneral\Exception\DcGeneralRuntimeException;

/**
 * Class DeleteHandler handles the delete action.
 *
 * @package ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Controller
 */
class DeleteHandler extends AbstractHandler
{
    /**
     * The environment.
     *
     * @var EnvironmentInterface
     */
    protected $environment;

    /**
     * Retrieve the environment.
     *
     * @return EnvironmentInterface
     */
    protected function getEnvironment()
    {
        return $this->environment;
    }

    /**
     * Set the environment. This is required for using handler for a non event modus.
     *
     * @param EnvironmentInterface $environment The environment.
     *
     * @return $this
     */
    public function setEnvironment(EnvironmentInterface $environment)
    {
        $this->environment = $environment;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function handleEvent(ActionEvent $event)
    {
        $this->setEnvironment($event->getEnvironment());
        parent::handleEvent($event);
    }

    /**
     * Guard that the environment is prepared for models data definition.
     *
     * @param ModelIdInterface $modelId The model id.
     *
     * @throws DcGeneralRuntimeException If data provider name of modelId and definition does not match.
     */
    private function guardValidEnvironment(ModelIdInterface $modelId)
    {
        if ($this->getEnvironment()->getDataDefinition()->getName() !== $modelId->getDataProviderName()) {
            throw new DcGeneralRuntimeException(
                sprintf(
                    'Not able to perform action. Environment is not prepared for model "%s"',
                    $modelId->getSerialized()
                )
            );
        }
    }

    /**
     * Guard that the data container is not in edit only mode.
     *
     * @param ModelIdInterface $modelId The model id.
     *
     * @return void
     *
     * @throws EditOnlyModeException If data container is in edit only mode.
     */
    protected function guardNotEditOnly(ModelIdInterface $modelId)
    {
        if ($this->getEnvironment()->getDataDefinition()->getBasicDefinition()->isEditOnlyMode()) {
            throw new EditOnlyModeException($modelId->getDataProviderName());
        }
    }

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
        $model = $dataProvider->fetch($dataProvider->getEmptyConfig()->setId($modelId->getId()));

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
        // FIXME: See DefaultController::delete() - we need to delete the children of this item as well over all
        //        data providers.
        /*
        $arrDelIDs = array();

        // Delete record
        switch ($definition->getSortingMode())
        {
            case 0:
            case 1:
            case 2:
            case 3:
            case 4:
                $arrDelIDs = array();
                $arrDelIDs[] = $intRecordID;
                break;

            case 5:
                $arrDelIDs = $environment->getController()->fetchMode5ChildrenOf($environment->getCurrentModel(), $blnRecurse = true);
                $arrDelIDs[] = $intRecordID;
                break;
        }

        // Delete all entries
        foreach ($arrDelIDs as $intId)
        {
            $this->getEnvironment()->getDataProvider()->delete($intId);

            // Add a log entry unless we are deleting from tl_log itself
            if ($environment->getDataDefinition()->getName() != 'tl_log')
            {
                BackendBindings::log('DELETE FROM ' . $environment->getDataDefinition()->getName() . ' WHERE id=' . $intId, 'DC_General - DefaultController - delete()', TL_GENERAL);
            }
        }
         */
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
        $this->guardValidEnvironment($modelId);
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
        if ($environment->getDataDefinition()->getBasicDefinition()->isEditOnlyMode()) {
            $event = new ActionEvent($environment, new Action('edit'));
            $environment->getEventDispatcher()->dispatch(DcGeneralEvents::ACTION, $event);
            $this->getEvent()->setResponse($event->getResponse());

            return;
        }

        // We want a redirect here if not deletable.
        $doRedirect = true;
        $this->guardIsDeletable($modelId, $doRedirect);

        $this->delete($modelId);

        ViewHelpers::redirectHome($this->environment);
    }
}
