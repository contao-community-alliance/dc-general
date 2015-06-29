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

namespace ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event;

use ContaoCommunityAlliance\DcGeneral\Action;
use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;
use ContaoCommunityAlliance\DcGeneral\Event\AbstractActionAwareEvent;

/**
 * Class PrepareMultipleModelsActionEvent.
 *
 * This event gets emitted when a multiple action shall be handled. A list of affected model ids are given. Use this
 * event to apply permission based filtering.
 *
 * @package DcGeneral\Contao\View\Contao2BackendView\Event
 */
class PrepareMultipleModelsActionEvent extends AbstractActionAwareEvent
{
    const NAME = 'dc-general.view.contao2backend.prepare-multiple-models-action';

    /**
     * The model ids.
     *
     * @var array
     */
    private $modelIds;

    /**
     * The submit action.
     *
     * @var string
     */
    private $submitAction;

    /**
     * Create a new instance.
     *
     * @param EnvironmentInterface $environment  The environment.
     * @param Action               $action       The called action.
     * @param array                $modelIds     The list of model ids being parsed.
     * @param string               $submitAction The submit action name.
     */
    public function __construct(EnvironmentInterface $environment, Action $action, array $modelIds, $submitAction)
    {
        parent::__construct($environment, $action);

        $this->modelIds     = $modelIds;
        $this->submitAction = $submitAction;
    }

    /**
     * Get modelIds.
     *
     * @return array
     */
    public function getModelIds()
    {
        return $this->modelIds;
    }

    /**
     * Set the model ids.
     *
     * @param array $modelIds The new model ids.
     *
     * @return $this
     */
    public function setModelIds(array $modelIds)
    {
        $this->modelIds = $modelIds;

        return $this;
    }

    /**
     * Get the submit action.
     *
     * @return string
     */
    public function getSubmitAction()
    {
        return $this->submitAction;
    }
}
