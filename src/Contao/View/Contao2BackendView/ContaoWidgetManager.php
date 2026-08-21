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
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Tristan Lins <tristan.lins@bit3.de>
 * @author     Christopher Boelter <christopher@boelter.eu>
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     Richard Henkenjohann <richardhenkenjohann@googlemail.com>
 * @copyright  2013-2026 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView;

use Contao\Backend;
use Contao\BackendTemplate;
use Contao\Config;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\Date;
use Contao\Input;
use Contao\System;
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
use ContaoCommunityAlliance\Contao\Bindings\ContaoEvents;
use ContaoCommunityAlliance\Contao\Bindings\Events\Image\GenerateHtmlEvent;
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
 *
 * @api
 */
class ContaoWidgetManager
{
    /**
     * The environment in use.
     *
     * @var ContaoFramework
     */
    protected ContaoFramework $framework;

    /**
     * The environment in use.
     *
     * @var EnvironmentInterface
     */
    protected EnvironmentInterface $environment;

    /**
     * The model for which widgets shall be generated.
     *
     * @var ModelInterface
     */
    protected ModelInterface $model;

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
    /**
     * The model carrying the input values of the current run, see modelWithInput().
     *
     * @var ModelInterface|null
     */
    private ?ModelInterface $inputModel = null;

    /**
     * The key the cached input model was built for.
     *
     * @var string|null
     */
    private ?string $inputModelKey = null;

    /**
     * Messages from exceptions caught while encoding a submitted value, keyed by property name.
     *
     * processInput() and renderWidget() build a fresh Widget instance each, so an error added to
     * the former is gone by the time the latter renders. cleanErrors() then also wipes whatever
     * survived, once per auto submit. Without remembering the message here, a disturbance while
     * encoding (a broken event listener, not a validation constraint) simply disappears instead
     * of ever reaching the editor - see contao-community-alliance/dc-general#100.
     *
     * @var array<string, list<string>>
     */
    private array $fatalErrors = [];

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
            // phpcs:disable
        } catch (\Exception $e) {
            // Fall though and return false.
        }
        // phpcs:enable
        return false;
    }

    /**
     * Function for pre-loading the tiny mce.
     *
     * @param string $buffer The rendered widget as string.
     * @param Widget $widget The widget.
     *
     * @return string The widget.
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     *
     * @psalm-suppress UndefinedMagicPropertyAssignment
     */
    public function loadRichTextEditor($buffer, Widget $widget)
    {
        /**
         * @psalm-suppress UndefinedMagicPropertyFetch
         * @var mixed $rte
         */
        $rte = $widget->rte;
        if (null === $rte) {
            return $buffer;
        }
        // Contao DCA allows "ace|sql" syntax to pass the highlight type via pipe.
        [$rteBase, $rteHighlight] = \explode('|', (string) $rte, 2) + [null, null];
        $rteHighlight = $rteHighlight ?? '';

        if (!str_starts_with($rteBase, 'tiny') && !str_starts_with($rteBase, 'ace')) {
            return $buffer;
        }

        /** @psalm-suppress InternalMethod - Class Adapter is internal, not the __call() method. Blame Contao. */
        $backendAdapter = $this->framework->getAdapter(Backend::class);
        $fileBrowserTypes = [];
        $pickerBuilder = System::getContainer()->get('contao.picker.builder');
        assert($pickerBuilder instanceof \Contao\CoreBundle\Picker\PickerBuilderInterface);
        foreach (['file' => 'image', 'link' => 'file'] as $context => $fileBrowserType) {
            if ($pickerBuilder->supportsContext($context)) {
                $fileBrowserTypes[] = $fileBrowserType;
            }
        }
        $definition = $this->getEnvironment()->getDataDefinition();
        assert($definition instanceof ContainerInterface);
        $propExtra = $definition->getPropertiesDefinition()->hasProperty($widget->id)
            ? $definition->getPropertiesDefinition()->getProperty($widget->id)->getExtra()
            : [];

        $template = new BackendTemplate('be_' . $rteBase);
        $template->selector = 'ctrl_' . $widget->id;
        $template->fileBrowserTypes = implode(' ', $fileBrowserTypes);
        // FIXME: Contao sets this as table.id while dcg uses table::id - Problem?
        $template->source = ModelId::fromModel($this->model)->getSerialized();
        /**
         * Contao widget class does not ensure that the property is set and of type bool.
         * @psalm-suppress RedundantCastGivenDocblockType
         * @psalm-suppress RedundantConditionGivenDocblockType
         * @psalm-suppress DocblockTypeContradiction
         * @psalm-suppress RedundantCondition
         * @psalm-suppress TypeDoesNotContainNull
         */
        $template->readonly = (bool) ($widget->readonly ?? false);
        $template->theme = $backendAdapter->getTheme();
        $template->enableAce = $GLOBALS['TL_CONFIG']['useCE'] ?? false;
        $template->aceType = $backendAdapter->getAceType($rteHighlight);
        $template->enableTinyMce = $GLOBALS['TL_CONFIG']['useRTE'] ?? false;
        $template->tinyMceLanguage = $backendAdapter->getTinyMceLanguage();
        $template->rows = (int) ($propExtra['rows'] ?? 0);

        return $buffer . $template->parse();
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
        $editSession = (array) $sessionStorage->get($modelId->getDataProviderName() . '.edit');
        $fields      = (array) ($editSession['properties'] ?? []);

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
     * @param string                         $property    Name of the property for which the widget shall be retrieved.
     * @param PropertyValueBagInterface|null $inputValues The input values to use (optional) (RAW widget value format).
     *
     * @return Widget|null
     *
     * @throws DcGeneralRuntimeException         When No widget could be built.
     * @throws DcGeneralInvalidArgumentException When property is not defined in the property definitions.
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     * @SuppressWarnings(PHPMD.EmptyCatchBlock)
     */
    public function getWidget($property, ?PropertyValueBagInterface $inputValues = null)
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

        $model = null === $inputValues
            ? $this->cloneModel($this->model)
            : $this->cloneModel($this->modelWithInput($inputValues));

        $event = new BuildWidgetEvent($environment, $model, $propertyDefinitions->getProperty($property));

        $dispatcher = $environment->getEventDispatcher();
        assert($dispatcher instanceof EventDispatcherInterface);

        $dispatcher->dispatch($event, $event::NAME);

        return $event->getWidget();
    }

    /**
     * Build the model that carries all passed input values - once per set of values.
     *
     * Every widget has to see the input of *all* fields, not just its own: display conditions and
     * dependent selects are evaluated against the other properties. That is why the model is rebuilt
     * from the whole bag rather than from a single value.
     *
     * Doing so per widget made the work quadratic. getWidget() is called once per property, and each
     * call encoded every value and wrote it to a fresh clone - with n properties that is n² encode
     * events and n² setProperty calls, and every setProperty converts the value again. Measured on a
     * mask with 27 widgets: 1323 encode calls and 1349 setProperty calls for one save.
     *
     * The values are the same for all widgets of one run, so the model is built once and cached. The
     * cache key covers the contents of the bag, so a changed value rebuilds it.
     *
     * @param PropertyValueBagInterface $inputValues The input values.
     *
     * @return ModelInterface
     */
    private function modelWithInput(PropertyValueBagInterface $inputValues): ModelInterface
    {
        $key = $this->inputCacheKey($inputValues);
        if (null !== $key && null !== $this->inputModel && $key === $this->inputModelKey) {
            return $this->inputModel;
        }

        $environment = $this->getEnvironment();
        $controller  = $environment->getController();
        assert($controller instanceof ControllerInterface);

        $model = $this->cloneModel($this->model);

        $values = new PropertyValueBag();
        foreach ($inputValues->getIterator() as $propertyName => $propertyValue) {
            try {
                $values->setPropertyValue(
                    $propertyName,
                    $this->encodeValue($propertyName, $propertyValue, $inputValues)
                );
            } catch (\Exception $e) {
                // A value that cannot be encoded is left out of the model, exactly as before this
                // was extracted - the widget then falls back to the stored value.
                continue;
            }
        }

        $controller->updateModelFromPropertyBag($model, $values);

        $this->inputModel    = $model;
        $this->inputModelKey = $key;

        return $model;
    }

    /**
     * Clone a model and keep its id - DefaultModel::__clone() drops it on purpose.
     *
     * @param ModelInterface $model The model to copy.
     *
     * @return ModelInterface
     */
    private function cloneModel(ModelInterface $model): ModelInterface
    {
        $copy = clone $model;
        $copy->setId($model->getId());

        return $copy;
    }

    /**
     * Build a cache key over the contents of the value bag, or null when they cannot be hashed.
     *
     * @param PropertyValueBagInterface $inputValues The input values.
     *
     * @return string|null
     */
    private function inputCacheKey(PropertyValueBagInterface $inputValues): ?string
    {
        try {
            return \md5(\serialize($inputValues->getArrayCopy()));
        } catch (\Throwable) {
            // Not hashable - fall back to rebuilding, which is what happened before anyway.
            return null;
        }
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
        /** @psalm-suppress MixedArrayAccess The Contao superglobal $GLOBALS['TL_CONFIG'] is untyped. */
        $strFormat = (string) $GLOBALS['TL_CONFIG'][(string) $objWidget->rgxp . 'Format'];

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

        // Picker.Date is still MooTools based in Contao 5.7 and has no vanilla counterpart, but the element
        // lookup does not need MooTools - see docs/mootools-removal.md.
        return 'new Picker.Date(document.getElementById("ctrl_' . $objWidget->id . '"), {
            draggable:false,
            toggle:document.getElementById("toggle_' . $objWidget->id . '"),
            format:"' . (string) Date::formatToJs($strFormat) . '",
            positionOffset:{x:-197,y:-182}' . $time . ',
            pickerClass:"datepicker_bootstrap",
            useFadeInOut:true,
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
     * @param string $property                             The name of the property for which the widget shall be
     *                                                     rendered.
     * @param bool                           $ignoreErrors Flag if the error property of the widget shall get
     *                                                     cleared prior rendering.
     * @param PropertyValueBagInterface|null $inputValues  The input values to use (optional) (RAW widget value format).
     *
     * @return string
     *
     * @throws DcGeneralRuntimeException For unknown properties.
     */
    public function renderWidget($property, $ignoreErrors = false, ?PropertyValueBagInterface $inputValues = null)
    {
        /** @var Widget $widget */
        $widget = $this->getWidget($property, $inputValues);

        $this->cleanErrors($widget, $ignoreErrors, $property);
        $this->widgetAddError($property, $widget, $inputValues, $ignoreErrors);

        $definition = $this->getEnvironment()->getDataDefinition();
        assert($definition instanceof ContainerInterface);

        $propInfo = $definition->getPropertiesDefinition()->getProperty($property);

        /** @psalm-suppress UndefinedMagicPropertyFetch */
        $isHideInput = (bool) $widget->hideInput;

        /** @var string|array<array-key, mixed> $widgetValue */
        $widgetValue  = $widget->value;
        $hiddenFields = ($isHideInput) ? $this->buildHiddenFields($widgetValue, $widget->name) : null;

        /** @psalm-suppress UndefinedMagicPropertyFetch */
        $content = (new ContaoBackendViewTemplate('dcbe_general_field'))
            ->set('strName', $property)
            ->set('strClass', $widget->tl_class)
            ->set('isColorPicker', (bool) ($propInfo->getExtra()['colorpicker'] ?? false))
            ->set('widget', $isHideInput ? null : $widget->parse())
            ->set('hasErrors', $isHideInput ? null : $widget->hasErrors())
            ->set('strDatepicker', $isHideInput ? null : $this->getDatePicker($propInfo->getExtra(), $widget))
            ->set('datepickerIcon', $this->renderDatePickerIcon($widget->id))
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
            if (!\is_string($item) && !\is_array($item)) {
                continue;
            }
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
        // phpcs:disable - Remember current POST data and clear it.
        $post  = $_POST;
        $_POST = [];
        // phpcs:enable
        Input::resetCache();

        // Set all POST data, these get used within the Widget::validate() method.
        foreach ($propertyValues as $property => $propertyValue) {
            Input::setPost($property, $propertyValue);
        }
        unset($property, $propertyValue);

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
                /** @var list<string> $widgetErrors */
                $widgetErrors = $widget->getErrors();
                foreach ($widgetErrors as $error) {
                    $propertyValues->markPropertyValueAsInvalid($property, $error);
                }
            } elseif ($widget->submitInput()) {
                try {
                    $encodedValues->setPropertyValue(
                        $property,
                        $this->encodeValue($property, $widget->value, $propertyValues)
                    );
                } catch (\Exception $exception) {
                    // Not the editor's doing - remember it so an auto submit render pass does not
                    // wipe it away below, see cleanErrors().
                    $this->fatalErrors[$property][] = $exception->getMessage();

                    $widget->addError($exception->getMessage());
                    /** @var list<string> $widgetErrors */
                    $widgetErrors = $widget->getErrors();
                    foreach ($widgetErrors as $error) {
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
        /** @var array<string, list<string>> $propertyErrors */
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
                $widget->addError((string) $event->getError());
            }
        }
    }

    /**
     * Clean errors for widget.
     *
     * @param Widget      $widget       The widget.
     * @param bool        $ignoreErrors The flag for errors cleared.
     * @param string|null $property     The property the widget belongs to, to re-apply a fatal
     *                                  error recorded by processInput() (optional).
     *
     * @return void
     *
     * @throws \ReflectionException
     */
    protected function cleanErrors(Widget $widget, $ignoreErrors = false, ?string $property = null)
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
        $reflectionPropClass->setValue(
            $widget,
            \str_replace('error', '', (string) $reflectionPropClass->getValue($widget))
        );

        // A disturbance while encoding is not a constraint an editor can fix by re-entering the
        // same value - unlike Widget::validate()'s errors, it must survive an auto submit.
        foreach ((null !== $property ? $this->fatalErrors[$property] ?? [] : []) as $error) {
            $widget->addError($error);
        }
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
        ?PropertyValueBagInterface $inputValues = null,
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

    /**
     * Render the icon that opens the date picker.
     *
     * Through the image event rather than assembled by hand: only there does Contao get to look
     * for a "--dark" companion and take the size from the file, and only there does the tooltip
     * find its target. Contao core renders the very same icon this way.
     *
     * @param string $widgetId The id of the widget the picker belongs to.
     *
     * @return string
     */
    private function renderDatePickerIcon(string $widgetId): string
    {
        $dispatcher = $this->getEnvironment()->getEventDispatcher();
        assert($dispatcher instanceof EventDispatcherInterface);

        $event = new GenerateHtmlEvent(
            'assets/datepicker/images/icon.svg',
            $this->translator->trans('MSC.datepicker', [], 'contao_default'),
            'id="toggle_' . $widgetId . '" style="cursor:pointer" data-contao--tooltips-target="tooltip"'
        );
        $dispatcher->dispatch($event, ContaoEvents::IMAGE_GET_HTML);

        return $event->getHtml() ?? '';
    }
}
