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
 * @author     Richard Henkenjohann <richardhenkenjohann@googlemail.com>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2013-2024 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\ActionHandler\MultipleHandler;

use Contao\System;
use Contao\Widget;
use ContaoCommunityAlliance\DcGeneral\Action;
use ContaoCommunityAlliance\DcGeneral\Contao\Compatibility\DcCompat;
use ContaoCommunityAlliance\DcGeneral\Contao\RequestScopeDeterminator;
use ContaoCommunityAlliance\DcGeneral\Contao\RequestScopeDeterminatorAwareTrait;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\ContaoWidgetManager;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\EncodePropertyValueFromWidgetEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Widget\AbstractWidget;
use ContaoCommunityAlliance\DcGeneral\Data\EditInformationInterface;
use ContaoCommunityAlliance\DcGeneral\Data\ModelInterface;
use ContaoCommunityAlliance\DcGeneral\Data\PropertyValueBag;
use ContaoCommunityAlliance\DcGeneral\Data\PropertyValueBagInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\ContainerInterface;
use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;
use ContaoCommunityAlliance\DcGeneral\Event\ActionEvent;
use ContaoCommunityAlliance\DcGeneral\InputProviderInterface;
use ContaoCommunityAlliance\DcGeneral\View\ActionHandler\AbstractPropertyOverrideEditAllHandler;
use ContaoCommunityAlliance\DcGeneral\View\ActionHandler\CallActionTrait;
use ContaoCommunityAlliance\Translator\TranslatorInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * The class handle the "overrideAll" commands.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class OverrideAllHandler extends AbstractPropertyOverrideEditAllHandler
{
    use RequestScopeDeterminatorAwareTrait;
    use CallActionTrait;

    /**
     * OverrideAllHandler constructor.
     *
     * @param RequestScopeDeterminator $scopeDeterminator The request scope determinator.
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
            || ('overrideAll' !== $event->getAction()->getName())
        ) {
            return;
        }

        if ('' !== ($response = $this->process($event->getAction(), $event->getEnvironment()))) {
            $event->setResponse($response);
            $event->stopPropagation();
        }
    }

    /**
     * Process the override all handler.
     *
     * @param Action               $action      The action.
     * @param EnvironmentInterface $environment The enviroment.
     *
     * @return string
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    private function process(Action $action, EnvironmentInterface $environment)
    {
        $inputProvider = $environment->getInputProvider();
        assert($inputProvider instanceof InputProviderInterface);

        $translator = $environment->getTranslator();
        assert($translator instanceof TranslatorInterface);

        $editInformation = System::getContainer()->get('cca.dc-general.edit-information');
        assert($editInformation instanceof EditInformationInterface);

        $renderInformation = new \ArrayObject();

        $propertyValueBag = new PropertyValueBag();
        foreach ($this->getOverrideProperties($action, $environment) as $property) {
            $propertyValueBag->setPropertyValue($property->getName(), $property->getDefaultValue());
        }

        if (false !== $inputProvider->hasValue('FORM_INPUTS')) {
            foreach ($inputProvider->getValue('FORM_INPUTS') as $formInput) {
                $propertyValueBag->setPropertyValue($formInput, $inputProvider->getValue($formInput));
            }
        }

        $this->invisibleUnusedProperties($action, $environment);
        $this->handleOverrideCollection($action, $renderInformation, $propertyValueBag, $environment);
        $this->renderFieldSets($action, $renderInformation, $propertyValueBag, $environment);
        $this->updateErrorInformation($renderInformation);

        if (!$editInformation->hasAnyModelError()) {
            $this->handleSubmit($action, $environment);
        }

        $definition = $environment->getDataDefinition();
        assert($definition instanceof ContainerInterface);

        return $this->renderTemplate(
            $action,
            [
                'subHeadline' =>
                    $translator->translate($inputProvider->getParameter('mode') . 'Selected', 'dc-general') . ': ' .
                    $translator->translate('editAll.label', 'dc-general'),
                'fieldsets'   => $renderInformation->offsetGet('fieldsets'),
                'table'       => $definition->getName(),
                'error'       => $renderInformation->offsetGet('error'),
                'breadcrumb'  => $this->renderBreadcrumb($environment),
                'editButtons' => $this->getEditButtons($action, $environment),
                'noReload'    => $editInformation->hasAnyModelError()
            ]
        );
    }

    /**
     * Handle invalid property value bag.
     *
     * @param PropertyValueBagInterface|null $propertyValueBag The property value bag.
     * @param ModelInterface|null            $model            The model.
     * @param EnvironmentInterface           $environment      The environment.
     *
     * @return void
     *
     * @deprecated Deprecated since 2.1 and where remove in 3.0.
     *
     * @SuppressWarnings(PHPMD.LongVariable)
     */
    protected function handleInvalidPropertyValueBag(
        PropertyValueBagInterface $propertyValueBag = null,
        ModelInterface $model = null,
        EnvironmentInterface $environment
    ) {
        // @codingStandardsIgnoreStart
        @\trigger_error('This function where remove in 3.0. ' . __CLASS__  . '::' . __FUNCTION__, E_USER_DEPRECATED);
        // @codingStandardsIgnoreEnd

        if ((null === $propertyValueBag) || (null === $model)) {
            return;
        }

        $inputProvider = $environment->getInputProvider();
        assert($inputProvider instanceof InputProviderInterface);

        foreach (\array_keys($propertyValueBag->getArrayCopy()) as $propertyName) {
            $allErrors    = $propertyValueBag->getPropertyValueErrors($propertyName);
            $mergedErrors = [];
            if (\count($allErrors) > 0) {
                foreach ($allErrors as $error) {
                    if (\in_array($error, $mergedErrors)) {
                        continue;
                    }

                    $mergedErrors[] = $error;
                }
            }

            $eventPropertyValueBag = new PropertyValueBag();
            $eventPropertyValueBag->setPropertyValue($propertyName, $inputProvider->getValue($propertyName, true));

            $event = new EncodePropertyValueFromWidgetEvent($environment, $model, $eventPropertyValueBag);
            $event->setProperty($propertyName)
                ->setValue($inputProvider->getValue($propertyName, true));

            $dispatcher = $environment->getEventDispatcher();
            assert($dispatcher instanceof EventDispatcherInterface);

            $dispatcher->dispatch($event, EncodePropertyValueFromWidgetEvent::NAME);

            $propertyValueBag->setPropertyValue($propertyName, $event->getValue());

            if (\count($mergedErrors) > 0) {
                $propertyValueBag->markPropertyValueAsInvalid($propertyName, $mergedErrors);
            }
        }
    }

    /**
     * Handle override of model collection.
     *
     * @param Action                    $action            The action.
     * @param \ArrayObject              $renderInformation The render information.
     * @param PropertyValueBagInterface $propertyValues    The property values.
     * @param EnvironmentInterface      $environment       The environment.
     *
     * @return void
     */
    private function handleOverrideCollection(
        Action $action,
        \ArrayObject $renderInformation,
        PropertyValueBagInterface $propertyValues = null,
        EnvironmentInterface $environment
    ) {
        if (!$propertyValues) {
            return;
        }

        $revertCollection = $this->getCollectionFromSession($action, $environment);
        $this->editCollection(
            $action,
            $this->getCollectionFromSession($action, $environment),
            $propertyValues,
            $renderInformation,
            $environment
        );
        if ($propertyValues->hasNoInvalidPropertyValues()) {
            $this->handleSubmit($action, $environment);
        }
        $this->revertValuesByErrors($action, $revertCollection, $environment);
    }

    /**
     * Return the select properties from the session.
     *
     * @param Action               $action      The action.
     * @param EnvironmentInterface $environment The environment.
     *
     * @return array
     */
    private function getOverrideProperties(Action $action, EnvironmentInterface $environment)
    {
        $selectProperties = $this->getPropertiesFromSession($action, $environment);

        $properties = [];
        foreach (\array_keys($selectProperties) as $propertyName) {
            $properties[$propertyName] = $selectProperties[$propertyName];
        }

        return $properties;
    }

    /**
     * Render the field sets.
     *
     * @param Action                    $action            The action.
     * @param \ArrayObject              $renderInformation The render information.
     * @param PropertyValueBagInterface $propertyValues    The property values.
     * @param EnvironmentInterface      $environment       The environment.
     *
     * @return void
     */
    private function renderFieldSets(
        Action $action,
        \ArrayObject $renderInformation,
        PropertyValueBagInterface $propertyValues,
        EnvironmentInterface $environment
    ) {
        $properties = $this->getOverrideProperties($action, $environment);
        $model      = $this->getIntersectionModel($action, $environment);

        $widgetManager = new ContaoWidgetManager($environment, $model);

        $errors   = [];
        $fieldSet = ['palette' => '', 'class' => 'tl_box'];

        $propertyNames = \array_keys($propertyValues->getArrayCopy());

        foreach ($propertyNames as $propertyName) {
            $errors = $this->getPropertyValueErrors($propertyValues, $propertyName, $errors);

            if (false === \array_key_exists($propertyName, $properties)) {
                continue;
            }

            $property = $properties[$propertyName];

            $this->setDefaultValue($model, $propertyValues, $propertyName, $environment);

            $widget = $widgetManager->getWidget($property->getName(), $propertyValues);
            assert($widget instanceof Widget);

            $widgetModel = $this->getModelFromWidget($widget);

            if (!$this->ensurePropertyVisibleInModel($action, $property->getName(), $widgetModel, $environment)) {
                $fieldSet['palette'] .=
                    $this->injectSelectParentPropertyInformation($action, $property, $widgetModel, $environment) ?? '';

                continue;
            }

            if ($extra = $property->getExtra()) {
                foreach (['tl_class'] as $extraName) {
                    unset($extra[$extraName]);
                }

                $property->setExtra($extra);
            }

            $fieldSet['palette'] .= $widgetManager->renderWidget($property->getName(), false, $propertyValues);

            $fieldSet['palette'] .= $this->injectSelectSubPropertiesInformation(
                $property,
                $widgetModel,
                $propertyValues,
                $environment
            ) ?? '';
        }

        $translator = $environment->getTranslator();
        assert($translator instanceof TranslatorInterface);

        if ('' === $fieldSet['palette']) {
            $fieldSet['palette'] = \sprintf(
                '<p>&nbsp;</p><strong>%s</strong><p>&nbsp;</p>',
                $translator->translate('no_properties_available', 'dc-general')
            );
        }

        $renderInformation->offsetSet('fieldsets', [$fieldSet]);
        $renderInformation->offsetSet('error', $errors);
    }

    /**
     * Get the model from the widget.
     *
     * @param Widget $widget The widget the contains the model.
     *
     * @return ModelInterface
     */
    private function getModelFromWidget(Widget $widget): ModelInterface
    {
        if ($widget->dataContainer instanceof DcCompat) {
            $model = $widget->dataContainer->getModel();
            if (null === $model) {
                throw new \InvalidArgumentException('Datacontainer does not hold a model.');
            }
            return $model;
        }
        if ($widget instanceof AbstractWidget) {
            return $widget->getModel();
        }

        throw new \InvalidArgumentException('Expected an instance of ' . AbstractWidget::class);
    }

    /**
     * Get the merged property value errors.
     *
     * @param PropertyValueBagInterface $propertyValueBag The property value bag.
     * @param string                    $propertyName     The property name.
     * @param array                     $errors           The errors.
     *
     * @return array
     */
    private function getPropertyValueErrors(
        PropertyValueBagInterface $propertyValueBag,
        string $propertyName,
        array $errors
    ): array {
        if (
            $propertyValueBag->hasPropertyValue($propertyName)
            && $propertyValueBag->isPropertyValueInvalid($propertyName)
        ) {
            $errors = \array_merge(
                $errors,
                $propertyValueBag->getPropertyValueErrors($propertyName)
            );
        }

        return $errors;
    }

    /**
     * Set the default value if no value is set.
     *
     * @param ModelInterface            $model            The model.
     * @param PropertyValueBagInterface $propertyValueBag The property value bag.
     * @param string                    $propertyName     The property name.
     * @param EnvironmentInterface      $environment      The environment.
     *
     * @return void
     */
    private function setDefaultValue(
        ModelInterface $model,
        PropertyValueBagInterface $propertyValueBag,
        string $propertyName,
        EnvironmentInterface $environment
    ): void {
        $definition = $environment->getDataDefinition();
        assert($definition instanceof ContainerInterface);

        $propertiesDefinition = $definition->getPropertiesDefinition();

        // If in the intersect model the value available, then set it as default.
        if ($modelValue = $model->getProperty($propertyName)) {
            $propertyValueBag->setPropertyValue($propertyName, $modelValue);

            return;
        }

        if (
            $propertiesDefinition->hasProperty($propertyName)
            && null !== ($inputProvider = $environment->getInputProvider())
            && !$inputProvider->hasValue($propertyName)
        ) {
            $propertyValueBag->setPropertyValue(
                $propertyName,
                $propertiesDefinition->getProperty($propertyName)->getDefaultValue()
            );
        }
    }
}
