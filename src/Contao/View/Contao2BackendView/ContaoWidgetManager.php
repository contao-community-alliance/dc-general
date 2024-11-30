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
 * @author     Tristan Lins <tristan.lins@bit3.de>
 * @author     Christopher Boelter <christopher@boelter.eu>
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     Richard Henkenjohann <richardhenkenjohann@googlemail.com>
 * @copyright  2013-2024 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView;

use Contao\Backend;
use Contao\Config;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\Date;
use Contao\Input;
use Contao\System;
use Contao\TemplateLoader;
use Contao\Widget;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\BuildWidgetEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\DecodePropertyValueForWidgetEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\EncodePropertyValueFromWidgetEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\ResolveWidgetErrorMessageEvent;
use ContaoCommunityAlliance\DcGeneral\Controller\ControllerInterface;
use ContaoCommunityAlliance\DcGeneral\Data\ModelId;
use ContaoCommunityAlliance\DcGeneral\Data\ModelInterface;
use ContaoCommunityAlliance\DcGeneral\Data\PropertyValueBag;
use ContaoCommunityAlliance\DcGeneral\Data\PropertyValueBagInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\ContainerInterface;
use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;
use ContaoCommunityAlliance\DcGeneral\Exception\DcGeneralInvalidArgumentException;
use ContaoCommunityAlliance\DcGeneral\Exception\DcGeneralRuntimeException;
use ContaoCommunityAlliance\DcGeneral\InputProviderInterface;
use ContaoCommunityAlliance\DcGeneral\SessionStorageInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class ContaoWidgetManager.
 *
 * This class is responsible for creating widgets and processing data through them.
 *
 * @SuppressWarnings(PHPMD.LongClassName)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ContaoWidgetManager
{
    /**
     * The environment in use.
     *
     * @var ContaoFramework
     */
    protected $framework;

    /**
     * The environment in use.
     *
     * @var EnvironmentInterface
     */
    protected $environment;

    /**
     * The model for which widgets shall be generated.
     *
     * @var ModelInterface
     */
    protected $model;

    /**
     * The translator.
     *
     * @var TranslatorInterface
     */
    protected TranslatorInterface $translator;

    /**
     * Create a new instance.
     *
     * @param EnvironmentInterface $environment The environment in use.
     * @param ModelInterface       $model       The model for which widgets shall be generated.
     */
    public function __construct(EnvironmentInterface $environment, ModelInterface $model)
    {
        $this->environment = $environment;
        $this->model       = $model;
        $framework         = System::getContainer()->get('contao.framework');
        assert($framework instanceof ContaoFramework);
        $translator = System::getContainer()->get('translator');
        assert($translator instanceof TranslatorInterface);

        $this->framework  = $framework;
        $this->translator = $translator;
    }

    /**
     * Encode a value from the widget to native data of the data provider via event.
     *
     * @param string                    $property       The property.
     * @param mixed                     $value          The value of the property.
     * @param PropertyValueBagInterface $propertyValues The property value bag the property value originates from.
     *
     * @return mixed
     */
    public function encodeValue($property, $value, PropertyValueBagInterface $propertyValues)
    {
        $environment = $this->getEnvironment();
        $dispatcher  = $environment->getEventDispatcher();
        assert($dispatcher instanceof EventDispatcherInterface);

        $event = new EncodePropertyValueFromWidgetEvent($environment, $this->model, $propertyValues);
        $event
            ->setProperty($property)
            ->setValue($value);

        $dispatcher->dispatch($event, EncodePropertyValueFromWidgetEvent::NAME);

        return $event->getValue();
    }

    /**
     * Decode a value from native data of the data provider to the widget via event.
     *
     * @param string $property The property.
     * @param mixed  $value    The value of the property.
     *
     * @return mixed
     */
    public function decodeValue($property, $value)
    {
        $environment = $this->getEnvironment();
        $dispatcher  = $environment->getEventDispatcher();
        assert($dispatcher instanceof EventDispatcherInterface);

        $event = new DecodePropertyValueForWidgetEvent($environment, $this->model);
        $event
            ->setProperty($property)
            ->setValue($value);
        $dispatcher->dispatch($event, DecodePropertyValueForWidgetEvent::NAME);

        return $event->getValue();
    }

    /**
     * @return EnvironmentInterface
     */
    public function getEnvironment()
    {
        return $this->environment;
    }

    /**
     * @param string $property
     *
     * @return bool
     */
    public function hasWidget($property)
    {
        try {
            return ($this->getWidget($property) instanceof Widget);
            // @codingStandardsIgnoreStart
        } catch (\Exception $e) {
            // Fall though and return false.
        }
        // @codingStandardsIgnoreEnd
        return false;
    }

    /**
     * Function for pre-loading the tiny mce.
     *
     * @param string $buffer The rendered widget as string.
     * @param Widget $widget The widget.
     *
     * @return string The widget.
     */
    public function loadRichTextEditor($buffer, Widget $widget)
    {
        /** @psalm-suppress UndefinedMagicPropertyFetch */
        $rte = $widget->rte;
        if (
            (null === $rte)
            || ((0 !== (\strncmp($rte, 'tiny', 4)))
                && (0 !== \strncmp($rte, 'ace', 3)))
        ) {
            return $buffer;
        }

        /** @psalm-suppress InternalMethod - Class Adapter is internal, not the __call() method. Blame Contao. */
        $backendAdapter = $this->framework->getAdapter(Backend::class);
        /** @psalm-suppress InternalMethod - Class Adapter is internal, not the __call() method. Blame Contao. */
        $templateLoader = $this->framework->getAdapter(TemplateLoader::class);

        [$file, $type] = \explode('|', $rte) + ['', ''];

        $templateName = 'be_' . $file;
        // This test if the rich text editor template exist.
        $templateLoader->getPath($templateName, 'html5');

        $template = new ContaoBackendViewTemplate($templateName);
        $template
            ->set('selector', 'ctrl_' . $widget->id)
            ->set('type', $type)
            ->set('readonly', $widget->readonly);

        if (0 !== \strncmp($rte, 'tiny', 4)) {
            /** @deprecated Deprecated since Contao 4.0, to be removed in Contao 5.0 */
            $template->set('language', $backendAdapter->getTinyMceLanguage());
        }

        $buffer .= $template->parse();

        return $buffer;
    }

    /**
     * Get the unique id.
     *
     * @param string $propertyName The property name.
     *
     * @return string
     */
    protected function getUniqueId($propertyName)
    {
        $inputProvider  = $this->getEnvironment()->getInputProvider();
        assert($inputProvider instanceof InputProviderInterface);

        $sessionStorage = $this->getEnvironment()->getSessionStorage();
        assert($sessionStorage instanceof SessionStorageInterface);

        $selector = 'ctrl_' . $propertyName;

        if (
            ('select' !== $inputProvider->getParameter('act'))
            || (false === $inputProvider->hasValue('edit') && false === $inputProvider->hasValue('edit_save'))
        ) {
            return $selector;
        }

        $modelId = ModelId::fromModel($this->model);
        $fields  = $sessionStorage->get($modelId->getDataProviderName() . '.edit')['properties'];

        $fieldId = new ModelId('property.' . $modelId->getDataProviderName(), $propertyName);
        if (!\in_array($fieldId->getSerialized(), $fields)) {
            return $selector;
        }

        $selector = 'ctrl_' . \str_replace('::', '____', $modelId->getSerialized()) . '_' . $propertyName;

        return $selector;
    }

    /**
     * Retrieve the instance of a widget for the given property.
     *
     * @param string                    $property    Name of the property for which the widget shall be retrieved.
     * @param PropertyValueBagInterface $inputValues The input values to use (optional) (RAW widget value format).
     *
     * @return Widget|null
     *
     * @throws DcGeneralRuntimeException         When No widget could be built.
     * @throws DcGeneralInvalidArgumentException When property is not defined in the property definitions.
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    public function getWidget($property, PropertyValueBagInterface $inputValues = null)
    {
        $environment = $this->getEnvironment();
        $definition  = $environment->getDataDefinition();
        assert($definition instanceof ContainerInterface);


        $propertyDefinitions = $definition->getPropertiesDefinition();

        if (!$propertyDefinitions->hasProperty($property)) {
            throw new DcGeneralInvalidArgumentException(
                'Property ' . $property . ' is not defined in propertyDefinitions.'
            );
        }

        $model = clone $this->model;
        $model->setId($this->model->getId());

        if ($inputValues) {
            $controller = $environment->getController();
            assert($controller instanceof ControllerInterface);

            $values = new PropertyValueBag();
            foreach ($inputValues->getIterator() as $propertyName => $propertyValue) {
                $values->setPropertyValue(
                    $propertyName,
                    $this->encodeValue($propertyName, $propertyValue, $inputValues)
                );
            }

            $controller->updateModelFromPropertyBag($model, $values);
        }

        $event = new BuildWidgetEvent($environment, $model, $propertyDefinitions->getProperty($property));

        $dispatcher = $environment->getEventDispatcher();
        assert($dispatcher instanceof EventDispatcherInterface);

        $dispatcher->dispatch($event, $event::NAME);

        return $event->getWidget();
    }

    /**
     * Build the date picker string.
     *
     * @param Widget $objWidget The widget instance to generate the date picker string for.
     *
     * @return string
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    protected function buildDatePicker($objWidget)
    {
        $strFormat = $GLOBALS['TL_CONFIG'][$objWidget->rgxp . 'Format'];

        switch ($objWidget->rgxp) {
            case 'datim':
                $time = ",\n      timePicker:true";
                break;

            case 'time':
                $time = ",\n      pickOnly:\"time\"";
                break;

            default:
                $time = '';
        }

        return 'new Picker.Date($$("#ctrl_' . $objWidget->id . '"), {
            draggable:false,
            toggle:$$("#toggle_' . $objWidget->id . '"),
            format:"' . Date::formatToJs($strFormat) . '",
            positionOffset:{x:-197,y:-182}' . $time . ',
            pickerClass:"datepicker_bootstrap",
            useFadeInOut:!Browser.ie,
            startDay:' . $this->translator->trans('weekOffset', [], 'dc-general') . ',
            titleFormat:"' . $this->translator->trans('titleFormat', [], 'dc-general') . '"
        });';
    }

    /**
     * Generate the help msg for a property.
     *
     * @param string $property The name of the property.
     *
     * @return string
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    protected function generateHelpText($property, Widget $widget)
    {
        $definition = $this->getEnvironment()->getDataDefinition();
        assert($definition instanceof ContainerInterface);

        $widgetType = $definition->getPropertiesDefinition()->getProperty($property)->getWidgetType();
        if (('password' === $widgetType) || !Config::get('showHelp')) {
            return '';
        }
        /** @psalm-suppress UndefinedMagicPropertyFetch */
        $label = (string) (
            $widget->description
            // see vendor/contao/core-bundle/src/Resources/contao/classes/DataContainer.php:817; method help();
            ?: $this->translator->trans($property . '.description', [], $definition->getName())
        );

        return '<p class="tl_help tl_tip">' . $label . '</p>';
    }

    /**
     * Render the widget for the named property.
     *
     * @param string                    $property     The name of the property for which the widget shall be rendered.
     * @param bool                      $ignoreErrors Flag if the error property of the widget shall get
     *                                                cleared prior rendering.
     * @param PropertyValueBagInterface $inputValues  The input values to use (optional) (RAW widget value format).
     *
     * @return string
     *
     * @throws DcGeneralRuntimeException For unknown properties.
     */
    public function renderWidget($property, $ignoreErrors = false, PropertyValueBagInterface $inputValues = null)
    {
        /** @var Widget $widget */
        $widget = $this->getWidget($property, $inputValues);

        $this->cleanErrors($widget, $ignoreErrors);
        $this->widgetAddError($property, $widget, $inputValues, $ignoreErrors);

        $definition = $this->getEnvironment()->getDataDefinition();
        assert($definition instanceof ContainerInterface);

        $propInfo = $definition->getPropertiesDefinition()->getProperty($property);

        /** @psalm-suppress UndefinedMagicPropertyFetch */
        $isHideInput = (bool) $widget->hideInput;

        $hiddenFields = ($isHideInput) ? $this->buildHiddenFields($widget->value, $widget->name) : null;

        /** @psalm-suppress UndefinedMagicPropertyFetch */
        $content = (new ContaoBackendViewTemplate('dcbe_general_field'))
            ->set('strName', $property)
            ->set('strClass', $widget->tl_class)
            ->set('widget', $isHideInput ? null : $widget->parse())
            ->set('hasErrors', $isHideInput ? null : $widget->hasErrors())
            ->set('strDatepicker', $isHideInput ? null : $this->getDatePicker($propInfo->getExtra(), $widget))
            // We used the var blnUpdate before.
            ->set('blnUpdate', false)
            ->set('strHelp', $isHideInput ? '' : $this->generateHelpText($property, $widget))
            ->set('strId', $widget->id)
            ->set('isHideInput', $isHideInput)
            ->set('hiddenName', $widget->name)
            ->set('value', $widget->value)
            ->set('hiddenFields', $hiddenFields)
            // See: \ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\EditMask::buildFieldSet
            ->set('disabled', $propInfo->getExtra()['readonly'] ?? false)
            ->parse();

        return $this->loadRichTextEditor($content, $widget);
    }

    /**
     * Build the hidden fields.
     * This return an array with field name and their value.
     *
     * @param string|array $value        The property value.
     * @param string       $propertyName The property name.
     *
     * @return array
     */
    public function buildHiddenFields($value, string $propertyName): array
    {
        if (\is_string($value)) {
            return [$propertyName => $value];
        }

        $values = [[]];
        foreach ($value as $key => $item) {
            $values[] = $this->buildHiddenFields($item, $propertyName . '[' . $key . ']');
        }

        return \array_merge(...$values);
    }

    /**
     * Process RAW input values.
     *
     * @param PropertyValueBag $propertyValues The RAW property values from the input provider.
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    public function processInput(PropertyValueBag $propertyValues): void
    {
        // @codingStandardsIgnoreStart - Remember current POST data and clear it.
        $post  = $_POST;
        $_POST = [];
        // @codingStandardsIgnoreEnd
        Input::resetCache();

        // Set all POST data, these get used within the Widget::validate() method.
        foreach ($propertyValues as $property => $propertyValue) {
            Input::setPost($property, $propertyValue);
        }

        // Now get and validate the widgets.
        $encodedValues = new PropertyValueBag();
        foreach (\array_keys($propertyValues->getArrayCopy()) as $property) {
            // NOTE: the passed input values are RAW DATA from the input provider - aka widget known values and not
            // native data as in the model.
            // Therefore, we do not need to decode them but MUST encode them.
            $widget = $this->getWidget($property, $propertyValues);
            assert($widget instanceof Widget);

            $widget->validate();

            if ($widget->hasErrors()) {
                foreach ($widget->getErrors() as $error) {
                    $propertyValues->markPropertyValueAsInvalid($property, $error);
                }
            } elseif ($widget->submitInput()) {
                try {
                    $encodedValues->setPropertyValue(
                        $property,
                        $this->encodeValue($property, $widget->value, $propertyValues)
                    );
                } catch (\Exception $exception) {
                    $widget->addError($exception->getMessage());
                    foreach ($widget->getErrors() as $error) {
                        $propertyValues->markPropertyValueAsInvalid($property, $error);
                    }
                }
            }
        }
        foreach ($encodedValues->getArrayCopy() as $propertyName => $propertyValue) {
            $propertyValues->setPropertyValue($propertyName, $propertyValue);
        }

        $_POST = $post;
        Input::resetCache();
    }

    /**
     * {@inheritDoc}
     */
    public function processErrors(PropertyValueBag $propertyValues): void
    {
        $propertyErrors = $propertyValues->getInvalidPropertyErrors();

        if (!$propertyErrors) {
            return;
        }

        $dispatcher = $this->getEnvironment()->getEventDispatcher();
        assert($dispatcher instanceof EventDispatcherInterface);

        foreach ($propertyErrors as $property => $errors) {
            $widget = $this->getWidget($property);
            assert($widget instanceof Widget);

            foreach ($errors as $error) {
                $event = new ResolveWidgetErrorMessageEvent($this->getEnvironment(), $error);
                $dispatcher->dispatch($event, ResolveWidgetErrorMessageEvent::NAME);
                $widget->addError($event->getError());
            }
        }
    }

    /**
     * Clean errors for widget.
     *
     * @param Widget $widget       The widget.
     * @param bool   $ignoreErrors The flag for errors cleared.
     *
     * @return void
     *
     * @throws \ReflectionException
     */
    protected function cleanErrors(Widget $widget, $ignoreErrors = false)
    {
        if (!$ignoreErrors) {
            return;
        }

        // Clean the errors array and fix up the CSS class.
        $reflectionPropError = new \ReflectionProperty(\get_class($widget), 'arrErrors');
        $reflectionPropError->setAccessible(true);
        $reflectionPropError->setValue($widget, []);

        $reflectionPropClass = new \ReflectionProperty(\get_class($widget), 'strClass');
        $reflectionPropClass->setAccessible(true);
        $reflectionPropClass->setValue($widget, \str_replace('error', '', $reflectionPropClass->getValue($widget)));
    }

    /**
     * Widget add error.
     *
     * @param string                         $property     The property.
     * @param Widget                         $widget       The widget.
     * @param PropertyValueBagInterface|null $inputValues  The input values.
     * @param bool                           $ignoreErrors The for add error.
     *
     * @return void
     */
    protected function widgetAddError(
        $property,
        Widget $widget,
        PropertyValueBagInterface $inputValues = null,
        $ignoreErrors = false
    ) {
        if (
            !(!$ignoreErrors && $inputValues && $inputValues->hasPropertyValue($property)
              && $inputValues->isPropertyValueInvalid($property))
        ) {
            return;
        }

        foreach ($inputValues->getPropertyValueErrors($property) as $error) {
            $widget->addError($error);
        }
    }

    /**
     * Get the date picker, if the widget has one.
     *
     * @param array  $propExtra The extra data from the property.
     * @param Widget $widget    The widget.
     *
     * @return string
     */
    protected function getDatePicker(array $propExtra, Widget $widget)
    {
        if (!empty($propExtra['datepicker'])) {
            return $this->buildDatePicker($widget);
        }

        return '';
    }
}
