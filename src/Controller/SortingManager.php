<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2024 Contao Community Alliance.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    contao-community-alliance/dc-general
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2013-2024 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Controller;

use ContaoCommunityAlliance\DcGeneral\Data\CollectionInterface;
use ContaoCommunityAlliance\DcGeneral\Data\ModelInterface;

/**
 * Handy helper class to keep manually sorted lists more manageable.
 */
class SortingManager
{
    /**
     * The collection containing the models to be inserted.
     *
     * @var CollectionInterface|null
     */
    protected $models;

    /**
     * The collection containing the models that are siblings.
     *
     * @var CollectionInterface|null
     */
    protected $siblings = null;

    /**
     * The collection containing the models that are siblings (working copy).
     *
     * @var CollectionInterface|null
     */
    protected $siblingsCopy;

    /**
     * The result collection.
     *
     * @var CollectionInterface|null
     */
    protected $results;

    /**
     * The model preceding the target position of the first model from the collection.
     *
     * @var ModelInterface|null
     */
    protected $previousModel;

    /**
     * The property that is used for sorting.
     *
     * @var string
     */
    protected $sortingProperty = '';

    /**
     * Temporary marker containing the model currently in scope.
     *
     * @var ModelInterface|null
     */
    protected $marker;

    /**
     * The current position value.
     *
     * @var int
     */
    protected $position = 0;

    /**
     * Create a new instance.
     *
     * @param CollectionInterface|null $models        The collection containing the models to be inserted.
     * @param CollectionInterface|null $siblings      The collection containing the models that are siblings.
     * @param string|null              $sortedBy      The property that is used for sorting.
     * @param ModelInterface|null      $previousModel The model preceding the target position of the first model from
     *                                                the collection.
     */
    public function __construct(
        CollectionInterface $models = null,
        CollectionInterface $siblings = null,
        string $sortedBy = null,
        ModelInterface $previousModel = null
    ) {
        if ($models) {
            $this->setModels($models);
        }

        if ($siblings) {
            $this->setSiblings($siblings);
        }

        if (null !== $sortedBy) {
            $this->setSortingProperty($sortedBy);
        }

        if ($previousModel) {
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
     * @return CollectionInterface|null
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
     * @return CollectionInterface|null
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
        if (null === $this->models) {
            return [];
        }

        $ids = [];
        foreach ($this->models as $model) {
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

        $siblingsCopy = $this->siblingsCopy;
        assert($siblingsCopy instanceof CollectionInterface);

        // If no previous model, insert at beginning.
        if (null === $this->previousModel) {
            if ($siblingsCopy->length()) {
                $this->marker = $siblingsCopy->shift();
            }

            return;
        }

        $previousModel = $this->getPreviousModel();
        assert($previousModel instanceof ModelInterface);

        if ($siblingsCopy->length()) {
            // Search for "previous" sibling.
            do {
                $this->marker = $siblingsCopy->shift();
                if ($this->marker) {
                    if (\in_array($this->marker->getId(), $ids, true)) {
                        continue;
                    }
                    $this->position = $this->marker->getProperty($this->getSortingProperty());
                }
            } while ($this->marker && $this->marker->getId() !== $previousModel->getId());

            // Remember the "next" sibling.
            if ($this->marker) {
                $this->marker = $siblingsCopy->shift();
            }
        }
    }

    /**
     * Determine delta value.
     *
     * Delta value will be between 2 and a multiple 128 which is large enough to contain all models being moved.
     *
     * @return float|int
     */
    private function determineDelta()
    {
        $marker = $this->marker;
        assert($marker instanceof ModelInterface);

        $results = $this->results;
        assert($results instanceof CollectionInterface);

        $delta = (
            ($marker->getProperty($this->getSortingProperty()) - $this->position) / $results->length()
        );

        // If delta too narrow, we need to make room.
        // Prevent delta to exceed, also. Use minimum delta which is calculated as multiple of 128.
        if (($delta < 2) || ($delta > 128)) {
            return (\ceil($results->length() / 128) * 128);
        }

        return $delta;
    }

    /**
     * Update the sorting property values of all models.
     *
     * @return void
     */
    private function updateSorting()
    {
        // Called from calculate() only when siblings exist.
        assert($this->results instanceof CollectionInterface);
        $ids = $this->getModelIds();
        // If no "next" sibling, simply increment the sorting as we are at the end of the list.
        if (!$this->marker) {
            foreach ($this->results as $model) {
                $this->position += 128;
                /** @var ModelInterface $model */
                $model->setProperty($this->getSortingProperty(), $this->position);
            }

            return;
        }

        $delta = $this->determineDelta();

        // Loop over all models and increment sorting value.
        foreach ($this->results as $model) {
            $this->position += (int) $delta;
            /** @var ModelInterface $model */
            $model->setProperty($this->getSortingProperty(), $this->position);
        }

        // When the sorting exceeds the sorting of the "next" sibling, we need to push the remaining siblings to the
        // end of the list.
        if ($this->marker->getProperty($this->getSortingProperty()) <= $this->position) {
            do {
                $siblingsCopy = $this->siblingsCopy;
                assert($siblingsCopy instanceof CollectionInterface);

                // Skip models about to be pasted.
                if (\in_array($this->marker->getId(), $ids)) {
                    $this->marker = $siblingsCopy->shift();
                    continue;
                }

                $this->position += (int) $delta;
                $this->marker->setProperty($this->getSortingProperty(), $this->position);
                $this->results->push($this->marker);

                $this->marker = $siblingsCopy->shift();
            } while ($this->marker);
        }
    }

    /**
     * Calculate the resulting list.
     *
     * @return void
     *
     * @throws \RuntimeException When no sorting property has been defined.
     */
    protected function calculate()
    {
        $models = $this->models;
        assert($models instanceof CollectionInterface);

        if (isset($this->results) || (0 === $models->length())) {
            return;
        }

        if (!$this->getSortingProperty()) {
            $firstModel = $models->get(0);
            assert($firstModel instanceof ModelInterface);

            throw new \RuntimeException('No sorting property defined for ' . $firstModel->getProviderName());
        }

        $this->results = clone $models;
        $this->siblingsCopy = $this->siblings ? clone $this->siblings : null;

        $this->scanToDesiredPosition();
        $this->updateSorting();
    }
}
