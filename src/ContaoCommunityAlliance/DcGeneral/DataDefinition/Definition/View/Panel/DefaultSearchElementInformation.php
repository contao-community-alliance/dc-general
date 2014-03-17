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

namespace ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\Panel;

/**
 * Class DefaultSearchElementInformation.
 *
 * Default implementation of a search definition on properties.
 *
 * @package DcGeneral\DataDefinition\Definition\View\Panel
 */
class DefaultSearchElementInformation implements SearchElementInformationInterface
{
	/**
	 * The property names to search on.
	 *
	 * @var array
	 */
	protected $properties;

	/**
	 * {@inheritDoc}
	 */
	public function getName()
	{
		return 'search';
	}

	/**
	 * {@inheritDoc}
	 */
	public function addProperty($propertyName)
	{
		$this->properties[] = $propertyName;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getPropertyNames()
	{
		return $this->properties;
	}
}
