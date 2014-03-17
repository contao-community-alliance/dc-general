<?php
/**
 * PHP version 5
 * @package    DcGeneral
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Tristan Lins <tristan.lins@bit3.de>
 * @copyright  The Contao Community Alliance.
 * @license    LGPL.
 * @filesource
 */

namespace DcGeneral\Controller;

use DcGeneral\Data\CollectionInterface;
use DcGeneral\Data\ModelInterface;

/**
 * Handy helper class to keep manually sorted lists more manageable.
 *
 * @package DcGeneral\Controller
 */
class SortingManager
{
	/**
	 * The collection containing the models to be inserted.
	 *
	 * @var CollectionInterface
	 */
	protected $models;

	/**
	 * The collection containing the models that are siblings.
	 *
	 * @var CollectionInterface
	 */
	protected $siblings;

	/**
	 * The collection containing the models that are siblings (working copy).
	 *
	 * @var CollectionInterface
	 */
	protected $siblingsCopy;

	/**
	 * The result collection.
	 *
	 * @var CollectionInterface
	 */
	protected $results;

	/**
	 * The model preceding the target position of the first model from the collection.
	 *
	 * @var null|ModelInterface
	 */
	protected $previousModel;

	/**
	 * The property that is used for sorting.
	 *
	 * @var string
	 */
	protected $sortingProperty;

	/**
	 * Temporary marker containing the model currently in scope.
	 *
	 * @var null|ModelInterface
	 */
	protected $marker;

	/**
	 * The current position value.
	 *
	 * @var int
	 */
	protected $position;

	/**
	 * Create a new instance.
	 *
	 * @param CollectionInterface $models        The collection containing the models to be inserted.
	 *
	 * @param CollectionInterface $siblings      The collection containing the models that are siblings.
	 *
	 * @param string              $sortedBy      The property that is used for sorting.
	 *
	 * @param ModelInterface      $previousModel The model preceding the target position of the first model from the
	 *                                           collection.
	 */
	public function __construct(
		CollectionInterface $models = null,
		CollectionInterface $siblings = null,
		$sortedBy = null,
		ModelInterface $previousModel = null
	)
	{
		if ($models)
		{
			$this->setModels($models);
		}

		if ($siblings)
		{
			$this->setSiblings($siblings);
		}

		if ($sortedBy)
		{
			$this->setSortingProperty($sortedBy);
		}

		if ($previousModel)
		{
			$this->setPreviousModel($previousModel);
		}
	}

	/**
	 * Set the collection containing the models to be inserted.
	 *
	 * @param CollectionInterface $models The collection containing the models to be inserted.
	 *
	 * @return SortingManager
	 */
	public function setModels(CollectionInterface $models)
	{
		unset($this->results);
		$this->models = clone $models;

		return $this;
	}

	/**
	 * Get the collection containing the models to be inserted.
	 *
	 * @return CollectionInterface
	 */
	public function getModels()
	{
		return $this->models;
	}

	/**
	 * Set the model preceding the target position of the first model from the collection.
	 *
	 * @param ModelInterface|null $previousModel The model preceding the target position of the first model from the
	 *                                           collection.
	 *
	 * @return SortingManager
	 */
	public function setPreviousModel($previousModel)
	{
		unset($this->results);
		$this->previousModel = $previousModel;

		return $this;
	}

	/**
	 * Get the model preceding the target position of the first model from the collection.
	 *
	 * @return ModelInterface|null
	 */
	public function getPreviousModel()
	{
		return $this->previousModel;
	}

	/**
	 * Get the result collection.
	 *
	 * @return CollectionInterface
	 */
	public function getResults()
	{
		$this->calculate();

		return $this->results;
	}

	/**
	 * Set the collection containing the models that are siblings.
	 *
	 * @param CollectionInterface $siblings The collection containing the models that are siblings.
	 *
	 * @return SortingManager
	 */
	public function setSiblings($siblings)
	{
		unset($this->results);
		$this->siblings = clone $siblings;

		return $this;
	}

	/**
	 * Set the name of the sorting property.
	 *
	 * @param string $sortingProperty The property that is used for sorting.
	 *
	 * @return SortingManager
	 */
	public function setSortingProperty($sortingProperty)
	{
		unset($this->results);
		$this->sortingProperty = $sortingProperty;

		return $this;
	}

	/**
	 * Get the name of the sorting property.
	 *
	 * @return string
	 */
	public function getSortingProperty()
	{
		return $this->sortingProperty;
	}

	/**
	 * Retrieve the ids of the models.
	 *
	 * @return array
	 */
	protected function getModelIds()
	{
		$ids = array();

		foreach ($this->models as $model)
		{
			/** @var ModelInterface $model */
			$ids[] = $model->getId();
		}

		return $ids;
	}

	/**
	 * Scan through the sibling list to the position we want to insert at.
	 *
	 * @return void
	 */
	protected function scanToDesiredPosition()
	{
		// Enforce proper sorting now.
		$this->marker   = null;
		$this->position = 0;
		$ids            = $this->getModelIds();
		// If no previous model, insert at beginning.
		if ($this->previousModel === null)
		{
			if ($this->siblingsCopy->length())
			{
				$this->marker = $this->siblingsCopy->shift();
			}

			return;
		}

		if ($this->siblingsCopy->length())
		{
			// Search for "previous" sibling.
			do
			{
				$this->marker = $this->siblingsCopy->shift();

				if (in_array($this->marker->getId(), $ids))
				{
					continue;
				}

				if ($this->marker)
				{
					$this->position = $this->marker->getProperty($this->getSortingProperty());
				}
			}
			while ($this->marker && $this->marker->getId() !== $this->getPreviousModel()->getId());

			// Remember the "next" sibling.
			if ($this->marker)
			{
				$this->marker = $this->siblingsCopy->shift();
			}
		}
	}

	/**
	 * Calculate the resulting list.
	 *
	 * @return void
	 */
	protected function calculate()
	{
		if (isset($this->results))
		{
			return;
		}

		$ids                = $this->getModelIds();
		$this->results      = clone $this->models;
		$this->siblingsCopy = clone $this->siblings;

		$this->scanToDesiredPosition();

		// If no "next" sibling, simply increment the sorting as we are at the end of the list.
		if (!$this->marker)
		{
			foreach ($this->results as $model)
			{
				$this->position += 128;
				/** @var ModelInterface $model */
				$model->setProperty($this->getSortingProperty(), $this->position);
			}

			return;
		}

		// Determine delta value: ((next sorting - current sorting) / amount of insert models).
		$delta = (($this->marker->getProperty($this->getSortingProperty()) - $this->position) / $this->results->length());
		// If delta too narrow, we need to make room.
		if ($delta < 2)
		{
			$delta = 128;
		}

		// Loop over all models and increment sorting value.
		foreach ($this->results as $model)
		{
			$this->position += $delta;
			/** @var ModelInterface $model */
			$model->setProperty($this->getSortingProperty(), $this->position);
		}

		// When the sorting exceeds the sorting of the "next" sibling, we need to push the remaining siblings to the
		// end of the list.
		if ($this->marker->getProperty($this->getSortingProperty()) <= $this->position)
		{
			do
			{
				if (in_array($this->marker->getId(), $ids))
				{
					$this->marker = $this->siblingsCopy->shift();
					continue;
				}

				$this->position += $delta;
				$this->marker->setProperty($this->getSortingProperty(), $this->position);
				$this->results->push($this->marker);

				$sibling = $this->siblingsCopy->shift();
			}
			while ($sibling);
		}
	}
}
