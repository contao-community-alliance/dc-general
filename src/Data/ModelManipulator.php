<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2019 Contao Community Alliance.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    contao-community-alliance/dc-general
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2013-2019 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Data;

use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\Properties\EmptyValueAwarePropertyInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\Properties\PropertyInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\PropertiesDefinitionInterface;

/**
 * This class provides methods to manipulate a model.
 */
class ModelManipulator
{
    /**
     * Update the model with the values from the value bag.
     *
     * @param PropertiesDefinitionInterface $properties The property information store.
     * @param ModelInterface                $model      The model to update.
     * @param PropertyValueBagInterface     $values     The value bag to retrieve the values from.
     *
     * @return void
     */
    public static function updateModelFromPropertyBag(
        PropertiesDefinitionInterface $properties,
        ModelInterface $model,
        PropertyValueBagInterface $values
    ) {
        foreach ($values as $propertyName => $value) {
            try {
                if (!$properties->hasProperty($propertyName)) {
                    continue;
                }

                $property = $properties->getProperty($propertyName);
                $extra    = $property->getExtra();
                // Don´t save value if isset property readonly.
                if (empty($extra['readonly'])) {
                    $model->setProperty($propertyName, static::sanitizeValue($property, $value));
                }

                if (empty($extra)) {
                    continue;
                }

                // If always save is true, we need to mark the model as changed.
                if (!empty($extra['alwaysSave'])) {
                    // Set property to generate alias or combined values.
                    if (!empty($extra['readonly'])) {
                        $model->setProperty($propertyName, static::sanitizeValue($property, null));
                    }

                    $model->setMeta($model::IS_CHANGED, true);
                }
            } catch (\Exception $exception) {
                $values->markPropertyValueAsInvalid($propertyName, $exception->getMessage());
            }
        }
    }

    /**
     * If value is empty, then override with the empty value stored in property information (if it has any).
     *
     * @param PropertyInterface $property The property information.
     * @param mixed             $value    The value.
     *
     * @return mixed
     */
    public static function sanitizeValue(PropertyInterface $property, $value)
    {
        // If value empty, then override with empty value in property (if it has any).
        if (empty($value)
            && ($property instanceof EmptyValueAwarePropertyInterface)
            && $property->hasEmptyValue()
        ) {
            return $property->getEmptyValue();
        }

        return $value;
    }
}
