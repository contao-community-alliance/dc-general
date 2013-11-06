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

namespace DcGeneral\DataDefinition\Section\View\Panel;

class DefaultFilterElementInformation implements FilterElementInformationInterface
{
	/**
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
