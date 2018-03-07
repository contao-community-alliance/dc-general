<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2017 Contao Community Alliance.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    contao-community-alliance/dc-general
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2013-2017 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\View\ActionHandler;

use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetPropertyOptionsEvent;
use ContaoCommunityAlliance\DcGeneral\Data\ModelId;
use ContaoCommunityAlliance\DcGeneral\Data\ModelInterface;
use ContaoCommunityAlliance\DcGeneral\Data\PropertyValueBagInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\ConditionChainInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\ConditionInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\Properties\PropertyInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Condition\Property\PropertyTrueCondition;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\PaletteInterface;

/**
 * This abstract visibility handler provide methods for the visibility of properties.
 */
abstract class AbstractPropertyVisibilityHandler extends AbstractHandler
{
    /**
     * Invisible all unused properties in the edit mask for edit/override all.
     *
     * @return void
     */
    protected function invisibleUnusedProperties()
    {
        $properties     = $this->getEnvironment()->getDataDefinition()->getPropertiesDefinition();
        $editProperties = $this->getPropertiesFromSession();


        foreach ($properties->getPropertyNames() as $propertyName) {
            $property = $properties->getProperty($propertyName);
            if (!$property->getWidgetType() || isset($editProperties[$propertyName])) {
                continue;
            }

            if ($this->excludePaletteSelectorProperty($property)) {
                continue;
            }

            $propertyClass = get_class($property);

            $newProperty = new $propertyClass($propertyName . '.dummy');
            $newProperty->setLabel($property->getLabel());
            $newProperty->setDescription($property->getDescription());
            $newProperty->setDefaultValue($property->getDefaultValue());
            $newProperty->setExcluded($property->isExcluded());
            $newProperty->setSearchable($property->isSearchable());
            $newProperty->setFilterable($property->isFilterable());
            $newProperty->setWidgetType($property->getWidgetType());
            $newProperty->setExplanation($property->getExplanation());
            $newProperty->setExtra($property->getExtra());
            $newProperty->setOptions($property->getOptions());

            $properties->addProperty($newProperty);
            $properties->removeProperty($property);

            $this->makePropertyInvisibleByPalette($property);
        }
    }

    /**
     * Excluded the palette selector property.
     *
     * @param PropertyInterface $property The property.
     *
     * @return bool
     */
    private function excludePaletteSelectorProperty(PropertyInterface $property)
    {
        $palettesDefinition = $this->getEnvironment()->getDataDefinition()->getPalettesDefinition();

        $excludeProperty = false;
        if (1 === count($palettesDefinition->getPalettes())) {
            return $excludeProperty;
        }

        $defaultPalette    = $palettesDefinition->findPalette();
        $defaultProperties = $defaultPalette->getProperties();
        $emptyModel        = $this->getIntersectionModel();

        if (empty($defaultProperties)) {
            return $excludeProperty;
        }

        foreach ($defaultProperties as $defaultProperty) {
            if ($property->getName() !== $defaultProperty->getName()) {
                continue;
            }

            $event = new GetPropertyOptionsEvent($this->getEnvironment(), $emptyModel);
            $event->setPropertyName($property->getName());
            $event->setOptions($property->getOptions());
            $this->getEnvironment()->getEventDispatcher()->dispatch(GetPropertyOptionsEvent::NAME, $event);
            if (0 > count($event->getOptions())) {
                continue;
            }

            $paletteCounter = 0;
            if (null === $event->getOptions()) {
                continue;
            }
            foreach (array_keys($event->getOptions()) as $paletteName) {
                $palettesDefinition->hasPaletteByName($paletteName) ? ++$paletteCounter : null;
            }
            if ($paletteCounter !== count($event->getOptions())) {
                continue;
            }

            $property->setOptions($event->getOptions());
            $excludeProperty = true;
        }

        return $excludeProperty;
    }

    /**
     * Make the property invisible in all legends of each palette.
     *
     * @param DataDefinition\Definition\Properties\PropertyInterface $property The property.
     *
     * @return void
     */
    private function makePropertyInvisibleByPalette(DataDefinition\Definition\Properties\PropertyInterface $property)
    {
        $palettes = $this->getEnvironment()->getDataDefinition()->getPalettesDefinition();

        foreach ($palettes->getPalettes() as $palette) {
            foreach ($palette->getLegends() as $legend) {
                if (!$legend->hasProperty($property->getName())) {
                    continue;
                }

                $visibleCondition = new PropertyTrueCondition('dummyNotVisible');

                $invisibleProperty = $legend->getProperty($property->getName());
                $conditions        = $invisibleProperty->getVisibleCondition();

                $conditions->addCondition($visibleCondition);
            }
        }
    }

    /**
     * Ensure if the property is visible by the model.
     *
     * @param string         $propertyName The property name.
     *
     * @param ModelInterface $model        The model.
     *
     * @return bool
     */
    protected function ensurePropertyVisibleInModel($propertyName, ModelInterface $model)
    {
        $palettesDefinition = $this->getEnvironment()->getDataDefinition()->getPalettesDefinition();
        $propertyValues     = $this->getPropertyValueBagFromModel($model);
        $palette            = $palettesDefinition->findPalette($model, $propertyValues);

        $invisible = false;
        foreach ($palette->getLegends() as $legend) {
            if ($invisible) {
                break;
            }

            foreach ($legend->getProperties($model, $propertyValues) as $property) {
                if ($invisible) {
                    break;
                }

                if ($property->getName() !== $propertyName) {
                    continue;
                }

                $invisible = $property->getVisibleCondition()->match($model, $propertyValues, $property, $legend);
            }
        }

        $findProperty =
            $this->getEnvironment()->getDataDefinition()->getPropertiesDefinition()->getProperty($propertyName);

        return $this->matchVisibilityOfPropertyInAnyPalette($findProperty, $invisible);
    }

    /**
     * Match visibility of property in any palette.
     *
     * @param PropertyInterface $property  The property.
     *
     * @param bool              $invisible The visibility.
     *
     * @return bool
     */
    private function matchVisibilityOfPropertyInAnyPalette(PropertyInterface $property, $invisible)
    {
        $palettesDefinition = $this->getEnvironment()->getDataDefinition()->getPalettesDefinition();
        if (true === $invisible || 1 === count($palettesDefinition->getPalettes())) {
            return $invisible;
        }

        $propertiesDefinition = $this->getEnvironment()->getDataDefinition()->getPropertiesDefinition();
        $defaultPalette       = $palettesDefinition->findPalette();
        $defaultProperties    = $defaultPalette->getProperties();
        $intersectModel       = $this->getIntersectionModel();

        $invisibleProperty = $invisible;
        foreach ($defaultProperties as $defaultProperty) {
            if (!$propertiesDefinition->hasProperty($defaultProperty->getName())) {
                continue;
            }

            $paletteSelectorProperty = $propertiesDefinition->getProperty($defaultProperty->getName());
            if (null === $paletteSelectorProperty->getOptions()) {
                continue;
            }

            $invisibleProperty = $this->matchPaletteProperty($property, $intersectModel, $defaultProperty);

            if (true === $invisibleProperty) {
                continue;
            }

            break;
        }

        return $invisibleProperty;
    }

    /**
     * Invisible palette property.
     *
     * @param PropertyInterface                        $property         The property.
     *
     * @param ModelInterface                           $intersectModel   The intersect model.
     *
     * @param DataDefinition\Palette\PropertyInterface $selectorProperty The palette selector property.
     *
     * @return bool
     */
    private function matchPaletteProperty(
        PropertyInterface $property,
        ModelInterface $intersectModel,
        DataDefinition\Palette\PropertyInterface $selectorProperty
    ) {
        $palettesDefinition   = $this->getEnvironment()->getDataDefinition()->getPalettesDefinition();
        $propertiesDefinition = $this->getEnvironment()->getDataDefinition()->getPropertiesDefinition();

        $invisibleProperty       = false;
        $paletteSelectorProperty = $propertiesDefinition->getProperty($selectorProperty->getName());
        foreach (array_keys($paletteSelectorProperty->getOptions()) as $paletteName) {
            if (!$palettesDefinition->hasPaletteByName($paletteName)) {
                continue;
            }

            $invisibleProperty =
                $this->invisiblePaletteProperty($property, $intersectModel, $selectorProperty, $paletteName);

            if (true === $invisibleProperty) {
                continue;
            }

            break;
        }

        return $invisibleProperty;
    }

    /**
     * Invisible palette property.
     *
     * @param PropertyInterface                        $property         The property.
     *
     * @param ModelInterface                           $intersectModel   The intersect model.
     *
     * @param DataDefinition\Palette\PropertyInterface $selectorProperty The palette selector property.
     *
     * @param string                                   $paletteName      The palette name.
     *
     * @return bool
     */
    private function invisiblePaletteProperty(
        PropertyInterface $property,
        ModelInterface $intersectModel,
        DataDefinition\Palette\PropertyInterface $selectorProperty,
        $paletteName
    ) {
        $palettesDefinition = $this->getEnvironment()->getDataDefinition()->getPalettesDefinition();

        $intersectModel->setProperty($selectorProperty->getName(), $paletteName);
        $searchPalette = $palettesDefinition->findPalette($intersectModel);

        $invisibleProperty = false;
        foreach ($searchPalette->getProperties($intersectModel) as $searchProperty) {
            if ($searchProperty->getName() !== $property->getName()) {
                continue;
            }

            if (false === $searchProperty->getVisibleCondition()->match($intersectModel)) {
                continue;
            }

            $invisibleProperty = true;
            break;
        }

        return $invisibleProperty;
    }

    /**
     * Inject select parent property information,
     * if select an sub selector and their parent property don´t select for edit.
     *
     * @param DataDefinition\Definition\Properties\PropertyInterface $property The property.
     *
     * @param ModelInterface                                         $model    The model.
     *
     * @return null|string
     */
    protected function injectSelectParentPropertyInformation(
        DataDefinition\Definition\Properties\PropertyInterface $property,
        ModelInterface $model
    ) {
        $translator         = $this->getEnvironment()->getTranslator();
        $palettesDefinition = $this->getEnvironment()->getDataDefinition()->getPalettesDefinition();

        $palette = $palettesDefinition->findPalette($model);

        $invisibleProperties = array();
        foreach ($palette->getLegends() as $legend) {
            if (!$legend->hasProperty($property->getName())) {
                continue;
            }

            foreach ($legend->getProperties() as $legendProperty) {
                if ($property->getName() !== $legendProperty->getName()) {
                    continue;
                }

                $this->matchInvisibleProperty($legendProperty->getVisibleCondition(), $invisibleProperties);
            }
        }

        $this->findInvisiblePaletteSelectorProperty($model, $invisibleProperties);

        if (empty($invisibleProperties)) {
            return null;
        }

        $information = array();
        foreach ($invisibleProperties as $propertyName => $informationProperty) {
            $labelParentProperty = !$informationProperty->getLabel() ? $propertyName : $informationProperty->getLabel();
            $labelEditProperty   = !$property->getLabel() ? $property->getName() : $property->getLabel();

            $information[] = sprintf(
                '<p class="tl_new">' . $translator->translate('MSC.select_parent_property_info') . '</p>',
                $labelParentProperty,
                $labelEditProperty
            );
        }

        return implode('', $information);
    }

    /**
     * Inject select sub properties information,
     * if select an sub selector and their properties don´t select for edit.
     *
     * @param DataDefinition\Definition\Properties\PropertyInterface $property         The property.
     *
     * @param ModelInterface                                         $model            The model.
     *
     * @param PropertyValueBagInterface                              $propertyValueBag The property values.
     *
     * @return null|string
     */
    protected function injectSelectSubPropertiesInformation(
        DataDefinition\Definition\Properties\PropertyInterface $property,
        ModelInterface $model,
        PropertyValueBagInterface $propertyValueBag
    ) {
        $translator = $this->getEnvironment()->getTranslator();

        $properties = $this->matchInvisibleSubProperties($model, $property, $propertyValueBag);
        if (empty($properties)) {
            return null;
        }

        $information = array();
        foreach ($properties as $propertyName => $informationProperty) {
            $label = !$informationProperty->getLabel() ? $propertyName : $informationProperty->getLabel();

            $information[] = sprintf(
                '<p class="tl_new">' . $translator->translate('MSC.select_property_info') . '</p>',
                $label
            );
        }

        return implode('', $information);
    }

    /**
     * Find the invisible palette selector property.
     *
     * @param ModelInterface $model               The model.
     *
     * @param array          $invisibleProperties The invisible properties.
     *
     * @return void
     */
    private function findInvisiblePaletteSelectorProperty(ModelInterface $model, array &$invisibleProperties)
    {
        $palettesDefinition = $this->getEnvironment()->getDataDefinition()->getPalettesDefinition();

        if (!empty($invisibleProperties) || 1 > count($palettesDefinition->getPalettes())) {
            return;
        }

        $propertiesDefinition = $this->getEnvironment()->getDataDefinition()->getPropertiesDefinition();

        $session = $this->getSession();

        $palette = $palettesDefinition->findPalette($model);
        foreach ($palette->getProperties() as $paletteProperty) {
            if (!array_key_exists($paletteProperty->getName(), $session['intersectValues'])) {
                continue;
            }

            $paletteName = $session['intersectValues'][$paletteProperty->getName()];
            if (!$palettesDefinition->hasPaletteByName($paletteName)) {
                continue;
            }

            $invisibleProperties[$paletteProperty->getName()]
                = $propertiesDefinition->getProperty($paletteProperty->getName() . '.dummy');

            break;
        }
    }

    /**
     * Match the invisible property.
     *
     * @param ConditionInterface $visibleCondition    The visible condition.
     *
     * @param array              $invisibleProperties The invisible properties.
     *
     * @return void
     */
    private function matchInvisibleProperty(ConditionInterface $visibleCondition, array &$invisibleProperties)
    {
        $propertiesDefinition = $this->getEnvironment()->getDataDefinition()->getPropertiesDefinition();

        foreach ($visibleCondition->getConditions() as $condition) {
            if ($condition instanceof ConditionChainInterface) {
                $this->matchInvisibleProperty($condition, $invisibleProperties);
            }

            if (!method_exists($condition, 'getPropertyName')) {
                continue;
            }

            if (isset($invisibleProperties[$condition->getPropertyName()])
                || !$propertiesDefinition->hasProperty($condition->getPropertyName() . '.dummy')
            ) {
                continue;
            }

            $invisibleProperties[$condition->getPropertyName()]
                = $propertiesDefinition->getProperty($condition->getPropertyName() . '.dummy');
        }
    }

    /**
     * Match invisible sub properties.
     *
     * @param ModelInterface                                         $model            The model.
     *
     * @param DataDefinition\Definition\Properties\PropertyInterface $property         The property.
     *
     * @param PropertyValueBagInterface                              $propertyValueBag The property values.
     *
     * @return array
     */
    private function matchInvisibleSubProperties(
        ModelInterface $model,
        DataDefinition\Definition\Properties\PropertyInterface $property,
        PropertyValueBagInterface $propertyValueBag
    ) {
        $palettesDefinition = $this->getEnvironment()->getDataDefinition()->getPalettesDefinition();

        $testPropertyValueBag = clone $propertyValueBag;
        $testPropertyValueBag->setPropertyValue('dummyNotVisible', true);

        $palette = $palettesDefinition->findPalette($model);

        $properties = array();
        foreach ($palette->getLegends() as $legend) {
            if (!$legend->hasProperty($property->getName())) {
                continue;
            }

            $legendProperties = (array) $legend->getProperties($model, $testPropertyValueBag);
            if (empty($legendProperties)) {
                continue;
            }

            foreach ($legendProperties as $legendProperty) {
                if ($property->getName() === $legendProperty->getName()) {
                    continue;
                }

                $conditions = $legendProperty->getVisibleCondition();

                if (!$conditions->match($model, $testPropertyValueBag, $legendProperty, $legend)) {
                    continue;
                }

                $this->matchParentInvisibleProperty($conditions, $property, $legendProperty, $properties);
            }
        }

        return $properties;
    }

    /**
     * Match the parent invisible property.
     *
     * @param ConditionInterface                                     $visibleCondition    The visible condition.
     *
     * @param DataDefinition\Definition\Properties\PropertyInterface $property            The property.
     *
     * @param DataDefinition\Palette\PropertyInterface               $legendProperty      The legend property.
     *
     * @param array                                                  $invisibleProperties The invisible properties.
     *
     * @return void
     */
    private function matchParentInvisibleProperty(
        ConditionInterface $visibleCondition,
        DataDefinition\Definition\Properties\PropertyInterface $property,
        DataDefinition\Palette\PropertyInterface $legendProperty,
        array &$invisibleProperties
    ) {
        $propertiesDefinition = $this->getEnvironment()->getDataDefinition()->getPropertiesDefinition();

        foreach ($visibleCondition->getConditions() as $condition) {
            if ($condition instanceof ConditionChainInterface) {
                $this->matchParentInvisibleProperty($condition, $property, $legendProperty, $invisibleProperties);
            }

            if (!method_exists($condition, 'getPropertyName')
                || ($property->getName() !== $condition->getPropertyName())
            ) {
                continue;
            }

            if (isset($invisibleProperties[$legendProperty->getName()])
                || !$propertiesDefinition->hasProperty($legendProperty->getName() . '.dummy')
            ) {
                continue;
            }


            $invisibleProperties[$legendProperty->getName()]
                = $propertiesDefinition->getProperty($legendProperty->getName() . '.dummy');
        }
    }

    /**
     * Get the intersection Model.
     * If all select models has the same value by their property, then is the value set it.
     *
     * @return ModelInterface
     */
    protected function getIntersectionModel()
    {
        $inputProvider        = $this->getEnvironment()->getInputProvider();
        $dataProvider         = $this->getEnvironment()->getDataProvider();
        $dataDefinition       = $this->getEnvironment()->getDataDefinition();
        $propertiesDefinition = $dataDefinition->getPropertiesDefinition();
        $session              = $this->getSession();

        $intersectModel = $dataProvider->getEmptyModel();

        $defaultPalette      = null;
        $legendPropertyNames = $this->getLegendPropertyNames($intersectModel, $defaultPalette);

        $idProperty = method_exists($dataProvider, 'getIdProperty') ? $dataProvider->getIdProperty() : 'id';
        foreach ($session['intersectValues'] as $intersectProperty => $intersectValue) {
            if (($idProperty === $intersectProperty)
                || !$propertiesDefinition->hasProperty($intersectProperty)
                || (false === $this->useIntersectValue($intersectProperty, $legendPropertyNames, $defaultPalette))
            ) {
                continue;
            }

            if ($inputProvider->hasValue($intersectProperty)) {
                $intersectModel->setProperty($intersectProperty, $inputProvider->getValue($intersectProperty));

                continue;
            }

            $intersectModel->setProperty($intersectProperty, $intersectValue);
        }

        $this->intersectModelSetPrimaryId($intersectModel, $idProperty);
        $this->intersectModelSetParentId($intersectModel);

        return $intersectModel;
    }

    /**
     * Use intersect value.
     *
     * @param string                $intersectPropertyName The intersect property name.
     *
     * @param array                 $legendPropertyNames   The legend names.
     *
     * @param PaletteInterface|null $defaultPalette        The default palette.
     *
     * @return bool
     */
    private function useIntersectValue(
        $intersectPropertyName,
        array $legendPropertyNames,
        PaletteInterface $defaultPalette = null
    ) {
        $propertiesDefinition = $this->getEnvironment()->getDataDefinition()->getPropertiesDefinition();
        $useIntersectValue    = (bool) $defaultPalette;

        if ($defaultPalette && !$propertiesDefinition->getProperty($intersectPropertyName)->getWidgetType()
        ) {
            $useIntersectValue = true;
        }

        if ($defaultPalette
            && (false === $useIntersectValue)
            && in_array($intersectPropertyName, $legendPropertyNames)
        ) {
            $useIntersectValue = true;
        }

        return $useIntersectValue;
    }

    /**
     * Set the primaray id to the model from intersect values.
     *
     * @param ModelInterface $intersectModel The intersect model.
     *
     * @param string         $idProperty     The id property.
     *
     * @return void
     */
    private function intersectModelSetPrimaryId($intersectModel, $idProperty)
    {
        if (null !== $intersectModel->getId()) {
            return;
        }

        $session = $this->getSession();

        $intersectModel->setId($session['intersectValues'][$idProperty]);
        $intersectModel->setProperty($idProperty, $session['intersectValues'][$idProperty]);
    }

    /**
     * Set the parent id to the inersect model.
     *
     * @param ModelInterface $intersectModel The intersect model.
     *
     * @return void
     */
    private function intersectModelSetParentId(ModelInterface $intersectModel)
    {
        $dataDefinition       = $this->getEnvironment()->getDataDefinition();
        $parentDataDefinition = $this->getEnvironment()->getParentDataDefinition();

        if (null === $parentDataDefinition) {
            return;
        }

        $relationships  = $dataDefinition->getModelRelationshipDefinition();
        $childCondition =
            $relationships->getChildCondition($parentDataDefinition->getName(), $dataDefinition->getName());

        $parentField = null;
        foreach ($childCondition->getSetters() as $setter) {
            if (!array_key_exists('to_field', $setter)) {
                continue;
            }

            $parentField = $setter['to_field'];
            break;
        }

        if (null !== $parentField) {
            $intersectModel->setProperty(
                $parentField,
                ModelId::fromSerialized($this->getEnvironment()->getInputProvider()->getParameter('pid'))
                    ->getId()
            );
        }
    }

    /**
     * Get legend property names.
     *
     * @param ModelInterface        $intersectModel The intersect model.
     *
     * @param PaletteInterface|null $defaultPalette The default palette.
     *
     * @return array
     */
    private function getLegendPropertyNames(ModelInterface $intersectModel, PaletteInterface &$defaultPalette = null)
    {
        $inputProvider      = $this->getEnvironment()->getInputProvider();
        $palettesDefinition = $this->getEnvironment()->getDataDefinition()->getPalettesDefinition();

        $legendPropertyNames = array();
        if (1 === count($palettesDefinition->getPalettes()) && $inputProvider->hasValue('FORM_INPUTS')) {
            return $legendPropertyNames;
        }

        $defaultPalette = $palettesDefinition->findPalette($intersectModel);
        foreach ($defaultPalette->getProperties($intersectModel) as $paletteProperty) {
            $legendPropertyNames[] = $paletteProperty->getName();
        }

        return $legendPropertyNames;
    }

    /**
     * Return the session contains the data definition and the override/edit mode.
     *
     * @return array
     */
    abstract protected function getSession();

    /**
     * Return select properties from the session.
     *
     * @return array
     */
    abstract protected function getPropertiesFromSession();
}
