<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2018 Contao Community Alliance.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    contao-community-alliance/dc-general
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2013-2018 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\ActionHandler;

use Contao\Widget;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\ContaoWidgetManager;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\EncodePropertyValueFromWidgetEvent;
use ContaoCommunityAlliance\DcGeneral\Data\ModelInterface;
use ContaoCommunityAlliance\DcGeneral\Data\PropertyValueBag;
use ContaoCommunityAlliance\DcGeneral\Data\PropertyValueBagInterface;
use ContaoCommunityAlliance\DcGeneral\View\ActionHandler\AbstractPropertyOverrideEditAllHandler;

/**
 * The class handle the "overrideAll" commands.
 */
class OverrideAllHandler extends AbstractPropertyOverrideEditAllHandler
{
    /**
     * Create the override all mask.
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    public function process()
    {
        $action = $this->getEvent()->getAction();
        if ($action->getName() !== 'overrideAll') {
            return;
        }

        $inputProvider   = $this->getEnvironment()->getInputProvider();
        $translator      = $this->getEnvironment()->getTranslator();
        $editInformation = $GLOBALS['container']['dc-general.edit-information'];

        $renderInformation = new \ArrayObject();
        $properties        = $this->getOverrideProperties();

        $propertyValueBag = new PropertyValueBag();
        foreach ($properties as $property) {
            $propertyValueBag->setPropertyValue($property->getName(), $property->getDefaultValue());
        }

        if (false !== $inputProvider->hasValue('FORM_INPUTS')) {
            foreach ($inputProvider->getValue('FORM_INPUTS') as $formInput) {
                $propertyValueBag->setPropertyValue($formInput, $inputProvider->getValue($formInput));
            }
        }

        $this->invisibleUnusedProperties();
        $this->handleOverrideCollection($renderInformation, $propertyValueBag);
        $this->renderFieldSets($renderInformation, $propertyValueBag);
        $this->updateErrorInformation($renderInformation);

        if (!$editInformation->hasAnyModelError()) {
            $this->handleSubmit();
        }

        $this->getEvent()->setResponse(
            $this->renderTemplate(
                [
                    'subHeadline' =>
                        $translator->translate('MSC.' . $inputProvider->getParameter('mode') . 'Selected') . ': ' .
                        $translator->translate('MSC.all.0'),
                    'fieldsets'   => $renderInformation->offsetGet('fieldsets'),
                    'table'       => $this->getEnvironment()->getDataDefinition()->getName(),
                    'error'       => $renderInformation->offsetGet('error'),
                    'breadcrumb'  => $this->renderBreadcrumb(),
                    'editButtons' => $this->getEditButtons(),
                    'noReload'    => (bool) $editInformation->hasAnyModelError()
                ]
            )
        );
    }

    /**
     * Handle invalid property value bag.
     *
     * @param PropertyValueBagInterface|null $propertyValueBag The property value bag.
     * @param ModelInterface|null            $model            The model.
     *
     * @return void
     */
    protected function handleInvalidPropertyValueBag(
        PropertyValueBagInterface $propertyValueBag = null,
        ModelInterface $model = null
    ) {
        if ((null === $propertyValueBag)
            || (null === $model)
        ) {
            return;
        }

        $inputProvider = $this->getEnvironment()->getInputProvider();

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

            $event = new EncodePropertyValueFromWidgetEvent($$this->getEnvironment(), $model, $eventPropertyValueBag);
            $event->setProperty($propertyName)
                ->setValue($inputProvider->getValue($propertyName, true));
            $this->getEnvironment()->getEventDispatcher()->dispatch(EncodePropertyValueFromWidgetEvent::NAME, $event);

            $propertyValueBag->setPropertyValue($propertyName, $event->getValue());

            if (\count($mergedErrors) > 0) {
                $propertyValueBag->markPropertyValueAsInvalid($propertyName, $mergedErrors);
            }
        }
    }

    /**
     * Handle override of model collection.
     *
     * @param \ArrayObject              $renderInformation The render information.
     * @param PropertyValueBagInterface $propertyValues    The property values.
     *
     * @return void
     */
    private function handleOverrideCollection(
        \ArrayObject $renderInformation,
        PropertyValueBagInterface $propertyValues = null
    ) {
        if (!$propertyValues) {
            return;
        }

        $revertCollection = $this->getCollectionFromSession();
        $this->editCollection($this->getCollectionFromSession(), $propertyValues, $renderInformation);
        if ($propertyValues->hasNoInvalidPropertyValues()) {
            $this->handleSubmit();
        }
        $this->revertValuesByErrors($revertCollection);
    }

    /**
     * Return the select properties from the session.
     *
     * @return array
     */
    private function getOverrideProperties()
    {
        $selectProperties = $this->getPropertiesFromSession();

        $properties = [];
        foreach (\array_keys($selectProperties) as $propertyName) {
            $properties[$propertyName] = $selectProperties[$propertyName];
        }

        return $properties;
    }

    /**
     * Render the field sets.
     *
     * @param \ArrayObject                   $renderInformation The render information.
     * @param PropertyValueBagInterface|null $propertyValues    The property values.
     *
     * @return void
     */
    private function renderFieldSets(\ArrayObject $renderInformation, PropertyValueBagInterface $propertyValues = null)
    {
        $properties = $this->getOverrideProperties();
        $model      = $this->getIntersectionModel();

        $widgetManager = new ContaoWidgetManager($this->getEnvironment(), $model);

        $errors   = [];
        $fieldSet = ['palette' => '', 'class' => 'tl_box'];

        $propertyNames = $propertyValues ? \array_keys($propertyValues->getArrayCopy()) : \array_keys($properties);

        foreach ($propertyNames as $propertyName) {
            $errors = $this->getPropertyValueErrors($propertyValues, $propertyName, $errors);

            if (false === \array_key_exists($propertyName, $properties)) {
                continue;
            }

            $property = $properties[$propertyName];

            $this->setDefaultValue($model, $propertyValues, $propertyName);

            $widget = $widgetManager->getWidget($property->getName(), $propertyValues);

            $widgetModel = $this->getModelFromWidget($widget);

            if (!$this->ensurePropertyVisibleInModel($property->getName(), $widgetModel)) {
                $fieldSet['palette'] .=
                    $this->injectSelectParentPropertyInformation($property, $widgetModel);

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
                $propertyValues
            );
        }

        if (empty($fieldSet['palette'])) {
            $fieldSet['palette'] = \sprintf(
                '<p>&nbsp;</p><strong>%s</strong><p>&nbsp;</p>',
                $this->getEnvironment()->getTranslator()->translate('MSC.no_properties_available')
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
    private function getModelFromWidget(Widget $widget)
    {
        if ($widget->dataContainer) {
            return $widget->dataContainer->getModel();
        }

        return $widget->getModel();
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
    private function getPropertyValueErrors(PropertyValueBagInterface $propertyValueBag, $propertyName, array $errors)
    {
        if (null !== $propertyValueBag
            && $propertyValueBag->hasPropertyValue($propertyName)
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
     *
     * @return void
     */
    private function setDefaultValue(ModelInterface $model, PropertyValueBagInterface $propertyValueBag, $propertyName)
    {
        $inputProvider        = $this->getEnvironment()->getInputProvider();
        $propertiesDefinition = $this->getEnvironment()->getDataDefinition()->getPropertiesDefinition();

        // If in the intersect model the value available, then set it as default.
        if ($modelValue = $model->getProperty($propertyName)) {
            $propertyValueBag->setPropertyValue($propertyName, $modelValue);

            return;
        }

        if (!$inputProvider->hasValue($propertyName)
            && $propertiesDefinition->hasProperty($propertyName)
        ) {
            $propertyValueBag->setPropertyValue(
                $propertyName,
                $propertiesDefinition->getProperty($propertyName)->getDefaultValue()
            );
        }
    }
}
