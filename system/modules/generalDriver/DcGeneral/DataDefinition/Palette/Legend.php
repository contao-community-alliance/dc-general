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

namespace DcGeneral\DataDefinition\Palette;

use DcGeneral\Data\ModelInterface;
use DcGeneral\Data\PropertyValueBag;
use DcGeneral\Exception\DcGeneralInvalidArgumentException;

/**
 * Default implementation of a legend.
 */
class Legend implements LegendInterface
{
	/**
	 * The palette this legend belongs to.
	 *
	 * @var PaletteInterface|null
	 */
	protected $palette = null;

	/**
	 * The name of this legend.
	 *
	 * @var string
	 */
	protected $name = '';

	/**
	 * The properties in this legend.
	 *
	 * @var PropertyInterface[]|array
	 */
	protected $properties = array();

	function __construct($name)
	{
		$this->setName($name);
	}

	/**
	 * {@inheritdoc}
	 */
	public function setPalette(PaletteInterface $palette = null)
	{
		if ($this->palette) {
			$this->palette->removeLegend($this);
		}

		$this->palette = $palette;
		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getPalette()
	{
		return $this->palette;
	}

	/**
	 * {@inheritdoc}
	 */
	public function setName($name)
	{
		$this->name = (string) $name;
		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * {@inheritdoc}
	 */
	public function clearProperties()
	{
		$this->properties = array();
		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function setProperties(array $properties)
	{
		$this->clearProperties();
		$this->addProperties($properties);
		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function addProperties(array $properties, PropertyInterface $before = null)
	{
		foreach ($properties as $property) {
			$this->addProperty($property, $before);
		}
		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function addProperty(PropertyInterface $property, PropertyInterface $before = null)
	{
		$hash = spl_object_hash($property);

		if ($before) {
			$beforeHash = spl_object_hash($before);

			if (isset($this->properties[$beforeHash])) {
				$hashes = array_keys($this->properties);
				$position = array_search($beforeHash, $hashes);
				$this->properties = array_merge(
					array_slice($this->properties, 0, $position),
					array($hash => $property),
					array_slice($this->properties, $position)
				);
			}
			else {
				throw new DcGeneralInvalidArgumentException('Property ' . $before->getName() . ' not found');
			}
		}
		else {
			$this->properties[$hash] = $property;
		}

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function removeProperty(PropertyInterface $property)
	{
		$hash = spl_object_hash($property);
		unset($this->properties[$hash]);
		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getProperties(ModelInterface $model = null, PropertyValueBag $input = null)
	{
		if ($model || $input)
		{
			$selectedProperties = array();

			foreach ($this->properties as $property)
			{
				$condition = $property->getVisibleCondition();

				if (!$condition || $condition->match($model, $input))
				{
					$selectedProperties[] = $property;
				}
			}

			return $selectedProperties;
		}
		else
		{
			return array_values($this->properties);
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function __clone()
	{
		$this->palette = null;

		$properties = array();
		foreach ($this->properties as $index => $property) {
			$properties[$index] = clone $property;
		}
		$this->properties = $properties;
	}
}
