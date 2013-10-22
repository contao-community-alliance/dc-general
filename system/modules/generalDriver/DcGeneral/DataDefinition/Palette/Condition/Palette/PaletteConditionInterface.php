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

namespace DcGeneral\DataDefinition\Palette\Condition\Palette;

use DcGeneral\Data\ModelInterface;
use DcGeneral\Data\PropertyValueBag;
use DcGeneral\DataDefinition\ConditionInterface;

/**
 * A condition define when a palette is used or not.
 */
interface PaletteConditionInterface extends ConditionInterface
{
	/**
	 * Calculate how "strong" (aka "count of matches") this condition match the model and input parameters.
	 *
	 * @param ModelInterface|null $model If given, selectors will be evaluated depending on the model.
	 * @param PropertyValueBag $input If given, selectors will be evaluated depending on the input data.
	 *
	 * @return bool
	 */
	public function getMatchCount(ModelInterface $model = null, PropertyValueBag $input = null);
}
