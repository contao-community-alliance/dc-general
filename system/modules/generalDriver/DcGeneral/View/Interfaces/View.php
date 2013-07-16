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

namespace DcGeneral\View\Interfaces;

use DcGeneral\Interfaces\DataContainer;

// TODO: we need to flesh this out some more out and add real interface methods. Currently this interface is rather useless.
interface View
{
	/**
	 * Set the DC
	 *
	 * @param DataContainer $objDC
	 */
	public function setDC($objDC);

	/**
	 * Get the DC
	 *
	 * @return DataContainer
	 */
	public function getDC();

	public function copy();

	public function copyAll();

	public function create();

	public function cut();

	public function cutAll();

	public function delete();

	public function edit();

	public function move();

	public function show();

	public function showAll();

	public function undo();

	public function generateAjaxPalette($strSelector);

}
