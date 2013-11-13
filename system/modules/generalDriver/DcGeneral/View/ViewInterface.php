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

namespace DcGeneral\View;

use DcGeneral\Data\PropertyValueBag;
use DcGeneral\DataContainerInterface;
use DcGeneral\EnvironmentInterface;

// TODO: we need to flesh this out some more out and add real interface methods. Currently this interface is rather useless.
interface ViewInterface
{
	/**
	 * Set the environment.
	 *
	 * @param EnvironmentInterface $environment
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
	 * Set the DC
	 *
	 * @param DataContainerInterface $objDC
	 *
	 * @deprecated Please do only use the Environment.
	 */
	public function setDC($objDC);

	/**
	 * Get the DC
	 *
	 * @return DataContainerInterface
	 *
	 * @deprecated Please do only use the Environment.
	 */
	public function getDC();

	public function paste();

	public function copy();

	public function copyAll();

	public function create();

	public function cut();

	public function cutAll();

	public function delete();

	public function edit();

	public function move();

	public function show();

	/**
	 * Overview listing over all items in the current scope.
	 *
	 * This is the default action to perform if no other action has been specified in the URL.
	 *
	 * @return string
	 */
	public function showAll();

	public function undo();

	public function generateAjaxPalette($strSelector);

	/**
	 * Process input and return all modified properties or null if there is no input.
	 *
	 * @return null|PropertyValueBag
	 */
	public function processInput();
}
