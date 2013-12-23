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

namespace DcGeneral;

use DcGeneral\Data\ModelInterface;

/**
 * Base interface providing access to a model.
 *
 * @package DcGeneral
 */
interface ModelAwareInterface
{
	/**
	 * Retrieve the attached model.
	 *
	 * @return ModelInterface
	 */
	public function getModel();
}
