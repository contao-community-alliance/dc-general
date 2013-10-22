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

namespace DcGeneral\DataDefinition\Palette;

use DcGeneral\Data\ModelInterface;
use DcGeneral\Data\PropertyValueBag;
use DcGeneral\DataDefinition\Palette\Condition\Property\PropertyConditionInterface;

interface PropertyInterface
{
	/**
	 * Set the name of the property.
	 *
	 * @param string $name
	 *
	 * @return PropertyInterface
	 */
	public function setName($name);

	/**
	 * Return the name of the property.
	 *
	 * @return string
	 */
	public function getName();

	/**
	 * Check the conditions, if this property is visible.
	 *
	 * @param ModelInterface|null $model If given, subpalettes will be evaluated depending on the model.
	 * If no model is given, all properties will be returned, including subpalette properties.
	 * @param PropertyValueBag $input If given, subpalettes will be evaluated depending on the input data.
	 * If no model and no input data is given, all properties will be returned, including subpalette properties.
	 *
	 * @return bool
	 */
	public function isVisible(ModelInterface $model = null, PropertyValueBag $input = null);

	/**
	 * Check the conditions, if this property is editable.
	 *
	 * @param ModelInterface|null $model If given, subpalettes will be evaluated depending on the model.
	 * If no model is given, all properties will be returned, including subpalette properties.
	 * @param PropertyValueBag $input If given, subpalettes will be evaluated depending on the input data.
	 * If no model and no input data is given, all properties will be returned, including subpalette properties.
	 *
	 * @return bool
	 */
	public function isEditable(ModelInterface $model = null, PropertyValueBag $input = null);

	/**
	 * Set the visible condition for this property.
	 *
	 * @param PropertyConditionInterface $condition
	 *
	 * @return PropertyInterface
	 */
	public function setVisibleCondition(PropertyConditionInterface $condition = null);

	/**
	 * Get the visible condition for this property.
	 *
	 * @return PropertyConditionInterface
	 */
	public function getVisibleCondition();

	/**
	 * Set the editable condition for this property.
	 *
	 * @param PropertyConditionInterface $condition
	 *
	 * @return PropertyInterface
	 */
	public function setEditableCondition(PropertyConditionInterface $condition = null);

	/**
	 * Get the editable condition for this property.
	 *
	 * @return PropertyConditionInterface
	 */
	public function getEditableCondition();
}
