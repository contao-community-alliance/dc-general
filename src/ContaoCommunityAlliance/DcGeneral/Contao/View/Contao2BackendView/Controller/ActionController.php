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

namespace ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Controller;

use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Exception\EditOnlyModeException;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Exception\NotDeleteableException;
use ContaoCommunityAlliance\DcGeneral\Data\ModelId;
use ContaoCommunityAlliance\DcGeneral\EnvironmentAwareInterface;
use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;
use ContaoCommunityAlliance\DcGeneral\Event\PostDeleteModelEvent;
use ContaoCommunityAlliance\DcGeneral\Event\PreDeleteModelEvent;
use ContaoCommunityAlliance\DcGeneral\Exception\DcGeneralRuntimeException;

/**
 * Class ActionController handles actions on the model.
 *
 * @package ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Controller
 */
class ActionController implements EnvironmentAwareInterface
{
    /**
     * The environment.
     *
     * @var EnvironmentInterface
     */
    protected $environment;

    /**
     * Construct.
     *
     * @param EnvironmentInterface $environment The environment.
     */
    public function __construct(EnvironmentInterface $environment)
    {
        $this->environment = $environment;
    }

    /**
     * {@inheritdoc}
     */
    public function getEnvironment()
    {
        return $this->environment;
    }

    /**
     * Guard that the environment is prepared for models data definition.
     *
     * @param ModelId $modelId The model id.
     *
     * @throws DcGeneralRuntimeException If data provider name of modelId and definition does not match.
     */
    private function guardValidEnvironment(ModelId $modelId)
    {
        if ($this->environment->getDataDefinition()->getName() !== $modelId->getDataProviderName()) {
            throw new DcGeneralRuntimeException(
                sprintf(
                    'Not able to perform action. Environment is not prepared for model "%s"',
                    $modelId->getSerialized()
                )
            );
        }
    }

    /**
     * Delete an model.
     *
     * @param ModelId $modelId The model id.
     *
     * @return void
     *
     * @throws EditOnlyModeException     If the data definition is in edit only mode.
     * @throws NotDeleteableException    If the data definition does not allow delete actions.
     * @throws DcGeneralRuntimeException If the model is not found.
     */
    public function delete(ModelId $modelId)
    {
        $this->guardValidEnvironment($modelId);

        if ($this->getEnvironment()->getDataDefinition()->getBasicDefinition()->isEditOnlyMode()) {
            throw new EditOnlyModeException($modelId->getDataProviderName());
        }

        // Check if is it allowed to delete a record.
        if (!$this->getEnvironment()->getDataDefinition()->getBasicDefinition()->isDeletable()) {
            throw new NotDeleteableException($modelId->getDataProviderName());
        }

        $environment  = $this->getEnvironment();
        $dataProvider = $environment->getDataProvider($modelId->getDataProviderName());
        $model        = $dataProvider->fetch($dataProvider->getEmptyConfig()->setId($modelId->getId()));

        if (!$model->getId()) {
            throw new DcGeneralRuntimeException(
                'Could not load model with id ' . $environment->getInputProvider()->getParameter('id')
            );
        }

        // Trigger event before the model will be deleted.
        $event = new PreDeleteModelEvent($environment, $model);
        $environment->getEventDispatcher()->dispatch(
            sprintf('%s[%s]', $event::NAME, $environment->getDataDefinition()->getName()),
            $event
        );
        $environment->getEventDispatcher()->dispatch($event::NAME, $event);

        // FIXME: See DefaultController::delete() - we need to delete the children of this item as well over all data providers.
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

        $dataProvider->delete($model);

        // Trigger event after the model is deleted.
        $event = new PostDeleteModelEvent($environment, $model);
        $environment->getEventDispatcher()->dispatch(
            sprintf('%s[%s]', $event::NAME, $environment->getDataDefinition()->getName()),
            $event
        );
        $environment->getEventDispatcher()->dispatch($event::NAME, $event);
    }
}
