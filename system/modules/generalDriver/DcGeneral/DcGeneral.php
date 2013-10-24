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

namespace DcGeneral;

class DcGeneral
{
	/**
	 * @var EnvironmentInterface
	 */
	protected $environment;

	function __construct(EnvironmentInterface $environment)
	{
		$this->environment = $environment;
	}

	/**
	 * @return EnvironmentInterface
	 */
	public function getEnvironment()
	{
		return $this->environment;
	}
}
