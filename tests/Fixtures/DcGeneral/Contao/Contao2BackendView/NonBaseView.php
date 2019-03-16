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
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2013-2019 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Test\Fixtures\DcGeneral\Contao\Contao2BackendView;

use ContaoCommunityAlliance\DcGeneral\Action;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\BackendViewInterface;
use ContaoCommunityAlliance\DcGeneral\Data\ModelInterface;
use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;
use ContaoCommunityAlliance\DcGeneral\Panel\PanelContainerInterface;
use ContaoCommunityAlliance\DcGeneral\View\ViewInterface;

/**
 * This simluate a non instance of BaseView.
 */
class NonBaseView implements BackendViewInterface
{
    /**
     * Set the panel container.
     *
     * @param PanelContainerInterface $panelContainer The panel container.
     *
     * @return BackendViewInterface
     */
    public function setPanel($panelContainer)
    {
        // TODO: Implement setPanel() method.
    }

    /**
     * Retrieve the panel container from the view.
     *
     * @return PanelContainerInterface
     */
    public function getPanel()
    {
        // TODO: Implement getPanel() method.
    }

    /**
     * Set the environment.
     *
     * @param EnvironmentInterface $environment The environment.
     *
     * @return ViewInterface
     */
    public function setEnvironment(EnvironmentInterface $environment)
    {
        // TODO: Implement setEnvironment() method.
    }

    /**
     * Retrieve the attached environment.
     *
     * @return EnvironmentInterface
     */
    public function getEnvironment()
    {
        // TODO: Implement getEnvironment() method.
    }

    /**
     * Handle an ajax request.
     *
     * @return void
     */
    public function handleAjaxCall()
    {
        // TODO: Implement handleAjaxCall() method.
    }

    /**
     * Invoked for cut and copy - inserts the content of the clipboard at the given position.
     *
     * @param Action $action The action being executed.
     *
     * @return void
     */
    public function paste(Action $action)
    {
        // TODO: Implement paste() method.
    }

    /**
     * Endpoint for create operation.
     *
     * @param Action $action The action being executed.
     *
     * @return string
     */
    public function create(Action $action)
    {
        // TODO: Implement create() method.
    }

    /**
     * Delete a model and redirect the user to the listing.
     *
     * NOTE: This method redirects the user to the listing and therefore the script will be ended.
     *
     * @param Action $action The action being executed.
     *
     * @return void
     */
    public function delete(Action $action)
    {
        // TODO: Implement delete() method.
    }

    /**
     * Endpoint for edit operation.
     *
     * @param Action $action The action being executed.
     *
     * @return string
     */
    public function edit(Action $action)
    {
        // TODO: Implement edit() method.
    }

    /**
     * Endpoint for move operation.
     *
     * @param Action $action The action being executed.
     *
     * @return string
     */
    public function move(Action $action)
    {
        // TODO: Implement move() method.
    }

    /**
     * Overview listing over all items in the current scope.
     *
     * This is the default action to perform if no other action has been specified in the URL.
     *
     * @param Action $action The action being executed.
     *
     * @return string
     */
    public function showAll(Action $action)
    {
        // TODO: Implement showAll() method.
    }

    /**
     * Endpoint for undo operation.
     *
     * @param Action $action The action being executed.
     *
     * @return string
     */
    public function undo(Action $action)
    {
        // TODO: Implement undo() method.
    }

    /**
     * Abstract method to be overridden in the certain child classes.
     *
     * This method will update the parent relationship between a model and the parent item.
     *
     * @param ModelInterface $model The model to be updated.
     *
     * @return void
     *
     * @deprecated Use listener on EnforceModelRelationshipEvent instead.
     *
     * @see        \ContaoCommunityAlliance\DcGeneral\Event\EnforceModelRelationshipEvent
     */
    public function enforceModelRelationship($model)
    {
        // TODO: Implement enforceModelRelationship() method.
    }
}
