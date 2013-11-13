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
use DcGeneral\Exception\DcGeneralInvalidArgumentException;

interface WidgetManagerInterface
{
	/**
	 * Get the current environment.
	 *
	 * @return EnvironmentInterface
	 */
	public function getEnvironment();

	/**
	 * Check if the given property has a widget.
	 *
	 * @param $property
	 *
	 * @return bool
	 */
	public function hasWidget($property);

	/**
	 * Return the widget for a given property.
	 *
	 * @param string $property
	 *
	 * @throws \DcGeneral\Exception\DcGeneralInvalidArgumentException
	 * @return \Widget
	 */
	public function getWidget($property);

	/**
	 * Render the named widget to an html string.
	 *
	 * @param string $property
	 *
	 * @return string
	 */
	public function renderWidget($property);

	/**
	 * Process all values from the PropertyValueBag through the widgets.
	 *
	 * @param PropertyValueBag $propertyValues
	 */
	public function processInput(PropertyValueBag $propertyValues);

	/**
	 * Process all errors from the PropertyValueBag and add them to the widgets.
	 *
	 * @param PropertyValueBag $propertyValues
	 */
	public function processErrors(PropertyValueBag $propertyValues);
}
