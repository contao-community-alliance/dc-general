<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2022 Contao Community Alliance.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    contao-community-alliance/dc-general
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2013-2022 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Data;

use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\Properties\PropertyInterface;

/**
 * This class is the base implementation for EditInformationInterface.
 */
class DefaultEditInformation implements EditInformationInterface
{
    /**
     * The edit models.
     *
     * @var array
     */
    protected $models = [];

    /**
     * The model errors.
     *
     * @var array
     */
    protected $modelErrors = [];

    /**
     * The uniform time.
     *
     * @var integer
     */
    protected $uniformTime;

    /**
     * DefaultEditInformation constructor.
     */
    public function __construct()
    {
        $this->uniformTime = time();
    }

    /**
     * {@inheritDoc}
     */
    public function hasAnyModelError()
    {
        return !empty($this->modelErrors);
    }

    /**
     * {@inheritDoc}
     */
    public function getModelError(ModelInterface $model)
    {
        $modelId = ModelId::fromModel($model);

        if (!isset($this->modelErrors[$modelId->getSerialized()])) {
            return null;
        }

        return $this->modelErrors[$modelId->getSerialized()];
    }

    /**
     * {@inheritDoc}
     */
    public function setModelError(ModelInterface $model, array $error, PropertyInterface $property)
    {
        $modelId = ModelId::fromModel($model);

        if (!isset($this->models[$modelId->getSerialized()])) {
            $this->models[$modelId->getSerialized()] = $model;
        }

        if (!isset($this->modelErrors[$modelId->getSerialized()])) {
            $this->modelErrors[$modelId->getSerialized()] = [];
        }

        if (isset($this->modelErrors[$modelId->getSerialized()][$property->getName()])) {
            $this->modelErrors[$modelId->getSerialized()][$property->getName()] = \array_merge(
                (array) $this->modelErrors[$modelId->getSerialized()][$property->getName()],
                $error
            );
        } else {
            $this->modelErrors[$modelId->getSerialized()][$property->getName()] = $error;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function uniformTime()
    {
        return $this->uniformTime;
    }

    /**
     * Get flat model errors. This returns all errors without property names hierarchy.
     *
     * @param ModelInterface $model The model.
     *
     * @return array|null
     */
    public function getFlatModelErrors(ModelInterface $model)
    {
        $modelErrors = $this->getModelError($model);
        if (!$modelErrors) {
            return $modelErrors;
        }

        $errors = [[]];
        foreach ($this->getModelError($model) as $modelError) {
            $errors[] = $modelError;
        }

        return \array_merge(...$errors);
    }
}
