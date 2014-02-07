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

use DcGeneral\DataDefinition\ContainerInterface;

/**
 * Base interface providing access to a data definition container.
 *
 * @package DcGeneral
 */
interface ContainerAwareInterface
{
	/**
	 * Retrieve the data definition container.
	 *
	 * @return ContainerInterface
	 */
	public function getContainer();
}
