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
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2013-2019 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\View\ActionHandler;

use ContaoCommunityAlliance\DcGeneral\Action;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetPropertyOptionsEvent;
use ContaoCommunityAlliance\DcGeneral\Data\ModelId;
use ContaoCommunityAlliance\DcGeneral\Data\ModelInterface;
use ContaoCommunityAlliance\DcGeneral\Data\PropertyValueBagInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\ConditionChainInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\ConditionInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\PalettesDefinitionInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\Properties\PropertyInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Condition\Property\PropertyTrueCondition;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\PaletteInterface;
use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;

/**
 * This abstract visibility handler provide methods for the visibility of properties.
 *
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
abstract class AbstractPropertyVisibilityHandler
{
    /**
     * Invisible all unused properties in the edit mask for edit/override all.
     *
     * @param Action               $action      The action.
     * @param EnvironmentInterface $environment The environment.
     *
     * @return void
     */
    protected function invisibleUnusedProperties(Action $action, EnvironmentInterface $environment)
    {
        $properties     = $environment->getDataDefinition()->getPropertiesDefinition();
        $editProperties = $this->getPropertiesFromSession($action, $environment);


        foreach ($properties->getPropertyNames() as $propertyName) {
            $property = $properties->getProperty($propertyName);
            if (isset($editProperties[$propertyName]) || !$property->getWidgetType()) {
                continue;
            }

            if ($this->excludePaletteSelectorProperty($action, $property, $environment)) {
                continue;
            }

            $propertyClass = \get_class($property);

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

            $this->makePropertyInvisibleByPalette($property, $environment);
        }
    }

    /**
     * Excluded the palette selector property.
     *
     * @param Action               $action      The action.
     * @param PropertyInterface    $property    The property.
     * @param EnvironmentInterface $environment The environment.
     *
     * @return bool
     */
    private function excludePaletteSelectorProperty(
        Action $action,
        PropertyInterface $property,
        EnvironmentInterface $environment
    ) {
        $palettesDefinition = $environment->getDataDefinition()->getPalettesDefinition();

        if (\count($palettesDefinition->getPalettes()) === 1) {
            return false;
        }

        return $this->analyzeExcludeProperty($action, $environment, $property, $palettesDefinition);
    }

    /**
     * Analyze property for exclude.
     *
     * @param Action                      $action             The action.
     * @param EnvironmentInterface        $environment        The environment.
     * @param PropertyInterface           $property           The prooperty.
     * @param PalettesDefinitionInterface $palettesDefinition The palettes definition.
     *
     * @return bool
     */
    private function analyzeExcludeProperty(
        Action $action,
        EnvironmentInterface $environment,
        PropertyInterface $property,
        PalettesDefinitionInterface $palettesDefinition
    ) {
        $defaultPalette    = $palettesDefinition->findPalette();
        $defaultProperties = $defaultPalette->getProperties();

        if (empty($defaultProperties)) {
            return false;
        }

        $emptyModel = $this->getIntersectionModel($action, $environment);

        $excludeProperty = false;
        foreach ($defaultProperties as $defaultProperty) {
            if ($property->getName() !== $defaultProperty->getName()) {
                continue;
            }

            $event = new GetPropertyOptionsEvent($environment, $emptyModel);
            $event->setPropertyName($property->getName());
            $event->setOptions($property->getOptions());
            $environment->getEventDispatcher()->dispatch(GetPropertyOptionsEvent::NAME, $event);
            if (($event->getOptions() === null) || (0 > \count($event->getOptions()))) {
                continue;
            }

            $paletteCounter = 0;
            foreach (\array_keys($event->getOptions()) as $paletteName) {
                $palettesDefinition->hasPaletteByName($paletteName) ? ++$paletteCounter : null;
            }
            if ($paletteCounter !== \count($event->getOptions())) {
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
     * @param DataDefinition\Definition\Properties\PropertyInterface $property    The property.
     * @param EnvironmentInterface                                   $environment The environment.
     *
     * @return void
     */
    private function makePropertyInvisibleByPalette(
        DataDefinition\Definition\Properties\PropertyInterface $property,
        EnvironmentInterface $environment
    ) {
        $palettes = $environment->getDataDefinition()->getPalettesDefinition();

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
     * @param Action               $action       The action.
     * @param string               $propertyName The property name.
     * @param ModelInterface       $model        The model.
     * @param EnvironmentInterface $environment  The environment.
     *
     * @return bool
     */
    protected function ensurePropertyVisibleInModel(
        Action $action,
        $propertyName,
        ModelInterface $model,
        EnvironmentInterface $environment
    ) {
        $palettesDefinition = $environment->getDataDefinition()->getPalettesDefinition();
        $propertyValues     = $this->getPropertyValueBagFromModel($action, $model, $environment);
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
            $environment->getDataDefinition()->getPropertiesDefinition()->getProperty($propertyName);

        return $this->matchVisibilityOfPropertyInAnyPalette($action, $findProperty, $invisible, $environment);
    }

    /**
     * Match visibility of property in any palette.
     *
     * @param Action               $action      The action.
     * @param PropertyInterface    $property    The property.
     * @param bool                 $invisible   The visibility.
     * @param EnvironmentInterface $environment The environment.
     *
     * @return bool
     */
    private function matchVisibilityOfPropertyInAnyPalette(
        Action $action,
        PropertyInterface $property,
        $invisible,
        EnvironmentInterface $environment
    ) {
        $palettesDefinition = $environment->getDataDefinition()->getPalettesDefinition();
        if (true === $invisible || 1 === \count($palettesDefinition->getPalettes())) {
            return $invisible;
        }

        $propertiesDefinition = $environment->getDataDefinition()->getPropertiesDefinition();
        $defaultPalette       = $palettesDefinition->findPalette();
        $defaultProperties    = $defaultPalette->getProperties();
        $intersectModel       = $this->getIntersectionModel($action, $environment);

        $invisibleProperty = $invisible;
        foreach ($defaultProperties as $defaultProperty) {
            if (!$propertiesDefinition->hasProperty($defaultProperty->getName())) {
                continue;
            }

            $paletteSelectorProperty = $propertiesDefinition->getProperty($defaultProperty->getName());
            if ($paletteSelectorProperty->getOptions() === null) {
                continue;
            }

            $invisibleProperty =
                $this->matchPaletteProperty($property, $intersectModel, $defaultProperty, $environment);

            if ($invisibleProperty === true) {
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
     * @param ModelInterface                           $intersectModel   The intersect model.
     * @param DataDefinition\Palette\PropertyInterface $selectorProperty The palette selector property.
     * @param EnvironmentInterface                     $environment      The environment.
     *
     * @return bool
     */
    private function matchPaletteProperty(
        PropertyInterface $property,
        ModelInterface $intersectModel,
        DataDefinition\Palette\PropertyInterface $selectorProperty,
        EnvironmentInterface $environment
    ) {
        $palettesDefinition   = $environment->getDataDefinition()->getPalettesDefinition();
        $propertiesDefinition = $environment->getDataDefinition()->getPropertiesDefinition();

        $invisibleProperty       = false;
        $paletteSelectorProperty = $propertiesDefinition->getProperty($selectorProperty->getName());
        foreach (\array_keys($paletteSelectorProperty->getOptions()) as $paletteName) {
            if (!$palettesDefinition->hasPaletteByName($paletteName)) {
                continue;
            }

            $invisibleProperty = $this->invisiblePaletteProperty(
                $property,
                $intersectModel,
                $selectorProperty,
                $paletteName,
                $environment
            );

            if ($invisibleProperty === true) {
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
     * @param ModelInterface                           $intersectModel   The intersect model.
     * @param DataDefinition\Palette\PropertyInterface $selectorProperty The palette selector property.
     * @param string                                   $paletteName      The palette name.
     * @param EnvironmentInterface                     $environment      The environment.
     *
     * @return bool
     */
    private function invisiblePaletteProperty(
        PropertyInterface $property,
        ModelInterface $intersectModel,
        DataDefinition\Palette\PropertyInterface $selectorProperty,
        $paletteName,
        EnvironmentInterface $environment
    ) {
        $palettesDefinition = $environment->getDataDefinition()->getPalettesDefinition();

        $intersectModel->setProperty($selectorProperty->getName(), $paletteName);
        $searchPalette = $palettesDefinition->findPalette($intersectModel);

        $invisibleProperty = false;
        foreach ($searchPalette->getProperties($intersectModel) as $searchProperty) {
            if ($searchProperty->getName() !== $property->getName()) {
                continue;
            }

            if ($searchProperty->getVisibleCondition()->match($intersectModel) === false) {
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
     * @param Action                                                 $action      The action.
     * @param DataDefinition\Definition\Properties\PropertyInterface $property    The property.
     * @param ModelInterface                                         $model       The model.
     * @param EnvironmentInterface                                   $environment The environment.
     *
     * @return null|string
     */
    protected function injectSelectParentPropertyInformation(
        Action $action,
        DataDefinition\Definition\Properties\PropertyInterface $property,
        ModelInterface $model,
        EnvironmentInterface $environment
    ) {
        $translator         = $environment->getTranslator();
        $palettesDefinition = $environment->getDataDefinition()->getPalettesDefinition();

        $palette = $palettesDefinition->findPalette($model);

        $invisibleProperties = [];
        foreach ($palette->getLegends() as $legend) {
            if (!$legend->hasProperty($property->getName())) {
                continue;
            }

            foreach ($legend->getProperties() as $legendProperty) {
                if ($property->getName() !== $legendProperty->getName()) {
                    continue;
                }

                $this->matchInvisibleProperty(
                    $legendProperty->getVisibleCondition(),
                    $invisibleProperties,
                    $environment
                );
            }
        }

        $this->findInvisiblePaletteSelectorProperty($action, $model, $invisibleProperties, $environment);

        if (empty($invisibleProperties)) {
            return null;
        }

        $information = [];
        foreach ($invisibleProperties as $propertyName => $informationProperty) {
            $labelParentProperty = !$informationProperty->getLabel() ? $propertyName : $informationProperty->getLabel();
            $labelEditProperty   = !$property->getLabel() ? $property->getName() : $property->getLabel();

            $information[] = \sprintf(
                '<p class="tl_new">' . $translator->translate('MSC.select_parent_property_info') . '</p>',
                $labelParentProperty,
                $labelEditProperty
            );
        }

        return \implode('', $information);
    }

    /**
     * Inject select sub properties information,
     * if select an sub selector and their properties don´t select for edit.
     *
     * @param DataDefinition\Definition\Properties\PropertyInterface $property         The property.
     * @param ModelInterface                                         $model            The model.
     * @param PropertyValueBagInterface                              $propertyValueBag The property values.
     * @param EnvironmentInterface                                   $environment      The environment.
     *
     * @return null|string
     */
    protected function injectSelectSubPropertiesInformation(
        DataDefinition\Definition\Properties\PropertyInterface $property,
        ModelInterface $model,
        PropertyValueBagInterface $propertyValueBag,
        EnvironmentInterface $environment
    ) {
        $translator = $environment->getTranslator();

        $properties = $this->matchInvisibleSubProperties($model, $property, $propertyValueBag, $environment);
        if (empty($properties)) {
            return null;
        }

        $information = [];
        foreach ($properties as $propertyName => $informationProperty) {
            $label = !$informationProperty->getLabel() ? $propertyName : $informationProperty->getLabel();

            $information[] = \sprintf(
                '<p class="tl_new">' . $translator->translate('MSC.select_property_info') . '</p>',
                $label
            );
        }

        return \implode('', $information);
    }

    /**
     * Find the invisible palette selector property.
     *
     * @param Action               $action              The action.
     * @param ModelInterface       $model               The model.
     * @param array                $invisibleProperties The invisible properties.
     * @param EnvironmentInterface $environment         The environment.
     *
     * @return void
     */
    private function findInvisiblePaletteSelectorProperty(
        Action $action,
        ModelInterface $model,
        array &$invisibleProperties,
        EnvironmentInterface $environment
    ) {
        $palettesDefinition = $environment->getDataDefinition()->getPalettesDefinition();

        if (!empty($invisibleProperties) || 1 > \count($palettesDefinition->getPalettes())) {
            return;
        }

        $propertiesDefinition = $environment->getDataDefinition()->getPropertiesDefinition();

        $session = $this->getSession($action, $environment);

        $palette = $palettesDefinition->findPalette($model);
        foreach ($palette->getProperties() as $paletteProperty) {
            if (!\array_key_exists($paletteProperty->getName(), $session['intersectValues'])) {
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
     * @param ConditionInterface   $visibleCondition    The visible condition.
     * @param array                $invisibleProperties The invisible properties.
     * @param EnvironmentInterface $environment         The environment.
     *
     * @return void
     */
    private function matchInvisibleProperty(
        ConditionInterface $visibleCondition,
        array &$invisibleProperties,
        EnvironmentInterface $environment
    ) {
        $propertiesDefinition = $environment->getDataDefinition()->getPropertiesDefinition();

        foreach ($visibleCondition->getConditions() as $condition) {
            if ($condition instanceof ConditionChainInterface) {
                $this->matchInvisibleProperty($condition, $invisibleProperties, $environment);
            }

            if (!\method_exists($condition, 'getPropertyName')) {
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
     * @param DataDefinition\Definition\Properties\PropertyInterface $property         The property.
     * @param PropertyValueBagInterface                              $propertyValueBag The property values.
     * @param EnvironmentInterface                                   $environment      The environment.
     *
     * @return array
     */
    private function matchInvisibleSubProperties(
        ModelInterface $model,
        DataDefinition\Definition\Properties\PropertyInterface $property,
        PropertyValueBagInterface $propertyValueBag,
        EnvironmentInterface $environment
    ) {
        $palettesDefinition = $environment->getDataDefinition()->getPalettesDefinition();

        $testPropertyValueBag = clone $propertyValueBag;
        $testPropertyValueBag->setPropertyValue('dummyNotVisible', true);

        $palette = $palettesDefinition->findPalette($model);

        $properties = [];
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

                $this->matchParentInvisibleProperty($conditions, $property, $legendProperty, $properties, $environment);
            }
        }

        return $properties;
    }

    /**
     * Match the parent invisible property.
     *
     * @param ConditionInterface                                     $visibleCondition    The visible condition.
     * @param DataDefinition\Definition\Properties\PropertyInterface $property            The property.
     * @param DataDefinition\Palette\PropertyInterface               $legendProperty      The legend property.
     * @param array                                                  $invisibleProperties The invisible properties.
     * @param EnvironmentInterface                                   $environment         The environment.
     *
     * @return void
     */
    private function matchParentInvisibleProperty(
        ConditionInterface $visibleCondition,
        DataDefinition\Definition\Properties\PropertyInterface $property,
        DataDefinition\Palette\PropertyInterface $legendProperty,
        array &$invisibleProperties,
        EnvironmentInterface $environment
    ) {
        $propertiesDefinition = $environment->getDataDefinition()->getPropertiesDefinition();

        foreach ($visibleCondition->getConditions() as $condition) {
            if ($condition instanceof ConditionChainInterface) {
                $this->matchParentInvisibleProperty(
                    $condition,
                    $property,
                    $legendProperty,
                    $invisibleProperties,
                    $environment
                );
            }

            if (!\method_exists($condition, 'getPropertyName')
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
     * @param Action               $action      The action.
     * @param EnvironmentInterface $environment The environment.
     *
     * @return ModelInterface
     */
    protected function getIntersectionModel(Action $action, EnvironmentInterface $environment)
    {
        $inputProvider        = $environment->getInputProvider();
        $dataProvider         = $environment->getDataProvider();
        $dataDefinition       = $environment->getDataDefinition();
        $propertiesDefinition = $dataDefinition->getPropertiesDefinition();
        $session              = $this->getSession($action, $environment);

        $intersectModel = $dataProvider->getEmptyModel();

        $defaultPalette      = null;
        $legendPropertyNames = $this->getLegendPropertyNames($intersectModel, $defaultPalette, $environment);

        $idProperty = \method_exists($dataProvider, 'getIdProperty') ? $dataProvider->getIdProperty() : 'id';
        foreach ((array) $session['intersectValues'] as $intersectProperty => $intersectValue) {
            if (($idProperty === $intersectProperty)
                || !$propertiesDefinition->hasProperty($intersectProperty)
                || ($this->useIntersectValue(
                        $intersectProperty,
                        $legendPropertyNames,
                        $defaultPalette,
                        $environment
                    ) === false)
            ) {
                continue;
            }

            if ($inputProvider->hasValue($intersectProperty)) {
                $intersectModel->setProperty($intersectProperty, $inputProvider->getValue($intersectProperty));

                continue;
            }

            $intersectModel->setProperty($intersectProperty, $intersectValue);
        }

        $this->intersectModelSetPrimaryId($action, $intersectModel, $idProperty, $environment);
        $this->intersectModelSetParentId($intersectModel, $environment);

        return $intersectModel;
    }

    /**
     * Use intersect value.
     *
     * @param string                $intersectPropertyName The intersect property name.
     * @param array                 $legendPropertyNames   The legend names.
     * @param PaletteInterface|null $defaultPalette        The default palette.
     * @param EnvironmentInterface  $environment           The environment.
     *
     * @return bool
     */
    private function useIntersectValue(
        $intersectPropertyName,
        array $legendPropertyNames,
        PaletteInterface $defaultPalette = null,
        EnvironmentInterface $environment
    ) {
        $propertiesDefinition = $environment->getDataDefinition()->getPropertiesDefinition();
        $useIntersectValue    = (bool) $defaultPalette;

        if ($defaultPalette && !$propertiesDefinition->getProperty($intersectPropertyName)->getWidgetType()
        ) {
            $useIntersectValue = true;
        }

        if ($defaultPalette
            && ($useIntersectValue === false)
            && \in_array($intersectPropertyName, $legendPropertyNames)
        ) {
            $useIntersectValue = true;
        }

        return $useIntersectValue;
    }

    /**
     * Set the primaray id to the model from intersect values.
     *
     * @param Action               $action         The action.
     * @param ModelInterface       $intersectModel The intersect model.
     * @param string               $idProperty     The id property.
     * @param EnvironmentInterface $environment    The environment.
     *
     * @return void
     */
    private function intersectModelSetPrimaryId(
        Action $action,
        $intersectModel,
        $idProperty,
        EnvironmentInterface $environment
    ) {
        if ($intersectModel->getId() !== null) {
            return;
        }

        $session = $this->getSession($action, $environment);

        $intersectModel->setId($session['intersectValues'][$idProperty]);
        $intersectModel->setProperty($idProperty, $session['intersectValues'][$idProperty]);
    }

    /**
     * Set the parent id to the inersect model.
     *
     * @param ModelInterface       $intersectModel The intersect model.
     * @param EnvironmentInterface $environment    The environment.
     *
     * @return void
     */
    private function intersectModelSetParentId(ModelInterface $intersectModel, EnvironmentInterface $environment)
    {
        $dataDefinition       = $environment->getDataDefinition();
        $parentDataDefinition = $environment->getParentDataDefinition();

        if ($parentDataDefinition === null) {
            return;
        }

        $relationships  = $dataDefinition->getModelRelationshipDefinition();
        $childCondition =
            $relationships->getChildCondition($parentDataDefinition->getName(), $dataDefinition->getName());

        $parentField = null;
        foreach ($childCondition->getSetters() as $setter) {
            if (!\array_key_exists('to_field', $setter)) {
                continue;
            }

            $parentField = $setter['to_field'];
            break;
        }

        if ($parentField !== null) {
            $intersectModel->setProperty(
                $parentField,
                ModelId::fromSerialized($environment->getInputProvider()->getParameter('pid'))
                    ->getId()
            );
        }
    }

    /**
     * Get legend property names.
     *
     * @param ModelInterface        $intersectModel The intersect model.
     * @param PaletteInterface|null $defaultPalette The default palette.
     * @param EnvironmentInterface  $environment    The environment.
     *
     * @return array
     */
    private function getLegendPropertyNames(
        ModelInterface $intersectModel,
        PaletteInterface &$defaultPalette = null,
        EnvironmentInterface $environment
    ) {
        $inputProvider      = $environment->getInputProvider();
        $palettesDefinition = $environment->getDataDefinition()->getPalettesDefinition();

        $legendPropertyNames = [];
        if ($inputProvider->hasValue('FORM_INPUTS') && \count($palettesDefinition->getPalettes()) === 1) {
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
     * @param Action               $action      The action.
     * @param EnvironmentInterface $environment The environment.
     *
     * @return array
     */
    abstract protected function getSession(Action $action, EnvironmentInterface $environment);

    /**
     * Return select properties from the session.
     *
     * @param Action               $action      The action.
     * @param EnvironmentInterface $environment The environment.
     *
     * @return array
     */
    abstract protected function getPropertiesFromSession(Action $action, EnvironmentInterface $environment);
}
