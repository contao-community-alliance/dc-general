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

namespace ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View;

/**
 * Format a model and create a listing child record.
 *
 * @package DcGeneral\DataDefinition\Definition\View
 */
class DefaultModelFormatterConfig implements ModelFormatterConfigInterface
{
	/**
	 * The used property names.
	 *
	 * @var array
	 */
	protected $propertyNames = array();

	/**
	 * The format string.
	 *
	 * @var string
	 */
	protected $format = '%s';

	/**
	 * The maximum length of the formatted string.
	 *
	 * @var int|null
	 */
	protected $maxLength = null;

	/**
	 * {@inheritDoc}
	 */
	public function setPropertyNames(array $propertyNames)
	{
		$this->propertyNames = $propertyNames;

		return $this;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getPropertyNames()
	{
		return $this->propertyNames;
	}

	/**
	 * {@inheritDoc}
	 */
	public function setFormat($format)
	{
		$this->format = (string)$format;

		return $this;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getFormat()
	{
		return $this->format;
	}

	/**
	 * {@inheritDoc}
	 */
	public function setMaxLength($maxLength)
	{
		$this->maxLength = ($maxLength !== null) ? (int)$maxLength : null;

		return $this;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getMaxLength()
	{
		return $this->maxLength;
	}
}
