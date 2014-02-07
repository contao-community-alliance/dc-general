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

/**
 * This class holds everything together.
 *
 * @package DcGeneral
 */
class DcGeneral
	implements EnvironmentAwareInterface
{
	/**
	 * The environment instance.
	 *
	 * @var EnvironmentInterface
	 */
	protected $environment;

	/**
	 * Create a new instance.
	 *
	 * @param EnvironmentInterface $environment The environment.
	 */
	public function __construct(EnvironmentInterface $environment)
	{
		$this->environment = $environment;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getEnvironment()
	{
		return $this->environment;
	}
}
