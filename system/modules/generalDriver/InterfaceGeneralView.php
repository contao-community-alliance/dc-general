<?php

/**
 * PHP version 5
 * @package    generalDriver
 * @author     Stefan Heimes <cms@men-at-work.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

interface InterfaceGeneralView
{

	/**
	 * Set the DC
	 *
	 * @param DC_General $objDC
	 */
	public function setDC($objDC);

	/**
	 * Get the DC
	 *
	 * @return DC_General
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