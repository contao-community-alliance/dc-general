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

namespace DcGeneral\DataDefinition\Definition\View\Panel;

/**
 * Class DefaultFilterElementInformation.
 *
 * Default implementation of a filter definition for a property.
 *
 * @package DcGeneral\DataDefinition\Definition\View\Panel
 */
class DefaultFilterElementInformation implements FilterElementInformationInterface
{
	/**
	 * The name of the property to filter on.
	 *
	 * @var string
	 */
	protected $propertyName;

	/**
	 * {@inheritDoc}
	 */
	public function getName()
	{
		return sprintf('filter[%s]', $this->getPropertyName());
	}

	/**
	 * {@inheritDoc}
	 */
	public function setPropertyName($propertyName)
	{
		$this->propertyName = $propertyName;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getPropertyName()
	{
		return $this->propertyName;
	}
}
