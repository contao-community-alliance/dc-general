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

namespace ContaoCommunityAlliance\DcGeneral;

/**
 * Base interface providing access to an environment.
 *
 * @package DcGeneral
 */
interface EnvironmentAwareInterface
{
	/**
	 * Retrieve the environment.
	 *
	 * @return EnvironmentInterface
	 */
	public function getEnvironment();
}
