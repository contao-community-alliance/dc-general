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

namespace ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette;

use ContaoCommunityAlliance\DcGeneral\Data\ModelInterface;
use ContaoCommunityAlliance\DcGeneral\Data\PropertyValueBag;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Condition\Palette\PaletteConditionInterface;
use ContaoCommunityAlliance\DcGeneral\Exception\DcGeneralInvalidArgumentException;
use ContaoCommunityAlliance\DcGeneral\Exception\DcGeneralRuntimeException;

/**
 * Default implementation of a palette.
 */
class Palette implements PaletteInterface
{
	/**
	 * The name of this palette.
	 *
	 * @var string
	 *
	 * @deprecated Only for backwards compatibility, we will remove palette names in the future!
	 */
	protected $name = null;

	/**
	 * List of all legends in this palette.
	 *
	 * @var array|LegendInterface[]
	 */
	protected $legends = array();

	/**
	 * The condition bound to this palette.
	 *
	 * @var PaletteConditionInterface|null
	 */
	protected $condition = null;

	/**
	 * {@inheritdoc}
	 */
	public function setName($name)
	{
		$this->name = (string)$name;

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
	public function getProperties(ModelInterface $model = null, PropertyValueBag $input = null)
	{
		$properties = array();

		foreach ($this->legends as $legend)
		{
			$properties = array_merge($properties, $legend->getProperties($model, $input));
		}

		return $properties;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getVisibleProperties(ModelInterface $model = null, PropertyValueBag $input = null)
	{
		$properties = array();

		foreach ($this->getLegends() as $legend)
		{
			foreach ($legend->getProperties($model, $input) as $property)
			{
				if ($property->isVisible($model, $input, $legend))
				{
					$properties[] = $property;
				}
			}
		}

		return $properties;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getEditableProperties(ModelInterface $model = null, PropertyValueBag $input = null)
	{
		$properties = array();

		foreach ($this->getLegends() as $legend)
		{
			foreach ($legend->getProperties($model, $input) as $property)
			{
				if ($property->isEditable($model, $input, $legend))
				{
					$properties[] = $property;
				}
			}
		}

		return $properties;
	}

	/**
	 * {@inheritdoc}
	 */
	public function clearLegends()
	{
		$this->legends = array();

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function setLegends(array $legends)
	{
		$this->clearLegends();
		$this->addLegends($legends);

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function addLegends(array $legends, LegendInterface $before = null)
	{
		foreach ($legends as $legend)
		{
			$this->addLegend($legend, $before);
		}

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function hasLegend($name)
	{
		foreach ($this->legends as $legend)
		{
			if ($legend->getName() == $name)
			{
				return true;
			}
		}

		return false;
	}

	/**
	 * {@inheritdoc}
	 */
	public function containsLegend(LegendInterface $legend)
	{
		$hash = spl_object_hash($legend);
		return isset($this->legends[$hash]);
	}

	/**
	 * {@inheritdoc}
	 *
	 * @throws DcGeneralInvalidArgumentException when the legend passed as $before can not be found.
	 */
	public function addLegend(LegendInterface $legend, LegendInterface $before = null)
	{
		$hash = spl_object_hash($legend);

		if ($before)
		{
			$beforeHash = spl_object_hash($before);

			if (isset($this->legends[$beforeHash]))
			{
				$hashes   = array_keys($this->legends);
				$position = array_search($beforeHash, $hashes);

				$this->legends = array_merge(
					array_slice($this->legends, 0, $position),
					array($hash => $legend),
					array_slice($this->legends, $position)
				);
			}
			else
			{
				throw new DcGeneralInvalidArgumentException(
					sprintf(
						'Legend %s not contained in palette - can not add %s after it.',
						$before->getName(),
						$legend->getName()
					)
				);
			}
		}
		else
		{
			$this->legends[$hash] = $legend;
		}

		$legend->setPalette($this);
		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function removeLegend(LegendInterface $legend)
	{
		$hash = spl_object_hash($legend);
		unset($this->legends[$hash]);

		return $this;
	}

	/**
	 * {@inheritdoc}
	 *
	 * @throws DcGeneralRuntimeException When the legend does not exist.
	 */
	public function getLegend($name)
	{
		foreach ($this->legends as $legend)
		{
			if ($legend->getName() == $name)
			{
				return $legend;
			}
		}

		throw new DcGeneralRuntimeException('Legend "' . $name . '" does not exists');
	}

	/**
	 * {@inheritdoc}
	 */
	public function getLegends()
	{
		return array_values($this->legends);
	}

	/**
	 * {@inheritdoc}
	 */
	public function setCondition(PaletteConditionInterface $condition = null)
	{
		$this->condition = $condition;

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getCondition()
	{
		return $this->condition;
	}

	/**
	 * {@inheritdoc}
	 */
	public function __clone()
	{
		/** @var Legend[] $legends */
		$legends = array();
		foreach ($this->legends as $legend)
		{
			$bobaFett = clone $legend;

			$legends[spl_object_hash($bobaFett)] = $bobaFett->setPalette($this);
		}
		$this->legends = $legends;

		if ($this->condition !== null)
		{
			$this->condition = clone $this->condition;
		}
	}
}
