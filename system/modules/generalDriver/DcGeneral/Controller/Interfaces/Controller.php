<?php
/**
 * PHP version 5
 * @package    generalDriver
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

namespace DcGeneral\Controller\Interfaces;

use DcGeneral\Interfaces\DataContainer;

// TODO: we need to flesh this out some more out and add real interface methods. Currently this interface is rather useless.
interface Controller
{

	/**
	 * Set the DataContainer.
	 *
	 * @param DataContainer $objDC
	 */
	public function setDC($objDC);

	/**
	 * Get the DataContainer.
	 *
	 * @return DataContainer
	 */
	public function getDC();

	public function generateAjaxPalette($strSelector);

	public function ajaxTreeView($mixID, $intLevel);

	public function copy();

	public function create();

	public function cut();

	public function delete();

	public function edit();

	public function move();

	public function show();

	/**
	 * Show all entries from a table
	 *
	 * @return void | String if error
	 */
	public function showAll();

	public function executePostActions();
}
