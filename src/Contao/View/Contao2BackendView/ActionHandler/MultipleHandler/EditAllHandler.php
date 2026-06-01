<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2026 Contao Community Alliance.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    contao-community-alliance/dc-general
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     Richard Henkenjohann <richardhenkenjohann@googlemail.com>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2013-2026 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\ActionHandler\MultipleHandler;

use Contao\CoreBundle\Intl\Locales;
use Contao\System;
use ContaoCommunityAlliance\DcGeneral\Action;
use ContaoCommunityAlliance\DcGeneral\Contao\RequestScopeDeterminator;
use ContaoCommunityAlliance\DcGeneral\Contao\RequestScopeDeterminatorAwareTrait;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\ContaoWidgetManager;
use ContaoCommunityAlliance\DcGeneral\Data\DataProviderInterface;
use ContaoCommunityAlliance\DcGeneral\Data\EditInformationInterface;
use ContaoCommunityAlliance\DcGeneral\Data\ModelId;
use ContaoCommunityAlliance\DcGeneral\Data\ModelIdInterface;
use ContaoCommunityAlliance\DcGeneral\Data\ModelInterface;
use ContaoCommunityAlliance\DcGeneral\Data\MultiLanguageDataProviderInterface;
use ContaoCommunityAlliance\DcGeneral\Data\PropertyValueBag;
use ContaoCommunityAlliance\DcGeneral\Data\PropertyValueBagInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\ContainerInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\Properties\PropertyInterface;
use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;
use ContaoCommunityAlliance\DcGeneral\Event\ActionEvent;
use ContaoCommunityAlliance\DcGeneral\InputProviderInterface;
use ContaoCommunityAlliance\DcGeneral\View\ActionHandler\AbstractPropertyOverrideEditAllHandler;
use ContaoCommunityAlliance\DcGeneral\View\ActionHandler\CallActionTrait;
use ContaoCommunityAlliance\Translator\TranslatorInterface;

/**
 * The class handle the "editAll" commands.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 *
 * @api
 */
class EditAllHandler extends AbstractPropertyOverrideEditAllHandler
{
    use RequestScopeDeterminatorAwareTrait;
    use CallActionTrait;

    /**
     * EditAllHandler constructor.
     *
     * @param RequestScopeDeterminator $scopeDeterminator
     */
    public function __construct(RequestScopeDeterminator $scopeDeterminator)
    {
        $this->scopeDeterminator = $scopeDeterminator;
    }

    /**
     * {@inheritDoc}
     */
    public function handleEvent(ActionEvent $event): void
    {
        if (
            !$this->getScopeDeterminator()->currentScopeIsBackend()
            || ('editAll' !== $event->getAction()->getName())
        ) {
            return;
        }

        $response = $this->process($event->getAction(), $event->getEnvironment());
        $event->setResponse($response);
        $event->stopPropagation();
    }

    /**
     * {@inheritDoc}
     */
    private function process(Action $action, EnvironmentInterface $environment): string
    {
        $inputProvider = $environment->getInputProvider();
        assert($inputProvider instanceof InputProviderInterface);

        $translator = $environment->getTranslator();
        assert($translator instanceof TranslatorInterface);

        $renderInformation = new \ArrayObject();

        $this->invisibleUnusedProperties($action, $environment);
        $this->buildFieldSets($action, $renderInformation, $environment);
        $this->updateErrorInformation($renderInformation);

        if (!$renderInformation->offsetGet('error')) {
            $this->handleSubmit($action, $environment);
        }

        $definition = $environment->getDataDefinition();
        assert($definition instanceof ContainerInterface);

        $dataProvider = $environment->getDataProvider($definition->getName());
        assert($dataProvider instanceof DataProviderInterface);

        return $this->renderTemplate(
            $action,
            \array_merge(
                [
                    /** @psalm-suppress MixedOperand */
                    'subHeadline' =>
                        $translator->translate(
                            (string) $inputProvider->getParameter('mode') . 'Selected',
                            'dc-general'
                        ) . ': ' . $translator->translate('editAll.label', 'dc-general'),
                    'fieldsets'   => $renderInformation->offsetGet('fieldsets'),
                    'table'       => $definition->getName(),
                    'error'       => $renderInformation->offsetGet('error'),
                    'breadcrumb'  => $this->renderBreadcrumb($environment),
                    'editButtons' => $this->getEditButtons($action, $environment),
                    'noReload'    => (bool) $renderInformation->offsetGet('error')
                ],
                $this->buildLanguageVars($dataProvider)
            )
        );
    }

    /**
     * Build language-related template variables.
     *
     * @param DataProviderInterface $dataProvider The data provider.
     *
     * @return array{language: string|null, languages: null, fallbackLanguage: string|null, languageHeadline: string}
     */
    private function buildLanguageVars(DataProviderInterface $dataProvider): array
    {
        if (
            ($dataProvider instanceof MultiLanguageDataProviderInterface)
            && (null !== $dataProvider->getLanguages(null))
        ) {
            $locales = System::getContainer()->get('contao.intl.locales');
            assert($locales instanceof Locales);
            $languages = $locales->getLocales(null, true);

            return [
                'language'         => $dataProvider->getCurrentLanguage(),
                'languages'        => null,
                'fallbackLanguage' => $dataProvider->getFallbackLanguage(null)?->getLocale(),
                'languageHeadline' => $languages[$dataProvider->getCurrentLanguage()] ?? '',
            ];
        }

        return [
            'language'         => null,
            'languages'        => null,
            'fallbackLanguage' => null,
            'languageHeadline' => '',
        ];
    }

    /**
     * Build the field sets for each model.
     *
     * Return error if their given.
     *
     * @param Action               $action            The action.
     * @param \ArrayObject         $renderInformation The render information.
     * @param EnvironmentInterface $environment       The environment.
     *
     * @return void
     */
    private function buildFieldSets(Action $action, \ArrayObject $renderInformation, EnvironmentInterface $environment)
    {
        $inputProvider = $environment->getInputProvider();
        assert($inputProvider instanceof InputProviderInterface);

        /** @psalm-suppress MixedAssignment */
        $formInputs = $inputProvider->getValue('FORM_INPUTS');
        $collection = $this->getCollectionFromSession($action, $environment);

        $fieldSets = [];
        $errors    = [];
        while ($collection->count() > 0) {
            if (null === ($model = $collection->shift())) {
                continue;
            }

            $modelId           = ModelId::fromModel($model);
            $propertyValuesBag = $this->getPropertyValueBagFromModel($action, $model, $environment);

            if ($formInputs) {
                $this->handleEditCollection($action, $propertyValuesBag, $model, $renderInformation, $environment);
            }

            $fields = $this->renderEditFields(
                $action,
                new ContaoWidgetManager($environment, $model),
                $model,
                $propertyValuesBag,
                $environment
            );

            if (\count($fields) < 1) {
                continue;
            }

            /** @psalm-suppress MixedArgumentTypeCoercion */
            $palette = \implode('', $fields);
            $fieldSets[] = [
                'label'   => $modelId->getSerialized(),
                'model'   => $model,
                'legend'  => \str_replace('::', '____', $modelId->getSerialized()),
                'class'   => 'tl_box',
                'palette' => $palette
            ];
        }

        $renderInformation->offsetSet('fieldsets', $this->handleLegendCollapsed($fieldSets));
        $renderInformation->offsetSet('error', $errors);
    }

    /**
     * Handle legend how are open if errors available.
     *
     * @param array $fieldSets The field sets.
     *
     * @return array
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    private function handleLegendCollapsed(array $fieldSets)
    {
        $editInformation = System::getContainer()->get('cca.dc-general.edit-information');
        assert($editInformation instanceof EditInformationInterface);

        if (!$editInformation->hasAnyModelError()) {
            return $fieldSets;
        }

        foreach (\array_keys($fieldSets) as $index) {
            /** @psalm-suppress MixedArrayAccess */
            $fieldSetModel = $fieldSets[$index]['model'];
            assert($fieldSetModel instanceof ModelInterface);
            if ($editInformation->getModelError($fieldSetModel)) {
                continue;
            }

            /** @psalm-suppress MixedOperand, MixedArrayAccess, MixedArrayAssignment */
            $fieldSets[$index]['class'] .= ' collapsed';
        }

        return $fieldSets;
    }

    /**
     * Render the edit fields.
     *
     * @param Action                    $action            The action.
     * @param ContaoWidgetManager       $widgetManager     The widget manager.
     * @param ModelInterface            $model             The model.
     * @param PropertyValueBagInterface $propertyValuesBag The property values.
     * @param EnvironmentInterface      $environment       The environment.
     *
     * @return array
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    private function renderEditFields(
        Action $action,
        ContaoWidgetManager $widgetManager,
        ModelInterface $model,
        PropertyValueBagInterface $propertyValuesBag,
        EnvironmentInterface $environment
    ) {
        $definition = $environment->getDataDefinition();
        assert($definition instanceof ContainerInterface);

        $properties = $definition->getPropertiesDefinition();

        $selectProperties = $this->getPropertiesFromSession($action, $environment);

        $modelId      = ModelId::fromModel($model);
        $dataProvider = $environment->getDataProvider($modelId->getDataProviderName());
        assert($dataProvider instanceof DataProviderInterface);

        $editModel = $dataProvider->fetch($dataProvider->getEmptyConfig()->setId($modelId->getId()));
        assert($editModel instanceof ModelInterface);

        $visibleModel = $this->getVisibleModel($action, $editModel, $dataProvider, $environment);

        $fields = [];
        foreach ($selectProperties as $selectProperty) {
            assert($selectProperty instanceof PropertyInterface);
            if (
                !$this->ensurePropertyVisibleInModel(
                    $action,
                    $selectProperty->getName(),
                    $visibleModel,
                    $environment
                )
            ) {
                $fields[] =
                    $this->injectSelectParentPropertyInformation($action, $selectProperty, $editModel, $environment);

                continue;
            }

            $editProperty = $this->buildEditProperty($selectProperty, $modelId);

            $properties->addProperty($editProperty);

            $rawValues = new PropertyValueBag();
            if (!empty($editProperty->getExtra()['readonly'])) {
                // For readonly properties (e.g. force_alias, force_combinedvalues):
                // Pre-set the value under the mangled property name so the widget can display it.
                // Pass null as raw values to bypass updateModelFromPropertyBag, which would null
                // out readonly+alwaysSave properties and produce an empty display value.
                $model->setProperty($editProperty->getName(), $model->getProperty($selectProperty->getName()));
                $fields[] = $widgetManager->renderWidget($editProperty->getName(), false, null);
            } else {
                $this->setPropertyValue($editModel, $selectProperty, $propertyValuesBag);
                /** @psalm-suppress MixedAssignment */
                foreach ($propertyValuesBag as $propName => $value) {
                    $rawValues->setPropertyValue($propName, $widgetManager->decodeValue($propName, $value));
                }
                $editErrors = $propertyValuesBag->getInvalidPropertyErrors();
                /** @psalm-suppress MixedAssignment */
                foreach ($editErrors as $propName => $errors) {
                    /** @psalm-suppress MixedArgumentTypeCoercion, MixedArgument */
                    $rawValues->markPropertyValueAsInvalid((string) $propName, $errors);
                }

                $this->markEditErrors($editProperty, $selectProperty, $rawValues);
                $this->markModelErrors(
                    $action,
                    $model,
                    $model,
                    $editProperty,
                    $selectProperty,
                    $rawValues,
                    $environment
                );

                $fields[] = $widgetManager->renderWidget($editProperty->getName(), false, $rawValues);
            }

            $fields[] = $this->injectSelectSubPropertiesInformation(
                $selectProperty,
                $editModel,
                $rawValues,
                $environment
            );
        }

        if (null === $fields[0]) {
            $translator = $environment->getTranslator();
            assert($translator instanceof TranslatorInterface);

            $fields[] = \sprintf(
                '<p>&nbsp;</p><strong>%s</strong><p>&nbsp;</p>',
                $translator->translate('no_properties_available', 'dc-general')
            );
        }

        return $fields;
    }

    /**
     * Get the visible model.
     *
     * @param Action                $action       The action.
     * @param ModelInterface        $editModel    The edit model.
     * @param DataProviderInterface $dataProvider The data provider.
     * @param EnvironmentInterface  $environment  The environment.
     *
     * @return ModelInterface
     */
    private function getVisibleModel(
        Action $action,
        ModelInterface $editModel,
        DataProviderInterface $dataProvider,
        EnvironmentInterface $environment
    ) {
        $selectProperties = $this->getPropertiesFromSession($action, $environment);

        $visibleModel = $dataProvider->getEmptyModel();
        $visibleModel->setId($editModel->getId());

        $widgetManager    = new ContaoWidgetManager($environment, $editModel);
        $propertyValueBag = new PropertyValueBag();

        foreach (\array_keys($selectProperties) as $visiblePropertyName) {
            $visiblePropertyName = (string) $visiblePropertyName;
            /** @psalm-suppress MixedAssignment */
            $visiblePropertyValue = $editModel->getProperty($visiblePropertyName);

            $propertyValueBag->setPropertyValue($visiblePropertyName, $visiblePropertyValue);

            $visibleModel->setProperty(
                $visiblePropertyName,
                $widgetManager->encodeValue(
                    $visiblePropertyName,
                    $widgetManager->decodeValue($visiblePropertyName, $visiblePropertyValue),
                    $propertyValueBag
                )
            );
        }

        return $visibleModel;
    }

    /**
     * Set property value.
     *
     * @param ModelInterface            $editModel         The edit model.
     * @param PropertyInterface         $selectProperty    The property.
     * @param PropertyValueBagInterface $propertyValuesBag The property value.
     *
     * @return void
     */
    private function setPropertyValue(
        ModelInterface $editModel,
        PropertyInterface $selectProperty,
        PropertyValueBagInterface $propertyValuesBag
    ) {
        if ($propertyValuesBag->hasPropertyValue($selectProperty->getName())) {
            $propertyValuesBag->setPropertyValue(
                $selectProperty->getName(),
                $editModel->getProperty($selectProperty->getName())
            );
        }
    }

    /**
     * Mark edit errors.
     *
     * @param PropertyInterface         $editProperty      The edit property.
     * @param PropertyInterface         $selectProperty    The select property.
     * @param PropertyValueBagInterface $propertyValuesBag The property values.
     *
     * @return void
     */
    private function markEditErrors(
        PropertyInterface $editProperty,
        PropertyInterface $selectProperty,
        PropertyValueBagInterface $propertyValuesBag
    ) {
        if (
            ($editErrors = $propertyValuesBag->getInvalidPropertyErrors())
            && \array_key_exists($selectProperty->getName(), $editErrors)
        ) {
            /** @psalm-suppress MixedArgument */
            $propertyValuesBag->markPropertyValueAsInvalid(
                $editProperty->getName(),
                $editErrors[$selectProperty->getName()]
            );
        }
    }

    /**
     * Mark model errors.
     *
     * @param Action                    $action            The action.
     * @param ModelInterface            $model             The model.
     * @param ModelInterface            $editModel         The edit model.
     * @param PropertyInterface         $editProperty      The edit property.
     * @param PropertyInterface         $selectProperty    The select property.
     * @param PropertyValueBagInterface $propertyValuesBag The properties values.
     * @param EnvironmentInterface      $environment       The environment.
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    private function markModelErrors(
        Action $action,
        ModelInterface $model,
        ModelInterface $editModel,
        PropertyInterface $editProperty,
        PropertyInterface $selectProperty,
        PropertyValueBagInterface $propertyValuesBag,
        EnvironmentInterface $environment
    ) {
        $editInformation = System::getContainer()->get('cca.dc-general.edit-information');
        assert($editInformation instanceof EditInformationInterface);

        $sessionValues = $this->getEditPropertiesByModelId($action, ModelId::fromModel($model), $environment);

        $modelError = $editInformation->getModelError($editModel);
        if ($modelError && isset($modelError[$selectProperty->getName()])) {
            $propertyValuesBag->setPropertyValue(
                $editProperty->getName(),
                $sessionValues[$selectProperty->getName()]
            );

            $propertyValuesBag->setPropertyValue(
                $selectProperty->getName(),
                $sessionValues[$selectProperty->getName()]
            );

            /** @psalm-suppress MixedArgument */
            $propertyValuesBag->markPropertyValueAsInvalid(
                $editProperty->getName(),
                $modelError[$selectProperty->getName()]
            );
        }
    }

    /**
     * Handle edit collection of models.
     *
     * @param Action                    $action            The action.
     * @param PropertyValueBagInterface $propertyValuesBag The property values.
     * @param ModelInterface            $model             The model.
     * @param \ArrayObject              $renderInformation The render information.
     * @param EnvironmentInterface      $environment       The environment.
     *
     * @return void
     */
    private function handleEditCollection(
        Action $action,
        PropertyValueBagInterface $propertyValuesBag,
        ModelInterface $model,
        \ArrayObject $renderInformation,
        EnvironmentInterface $environment
    ) {
        $dataProvider = $environment->getDataProvider($model->getProviderName());
        assert($dataProvider instanceof DataProviderInterface);

        $editCollection   = $dataProvider->getEmptyCollection();
        $revertCollection = $dataProvider->getEmptyCollection();

        $editCollection->push($model);

        $revertModel = clone $model;
        $revertModel->setId($model->getId());
        $revertCollection->push($model);

        $this->editCollection($action, $editCollection, $propertyValuesBag, $renderInformation, $environment);

        $this->revertValuesByErrors($action, $revertCollection, $environment);
    }

    /**
     * Build edit property from the original property.
     *
     * @param PropertyInterface $originalProperty The original property.
     * @param ModelIdInterface  $modelId          The model id.
     *
     * @return PropertyInterface
     */
    private function buildEditProperty(PropertyInterface $originalProperty, ModelIdInterface $modelId)
    {
        $editPropertyClass = \get_class($originalProperty);

        $editPropertyName = \str_replace('::', '____', $modelId->getSerialized()) . '_' . $originalProperty->getName();

        $editProperty = new $editPropertyClass($editPropertyName);
        $editProperty->setLabel($originalProperty->getLabel());
        $editProperty->setDescription($originalProperty->getDescription());
        $editProperty->setDefaultValue($editProperty->getDefaultValue());
        $editProperty->setExcluded($originalProperty->isExcluded());
        $editProperty->setSearchable($originalProperty->isSearchable());
        $editProperty->setFilterable($originalProperty->isFilterable());
        $editProperty->setWidgetType($originalProperty->getWidgetType());
        $editProperty->setOptions($originalProperty->getOptions() ?? []);
        $editProperty->setExplanation($originalProperty->getExplanation());
        $editProperty->setExtra($originalProperty->getExtra());

        return $editProperty;
    }
}
