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
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Tristan Lins <tristan.lins@bit3.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2013-2019 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\View;

use ContaoCommunityAlliance\DcGeneral\Action;
use ContaoCommunityAlliance\DcGeneral\Data\ModelInterface;
use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;

/**
 * The interface for a view.
 */
interface ViewInterface
{
    /**
     * Set the environment.
     *
     * @param EnvironmentInterface $environment The environment.
     *
     * @return ViewInterface
     */
    public function setEnvironment(EnvironmentInterface $environment);

    /**
     * Retrieve the attached environment.
     *
     * @return EnvironmentInterface
     */
    public function getEnvironment();

    /**
     * Handle an ajax request.
     *
     * @return void
     */
    public function handleAjaxCall();

    /**
     * Invoked for cut and copy - inserts the content of the clipboard at the given position.
     *
     * @param Action $action The action being executed.
     *
     * @return void
     */
    public function paste(Action $action);

    /**
     * Endpoint for create operation.
     *
     * @param Action $action The action being executed.
     *
     * @return string
     */
    public function create(Action $action);

    /**
     * Delete a model and redirect the user to the listing.
     *
     * NOTE: This method redirects the user to the listing and therefore the script will be ended.
     *
     * @param Action $action The action being executed.
     *
     * @return void
     */
    public function delete(Action $action);

    /**
     * Endpoint for edit operation.
     *
     * @param Action $action The action being executed.
     *
     * @return string
     */
    public function edit(Action $action);

    /**
     * Endpoint for move operation.
     *
     * @param Action $action The action being executed.
     *
     * @return string
     */
    public function move(Action $action);

    /**
     * Overview listing over all items in the current scope.
     *
     * This is the default action to perform if no other action has been specified in the URL.
     *
     * @param Action $action The action being executed.
     *
     * @return string
     */
    public function showAll(Action $action);

    /**
     * Endpoint for undo operation.
     *
     * @param Action $action The action being executed.
     *
     * @return string
     */
    public function undo(Action $action);

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
     * @see \ContaoCommunityAlliance\DcGeneral\Event\EnforceModelRelationshipEvent
     */
    public function enforceModelRelationship($model);
}
