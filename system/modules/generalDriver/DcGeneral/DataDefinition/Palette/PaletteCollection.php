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

use DcGeneral\Data\PropertyValueBag;
use DcGeneral\Data\ModelInterface;
use DcGeneral\Exception\DcGeneralInvalidArgumentException;

/**
 * Default implementation of PaletteCollectionInterface.
 */
class PaletteCollection implements PaletteCollectionInterface
{
	/**
	 * @var array|PaletteInterface[]
	 */
	protected $palettes = array();

	/**
	 * Remove all palettes from this collection.
	 *
	 * @return PaletteCollectionInterface
	 */
	public function clearPalettes()
	{
		$this->palettes = array();
		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function setPalettes(array $palettes)
	{
		$this->clearPalettes();
		$this->addPalettes($palettes);
		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function addPalettes(array $palettes)
	{
		foreach ($palettes as $palette) {
			$this->addPalette($palette);
		}
		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function addPalette(PaletteInterface $palette)
	{
		$hash = spl_object_hash($palette);
		$this->palettes[$hash] = $palette;
		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function removePalette(PaletteInterface $palette)
	{
		$hash = spl_object_hash($palette);
		unset($this->palettes[$hash]);
		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getPalettes()
	{
		return array_values($this->palettes);
	}

	/**
	 * {@inheritdoc}
	 */
	public function hasPalette(PaletteInterface $palette)
	{
		$hash = spl_object_hash($palette);
		return isset($this->palettes[$hash]);
	}

	/**
	 * {@inheritdoc}
	 */
	public function findPalette(ModelInterface $model = null, PropertyValueBag $input = null)
	{
		$matches = array();

		// determinate the matching count for each palette
		foreach ($this->palettes as $palette)
		{
			$condition = $palette->getCondition();

			if ($condition) {
				$count = $condition->getMatchCount($model, $input);
				$matches[$count][] = $palette;
			}
		}

		// sort by count
		ksort($matches);

		// get palettes with highest matching count
		$palettes = array_pop($matches);

		if (count($palettes) !== 1) {
			throw new DcGeneralInvalidArgumentException();
		}

		return $palettes[0];
	}

	/**
	 * {@inheritdoc}
	 */
	public function hasPaletteByName($paletteName)
	{
		foreach ($this->palettes as $palette) {
			if ($palette->getName() == $paletteName) {
				return true;
			}
		}

		return false;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getPaletteByName($paletteName)
	{
		foreach ($this->palettes as $palette) {
			if ($palette->getName() == $paletteName) {
				return $palette;
			}
		}

		return new DcGeneralInvalidArgumentException('No palette found for name ' . $paletteName);
	}

	/**
	 * {@inheritdoc}
	 */
	public function __clone()
	{
		$palettes = array();
		foreach ($this->palettes as $index => $palette) {
			$palettes[$index] = clone $palette;
		}
		$this->palettes = $palettes;
	}
}
