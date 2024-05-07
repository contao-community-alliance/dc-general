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
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2013-2024 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\View\ActionHandler;

use ContaoCommunityAlliance\DcGeneral\Action;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetPropertyOptionsEvent;
use ContaoCommunityAlliance\DcGeneral\DataDefinition;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\ConditionChainInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\ConditionInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\ContainerInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\PalettesDefinitionInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\Properties\PropertyInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Condition\Property\PropertyConditionInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Condition\Property\PropertyTrueCondition;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\PaletteInterface;
use ContaoCommunityAlliance\DcGeneral\Data\ModelId;
use ContaoCommunityAlliance\DcGeneral\Data\ModelInterface;
use ContaoCommunityAlliance\DcGeneral\Data\PropertyValueBagInterface;
use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;
use ContaoCommunityAlliance\DcGeneral\Exception\DcGeneralInvalidArgumentException;
use ContaoCommunityAlliance\DcGeneral\InputProviderInterface;
use ContaoCommunityAlliance\Translator\TranslatorInterface;
use LogicException;
use ReturnTypeWillChange;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

use function array_key_exists;
use function array_keys;
use function count;
use function get_class;
use function implode;
use function in_array;
use function method_exists;
use function sprintf;
use function Symfony\Component\DependencyInjection\Loader\Configurator\env;

/**
 * This abstract visibility handler provide methods for the visibility of properties.
 *
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
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
    #[ReturnTypeWillChange]
    protected function invisibleUnusedProperties(Action $action, EnvironmentInterface $environment)
    {
        $properties     = $this->getDataDefinition($environment)->getPropertiesDefinition();
        $editProperties = $this->getPropertiesFromSession($action, $environment);

        foreach ($properties->getPropertyNames() as $propertyName) {
            $property = $properties->getProperty($propertyName);
            if (isset($editProperties[$propertyName]) || !$property->getWidgetType()) {
                continue;
            }

            if ($this->excludePaletteSelectorProperty($action, $property, $environment)) {
                continue;
            }

            /** @var class-string<PropertyInterface> $propertyClass */
            $propertyClass = get_class($property);

            /** @var PropertyInterface $newProperty */
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
            if (null !== $options = $property->getOptions()) {
                $newProperty->setOptions($options);
            }

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
    ): bool {
        $palettesDefinition = $this->getDataDefinition($environment)->getPalettesDefinition();

        if (1 === count($palettesDefinition->getPalettes())) {
            return false;
        }

        return $this->analyzeExcludeProperty($action, $environment, $property, $palettesDefinition);
    }

    /**
     * Analyze property for exclude.
     *
     * @param Action                      $action             The action.
     * @param EnvironmentInterface        $environment        The environment.
     * @param PropertyInterface           $property           The property.
     * @param PalettesDefinitionInterface $palettesDefinition The palettes definition.
     *
     * @return bool
     */
    private function analyzeExcludeProperty(
        Action $action,
        EnvironmentInterface $environment,
        PropertyInterface $property,
        PalettesDefinitionInterface $palettesDefinition
    ): bool {
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
            $this->getEventDispatcher($environment)->dispatch($event, GetPropertyOptionsEvent::NAME);
            $options = $event->getOptions();
            if ((null === $options) || ([] === $options)) {
                continue;
            }

            $paletteCounter = 0;
            foreach (array_keys($options) as $paletteName) {
                $palettesDefinition->hasPaletteByName($paletteName) ? ++$paletteCounter : null;
            }
            if ($paletteCounter !== count($options)) {
                continue;
            }

            $property->setOptions($options);
            $excludeProperty = true;
        }

        return $excludeProperty;
    }

    /**
     * Make the property invisible in all legends of each palette.
     *
     * @param PropertyInterface    $property    The property.
     * @param EnvironmentInterface $environment The environment.
     *
     * @return void
     */
    private function makePropertyInvisibleByPalette(
        PropertyInterface $property,
        EnvironmentInterface $environment
    ): void {
        $palettes = $this->getDataDefinition($environment)->getPalettesDefinition();

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
    #[ReturnTypeWillChange]
    protected function ensurePropertyVisibleInModel(
        Action $action,
        $propertyName,
        ModelInterface $model,
        EnvironmentInterface $environment
    ) {
        $definition         = $this->getDataDefinition($environment);
        $palettesDefinition = $definition->getPalettesDefinition();
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

        $findProperty = $definition->getPropertiesDefinition()->getProperty($propertyName);

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
     *
     * @SuppressWarnings(PHPMD.LongVariable)
     */
    private function matchVisibilityOfPropertyInAnyPalette(
        Action $action,
        PropertyInterface $property,
        bool $invisible,
        EnvironmentInterface $environment
    ): bool {
        $definition         = $this->getDataDefinition($environment);
        $palettesDefinition = $definition->getPalettesDefinition();
        if (true === $invisible || 1 === count($palettesDefinition->getPalettes())) {
            return $invisible;
        }

        $propertiesDefinition = $definition->getPropertiesDefinition();
        $defaultPalette       = $palettesDefinition->findPalette();
        $defaultProperties    = $defaultPalette->getProperties();
        $intersectModel       = $this->getIntersectionModel($action, $environment);

        $invisibleProperty = $invisible;
        foreach ($defaultProperties as $defaultProperty) {
            if (!$propertiesDefinition->hasProperty($defaultProperty->getName())) {
                continue;
            }

            $paletteSelectorProperty = $propertiesDefinition->getProperty($defaultProperty->getName());
            if (null === $paletteSelectorProperty->getOptions()) {
                continue;
            }

            $invisibleProperty =
                $this->matchPaletteProperty($property, $intersectModel, $defaultProperty, $environment);

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
     * @param ModelInterface                           $intersectModel   The intersect model.
     * @param DataDefinition\Palette\PropertyInterface $selectorProperty The palette selector property.
     * @param EnvironmentInterface                     $environment      The environment.
     *
     * @return bool
     *
     * @SuppressWarnings(PHPMD.LongVariable)
     */
    private function matchPaletteProperty(
        PropertyInterface $property,
        ModelInterface $intersectModel,
        DataDefinition\Palette\PropertyInterface $selectorProperty,
        EnvironmentInterface $environment
    ): bool {
        $definition           = $this->getDataDefinition($environment);
        $palettesDefinition   = $definition->getPalettesDefinition();
        $propertiesDefinition = $definition->getPropertiesDefinition();

        $invisibleProperty       = false;
        $paletteSelectorProperty = $propertiesDefinition->getProperty($selectorProperty->getName());
        foreach (array_keys($paletteSelectorProperty->getOptions() ?? []) as $paletteName) {
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
        string $paletteName,
        EnvironmentInterface $environment
    ): bool {
        $palettesDefinition = $this->getDataDefinition($environment)->getPalettesDefinition();

        $intersectModel->setProperty($selectorProperty->getName(), $paletteName);
        $searchPalette = $palettesDefinition->findPalette($intersectModel);

        $invisibleProperty = false;
        foreach ($searchPalette->getProperties($intersectModel) as $searchProperty) {
            if ($searchProperty->getName() !== $property->getName()) {
                continue;
            }

            $visibleCondition = $searchProperty->getVisibleCondition();
            assert($visibleCondition instanceof PropertyConditionInterface);

            if (false === $visibleCondition->match($intersectModel)) {
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
     * @param Action               $action      The action.
     * @param PropertyInterface    $property    The property.
     * @param ModelInterface       $model       The model.
     * @param EnvironmentInterface $environment The environment.
     *
     * @return null|string
     */
    #[ReturnTypeWillChange]
    protected function injectSelectParentPropertyInformation(
        Action $action,
        PropertyInterface $property,
        ModelInterface $model,
        EnvironmentInterface $environment
    ) {
        $translator         = $this->getTranslator($environment);
        $palettesDefinition = $this->getDataDefinition($environment)->getPalettesDefinition();

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

            $information[] = sprintf(
                '<p class="tl_new">%s</p>',
                $translator->translate(
                    'select_parent_property_info',
                    'dc-general',
                    ['%parent_property%' => $labelParentProperty, '%edit_property%' => $labelEditProperty]
                )
            );
        }

        return implode('', $information);
    }

    /**
     * Inject select sub properties information,
     * if select an sub selector and their properties don´t select for edit.
     *
     * @param PropertyInterface         $property         The property.
     * @param ModelInterface            $model            The model.
     * @param PropertyValueBagInterface $propertyValueBag The property values.
     * @param EnvironmentInterface      $environment      The environment.
     *
     * @return null|string
     */
    #[ReturnTypeWillChange]
    protected function injectSelectSubPropertiesInformation(
        PropertyInterface $property,
        ModelInterface $model,
        PropertyValueBagInterface $propertyValueBag,
        EnvironmentInterface $environment
    ) {
        $translator = $this->getTranslator($environment);

        $properties = $this->matchInvisibleSubProperties($model, $property, $propertyValueBag, $environment);
        if (empty($properties)) {
            return null;
        }

        $information = [];
        foreach ($properties as $propertyName => $informationProperty) {
            $label = $translator->translate(
                $informationProperty->getLabel() ?: $propertyName,
                $environment->getDataDefinition()?->getName()
            );

            $information[] =
                '<p class="tl_new">' .
                $translator->translate('select_property_info', 'dc-general', ['%property%' => $label]) .
                '</p>'
            ;
        }

        return implode('', $information);
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
    ): void {
        $definition         = $this->getDataDefinition($environment);
        $palettesDefinition = $definition->getPalettesDefinition();

        if (!empty($invisibleProperties) || 1 > count($palettesDefinition->getPalettes())) {
            return;
        }

        $propertiesDefinition = $definition->getPropertiesDefinition();

        $session = $this->getSession($action, $environment);

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
    ): void {
        $propertiesDefinition = $this->getDataDefinition($environment)->getPropertiesDefinition();
        if (!$visibleCondition instanceof ConditionChainInterface) {
            return;
        }
        foreach ($visibleCondition->getConditions() as $condition) {
            if ($condition instanceof ConditionChainInterface) {
                $this->matchInvisibleProperty($condition, $invisibleProperties, $environment);
            }

            if (!method_exists($condition, 'getPropertyName')) {
                continue;
            }

            if (
                isset($invisibleProperties[$condition->getPropertyName()])
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
     * @param ModelInterface            $model            The model.
     * @param PropertyInterface         $property         The property.
     * @param PropertyValueBagInterface $propertyValueBag The property values.
     * @param EnvironmentInterface      $environment      The environment.
     *
     * @return array
     */
    private function matchInvisibleSubProperties(
        ModelInterface $model,
        PropertyInterface $property,
        PropertyValueBagInterface $propertyValueBag,
        EnvironmentInterface $environment
    ): array {
        $palettesDefinition = $this->getDataDefinition($environment)->getPalettesDefinition();

        $testPropertyValueBag = clone $propertyValueBag;
        $testPropertyValueBag->setPropertyValue('dummyNotVisible', true);

        $palette = $palettesDefinition->findPalette($model);

        $properties = [];
        foreach ($palette->getLegends() as $legend) {
            if (!$legend->hasProperty($property->getName())) {
                continue;
            }

            $legendProperties = $legend->getProperties($model, $testPropertyValueBag);
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
     * @param ConditionInterface                       $visibleCondition    The visible condition.
     * @param PropertyInterface                        $property            The property.
     * @param DataDefinition\Palette\PropertyInterface $legendProperty      The legend property.
     * @param array                                    $invisibleProperties The invisible properties.
     * @param EnvironmentInterface                     $environment         The environment.
     *
     * @return void
     */
    private function matchParentInvisibleProperty(
        ConditionInterface $visibleCondition,
        PropertyInterface $property,
        DataDefinition\Palette\PropertyInterface $legendProperty,
        array &$invisibleProperties,
        EnvironmentInterface $environment
    ): void {
        $propertiesDefinition = $this->getDataDefinition($environment)->getPropertiesDefinition();
        if (!$visibleCondition instanceof ConditionChainInterface) {
            return;
        }

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

            if (
                !method_exists($condition, 'getPropertyName')
                || ($property->getName() !== $condition->getPropertyName())
            ) {
                continue;
            }

            if (
                isset($invisibleProperties[$legendProperty->getName()])
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
    #[ReturnTypeWillChange]
    protected function getIntersectionModel(Action $action, EnvironmentInterface $environment)
    {
        $inputProvider        = $this->getInputProvider($environment);
        $dataProvider         = $environment->getDataProvider();
        $dataDefinition       = $this->getDataDefinition($environment);
        $propertiesDefinition = $dataDefinition->getPropertiesDefinition();
        $session              = $this->getSession($action, $environment);
        if (null === $dataProvider) {
            throw new LogicException('No data provider found in environment.');
        }

        $intersectModel = $dataProvider->getEmptyModel();

        $defaultPalette      = null;
        $legendPropertyNames = $this->getLegendPropertyNames($intersectModel, $environment, $defaultPalette);

        $idProperty = method_exists($dataProvider, 'getIdProperty') ? $dataProvider->getIdProperty() : 'id';
        foreach ((array) $session['intersectValues'] as $intersectProperty => $intersectValue) {
            if (
                ($idProperty === $intersectProperty)
                || !$propertiesDefinition->hasProperty($intersectProperty)
                || (false === $this->useIntersectValue(
                    $intersectProperty,
                    $legendPropertyNames,
                    $environment,
                    $defaultPalette
                ))
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
     * @param EnvironmentInterface  $environment           The environment.
     *
     * @param PaletteInterface|null $defaultPalette        The default palette.
     *
     * @return bool
     *
     * @SuppressWarnings(PHPMD.LongVariable)
     */
    private function useIntersectValue(
        $intersectPropertyName,
        array $legendPropertyNames,
        EnvironmentInterface $environment,
        PaletteInterface $defaultPalette = null
    ): bool {
        $propertiesDefinition = $this->getDataDefinition($environment)->getPropertiesDefinition();
        $useIntersectValue    = (bool) $defaultPalette;

        if ($defaultPalette && !$propertiesDefinition->getProperty($intersectPropertyName)->getWidgetType()) {
            $useIntersectValue = true;
        }

        if (
            $defaultPalette
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
    ): void {
        if (null !== $intersectModel->getId()) {
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
     *
     * @throws DcGeneralInvalidArgumentException Invalid configuration. Child condition must be defined.
     */
    private function intersectModelSetParentId(ModelInterface $intersectModel, EnvironmentInterface $environment): void
    {
        $dataDefinition       = $this->getDataDefinition($environment);
        $parentDataDefinition = $environment->getParentDataDefinition();

        if (null === $parentDataDefinition) {
            return;
        }

        $relationships  = $dataDefinition->getModelRelationshipDefinition();
        $childCondition =
            $relationships->getChildCondition($parentDataDefinition->getName(), $dataDefinition->getName());
        if (null === $childCondition) {
            throw new DcGeneralInvalidArgumentException(
                'Invalid configuration. Child condition must be defined!'
            );
        }

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
                ModelId::fromSerialized($this->getInputProvider($environment)->getParameter('pid'))
                    ->getId()
            );
        }
    }

    /**
     * Get legend property names.
     *
     * @param ModelInterface        $intersectModel The intersect model.
     * @param EnvironmentInterface  $environment    The environment.
     * @param PaletteInterface|null $defaultPalette The default palette.
     *
     * @return array
     */
    private function getLegendPropertyNames(
        ModelInterface $intersectModel,
        EnvironmentInterface $environment,
        PaletteInterface &$defaultPalette = null
    ): array {
        $inputProvider      = $this->getInputProvider($environment);
        $palettesDefinition = $this->getDataDefinition($environment)->getPalettesDefinition();

        $legendPropertyNames = [];
        if ($inputProvider->hasValue('FORM_INPUTS') && (1 === count($palettesDefinition->getPalettes()))) {
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
    #[ReturnTypeWillChange]
    abstract protected function getSession(Action $action, EnvironmentInterface $environment);

    /**
     * Return select properties from the session.
     *
     * @param Action               $action      The action.
     * @param EnvironmentInterface $environment The environment.
     *
     * @return array
     */
    #[ReturnTypeWillChange]
    abstract protected function getPropertiesFromSession(Action $action, EnvironmentInterface $environment);

    /**
     * Get property value bag from the model.
     *
     * @param Action               $action      The action.
     * @param ModelInterface       $model       The model.
     * @param EnvironmentInterface $environment The environment.
     *
     * @return PropertyValueBagInterface
     *
     * @throws DcGeneralInvalidArgumentException If create property value bug, the construct argument isn´t right.
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    abstract protected function getPropertyValueBagFromModel(
        Action $action,
        ModelInterface $model,
        EnvironmentInterface $environment
    );

    private function getDataDefinition(EnvironmentInterface $environment): ContainerInterface
    {
        $definition = $environment->getDataDefinition();
        if (null === $definition) {
            throw new LogicException('No data definition found in environment.');
        }

        return $definition;
    }

    private function getEventDispatcher(EnvironmentInterface $environment): EventDispatcherInterface
    {
        $dispatcher = $environment->getEventDispatcher();
        if (null === $dispatcher) {
            throw new LogicException('No event dispatcher found in environment.');
        }

        return $dispatcher;
    }

    private function getInputProvider(EnvironmentInterface $environment): InputProviderInterface
    {
        $input = $environment->getInputProvider();
        if (null === $input) {
            throw new LogicException('No input provider found in environment.');
        }

        return $input;
    }

    private function getTranslator(EnvironmentInterface $environment): TranslatorInterface
    {
        $translator = $environment->getTranslator();

        if (null === $translator) {
            throw new LogicException('No translator found in environment.');
        }

        return $translator;
    }
}
