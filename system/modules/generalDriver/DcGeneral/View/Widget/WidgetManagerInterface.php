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

namespace DcGeneral\View\Widget;

use DcGeneral\Data\PropertyValueBag;
use DcGeneral\DataContainerInterface;
use DcGeneral\EnvironmentInterface;

interface WidgetManagerInterface
{
	/**
	 * Get the current environment.
	 *
	 * @return EnvironmentInterface
	 */
	public function getEnvironment();

	/**
	 * Check if the given field has a widget.
	 *
	 * @param $fieldName
	 *
	 * @return bool
	 */
	public function hasWidget($fieldName);

	/**
	 * Return the widget for a given field.
	 *
	 * @param $fieldName
	 *
	 * @return \Widget
	 *
	 * @throws \InvalidArgumentException
	 */
	public function getWidget($fieldName);

	/**
	 * Process all values from the PropertyValueBag through the widgets.
	 *
	 * @param PropertyValueBag $input
	 */
	public function processInput(PropertyValueBag $propertyValues);

	/**
	 * Process all errors from the PropertyValueBag and add them to the widgets.
	 *
	 * @param PropertyValueBag $input
	 */
	public function processErrors(PropertyValueBag $propertyValues);
}
