<?php
/**
 * PHP version 5
 * @package    generalDriver
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Tristan Lins <tristan.lins@bit3.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\View;

use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;

/**
 * The interface for a view.
 *
 * @package ContaoCommunityAlliance\DcGeneral\View
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
	 * @return void
	 */
	public function paste();

	/**
	 * Endpoint for copying a model (including child models).
	 *
	 * @return string
	 */
	public function copy();

	/**
	 * Endpoint for copying multiple models (including child models).
	 *
	 * @return string
	 */
	public function copyAll();

	/**
	 * Endpoint for create operation.
	 *
	 * @return string
	 */
	public function create();

	/**
	 * Endpoint for cutting a model (including child models).
	 *
	 * @return string
	 */
	public function cut();

	/**
	 * Endpoint for cutting multiple models (including child models).
	 *
	 * @return string
	 */
	public function cutAll();

	/**
	 * Delete a model and redirect the user to the listing.
	 *
	 * NOTE: This method redirects the user to the listing and therefore the script will be ended.
	 *
	 * @return void
	 */
	public function delete();

	/**
	 * Endpoint for edit operation.
	 *
	 * @return string
	 */
	public function edit();

	/**
	 * Endpoint for move operation.
	 *
	 * @return string
	 */
	public function move();

	/**
	 * Endpoint for show operation.
	 *
	 * @return string
	 */
	public function show();

	/**
	 * Overview listing over all items in the current scope.
	 *
	 * This is the default action to perform if no other action has been specified in the URL.
	 *
	 * @return string
	 */
	public function showAll();

	/**
	 * Endpoint for undo operation.
	 *
	 * @return string
	 */
	public function undo();
}
