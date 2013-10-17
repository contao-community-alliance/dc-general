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

use DcGeneral\DataDefinition\ConditionInterface;
use DcGeneral\DataDefinition\PropertyInterface;

/**
 * A condition define when a property is visible or editable and when not.
 */
interface PropertyConditionInterface extends ConditionInterface
{
	/**
	 * Check if the property is visible under this condition.
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
	 * Check if the property is editable under this condition.
	 *
	 * @param ModelInterface|null $model If given, subpalettes will be evaluated depending on the model.
	 * If no model is given, all properties will be returned, including subpalette properties.
	 * @param PropertyValueBag $input If given, subpalettes will be evaluated depending on the input data.
	 * If no model and no input data is given, all properties will be returned, including subpalette properties.
	 *
	 * @return bool
	 */
	public function isEditable(ModelInterface $model = null, PropertyValueBag $input = null);
}
