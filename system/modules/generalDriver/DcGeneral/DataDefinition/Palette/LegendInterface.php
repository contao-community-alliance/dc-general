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

/**
 * A legend group a lot of properties.
 */
interface LegendInterface
{
	/**
	 * Return the palette this legend belongs to.
	 *
	 * @return PaletteInterface
	 */
	public function getPalette();

	/**
	 * Return the name of this legend (e.g. "title", not "title_legend").
	 *
	 * @return string
	 */
	public function getName();

	/**
	 * Get all properties in this legend.
	 *
	 * @param ModelInterface|null $model If given, subpalettes will be evaluated depending on the model.
	 * If no model is given, all properties will be returned, including subpalette properties.
	 * @param PropertyValueBag $input If given, subpalettes will be evaluated depending on the input data.
	 * If no model and no input data is given, all properties will be returned, including subpalette properties.
	 *
	 * @return PropertyInterface[]
	 */
	public function getProperties(ModelInterface $model = null, PropertyValueBag $input = null);
}
