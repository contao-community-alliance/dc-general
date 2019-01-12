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
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2013-2019 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event;

use ContaoCommunityAlliance\DcGeneral\Action;
use ContaoCommunityAlliance\DcGeneral\Data\ModelIdInterface;
use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;
use ContaoCommunityAlliance\DcGeneral\Event\AbstractActionAwareEvent;

/**
 * Class PrepareMultipleModelsActionEvent.
 *
 * This event gets emitted when a multiple action shall be handled. A list of affected model ids are given. Use this
 * event to apply permission based filtering.
 */
class PrepareMultipleModelsActionEvent extends AbstractActionAwareEvent
{
    const NAME = 'dc-general.view.contao2backend.prepare-multiple-models-action';

    /**
     * The model ids.
     *
     * @var ModelIdInterface[]
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
     * @param ModelIdInterface[]   $modelIds     The list of model ids being parsed.
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
     * @return ModelIdInterface[]
     */
    public function getModelIds()
    {
        return $this->modelIds;
    }

    /**
     * Set the model ids.
     *
     * @param ModelIdInterface[] $modelIds The new model ids.
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
