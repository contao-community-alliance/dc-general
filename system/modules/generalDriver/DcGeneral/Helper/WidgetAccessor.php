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

namespace DcGeneral\Helper;

class WidgetAccessor extends \Widget
{
	/**
	 * Static accessor method to reset all error state in the passed widget.
	 * Useful for clearing widget errors when SUBMIT_TYPE == 'auto'
	 *
	 * @param \Widget $objWidget the widget to clear the error information from.
	 * @return void
	 */
	public static function resetErrors($objWidget)
	{
		$objWidget->arrErrors = array();
		$objWidget->strClass = str_replace('error', '', $objWidget->strClass);
	}

	/**
	 * Dummy implementation to make this class non abstract.
	 * Does nothing aside from throwing an exception when called.
	 *
	 * @return void.
	 * @throws \Exception
	 */
	public function generate()
	{
		throw new \RuntimeException('This is not a real widget but only an accessor to all widgets.');
	}
}
