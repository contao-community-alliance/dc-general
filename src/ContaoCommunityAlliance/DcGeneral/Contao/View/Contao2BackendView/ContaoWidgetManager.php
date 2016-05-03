<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2015 Contao Community Alliance.
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
 * @copyright  2013-2015 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView;

use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\BuildWidgetEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\DecodePropertyValueForWidgetEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\EncodePropertyValueFromWidgetEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\ResolveWidgetErrorMessageEvent;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\Properties\PropertyInterface;
use ContaoCommunityAlliance\DcGeneral\Data\ModelInterface;
use ContaoCommunityAlliance\DcGeneral\Data\PropertyValueBag;
use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;
use ContaoCommunityAlliance\DcGeneral\Exception\DcGeneralInvalidArgumentException;
use ContaoCommunityAlliance\DcGeneral\Exception\DcGeneralRuntimeException;

/**
 * Class ContaoWidgetManager.
 *
 * This class is responsible for creating widgets and processing data through them.
 */
class ContaoWidgetManager
{
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
     * Create a new instance.
     *
     * @param EnvironmentInterface $environment The environment in use.
     *
     * @param ModelInterface       $model       The model for which widgets shall be generated.
     */
    public function __construct(EnvironmentInterface $environment, ModelInterface $model)
    {
        $this->environment = $environment;
        $this->model       = $model;

        $this->preLoadRichTextEditor();
    }

    /**
     * Encode a value from the widget to native data of the data provider via event.
     *
     * @param string           $property       The property.
     *
     * @param mixed            $value          The value of the property.
     *
     * @param PropertyValueBag $propertyValues The property value bag the property value originates from.
     *
     * @return mixed
     */
    public function encodeValue($property, $value, PropertyValueBag $propertyValues)
    {
        $environment = $this->getEnvironment();

        $event = new EncodePropertyValueFromWidgetEvent($environment, $this->model, $propertyValues);
        $event
            ->setProperty($property)
            ->setValue($value);

        $environment->getEventDispatcher()->dispatch(EncodePropertyValueFromWidgetEvent::NAME, $event);

        return $event->getValue();
    }

    /**
     * Decode a value from native data of the data provider to the widget via event.
     *
     * @param string $property The property.
     *
     * @param mixed  $value    The value of the property.
     *
     * @return mixed
     */
    public function decodeValue($property, $value)
    {
        $environment = $this->getEnvironment();

        $event = new DecodePropertyValueForWidgetEvent($environment, $this->model);
        $event
            ->setProperty($property)
            ->setValue($value);
        $environment->getEventDispatcher()->dispatch(EncodePropertyValueFromWidgetEvent::NAME, $event);

        return $event->getValue();
    }

    /**
     * {@inheritdoc}
     */
    public function getEnvironment()
    {
        return $this->environment;
    }

    /**
     * {@inheritDoc}
     */
    public function hasWidget($property)
    {
        try {
            return $this->getWidget($property) !== null;
        } catch (\Exception $e) {
            // Fall though and return false.
        }
        return false;
    }

    /**
     * Function for pre-loading the tiny mce.
     *
     * @return void
     *
     * @throws \Exception When the rich text editor config file can not be found.
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    public function preLoadRichTextEditor()
    {
        foreach ($this->getEnvironment()->getDataDefinition()->getPropertiesDefinition()->getProperties(
        ) as $property) {
            /** @var PropertyInterface $property */
            $extra = $property->getExtra();

            if (!isset($extra['rte'])) {
                continue;
            }

            if (strncmp($extra['rte'], 'tiny', 4) !== 0) {
                continue;
            }

            list($file, $type) = explode('|', $extra['rte']);

            $selector = 'ctrl_' . $property->getName();

            if (version_compare(VERSION, '3.3', '<')) {
                $GLOBALS['TL_RTE'][$file][$selector] = array(
                    'id'   => $selector,
                    'file' => $file,
                    'type' => $type
                );
            } else {
                if (!file_exists(TL_ROOT . '/system/config/' . $file . '.php')) {
                    throw new \Exception(sprintf('Cannot find editor configuration file "%s.php"', $file));
                }

                // Backwards compatibility.
                $language = substr($GLOBALS['TL_LANGUAGE'], 0, 2);

                // Keep $language and $selector here, they are used in the included template file! See #76.
                if (!file_exists(TL_ROOT . '/assets/tinymce/langs/' . $language . '.js')) {
                    $language = 'en';
                }

                ob_start();
                include TL_ROOT . '/system/config/' . $file . '.php';
                $updateMode = ob_get_contents();
                ob_end_clean();

                $GLOBALS['TL_MOOTOOLS'][] = $updateMode;
            }
        }
    }

    /**
     * Retrieve the instance of a widget for the given property.
     *
     * @param string           $property    Name of the property for which the widget shall be retrieved.
     *
     * @param PropertyValueBag $inputValues The input values to use (optional).
     *
     * @return \Widget
     *
     * @throws DcGeneralRuntimeException         When No widget could be build.
     * @throws DcGeneralInvalidArgumentException When property is not defined in the property definitions.
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    public function getWidget($property, PropertyValueBag $inputValues = null)
    {
        $environment         = $this->getEnvironment();
        $dispatcher          = $environment->getEventDispatcher();
        $propertyDefinitions = $environment->getDataDefinition()->getPropertiesDefinition();

        if (!$propertyDefinitions->hasProperty($property)) {
            throw new DcGeneralInvalidArgumentException(
                'Property ' . $property . ' is not defined in propertyDefinitions.'
            );
        }

        $model = clone $this->model;
        $model->setId($this->model->getId());

        if ($inputValues) {
            $values = new PropertyValueBag($inputValues->getArrayCopy());
            $this->environment->getController()->updateModelFromPropertyBag($model, $values);
        }

        $propertyDefinition = $propertyDefinitions->getProperty($property);
        $event              = new BuildWidgetEvent($environment, $model, $propertyDefinition);

        $dispatcher->dispatch($event::NAME, $event);
        if (!$event->getWidget()) {
            throw new DcGeneralRuntimeException(
                sprintf('Widget was not build for property %s::%s.', $this->model->getProviderName(), $property)
            );
        }

        return $event->getWidget();
    }

    /**
     * Build the date picker string.
     *
     * @param \Contao\Widget $objWidget The widget instance to generate the date picker string for.
     *
     * @return string
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    protected function buildDatePicker($objWidget)
    {
        $translator = $this->getEnvironment()->getTranslator();
        $strFormat  = $GLOBALS['TL_CONFIG'][$objWidget->rgxp . 'Format'];

        $arrConfig = array(
            'allowEmpty'        => true,
            'toggleElements'    => '#toggle_' . $objWidget->id,
            'pickerClass'       => 'datepicker_dashboard',
            'format'            => $strFormat,
            'inputOutputFormat' => $strFormat,
            'positionOffset'    => array(
                'x' => 130,
                'y' => -185
            ),
            'startDay'          => $translator->translate('weekOffset', 'MSC'),
            'days'              => array_values((array) $translator->translate('DAYS', 'MSC')),
            'dayShort'          => $translator->translate('dayShortLength', 'MSC'),
            'months'            => array_values((array) $translator->translate('MONTHS', 'MSC')),
            'monthShort'        => $translator->translate('monthShortLength', 'MSC')
        );

        switch ($objWidget->rgxp) {
            case 'datim':
                $arrConfig['timePicker'] = true;

                $time = ",\n      timePicker:true";
                break;

            case 'time':
                $arrConfig['timePickerOnly'] = true;

                $time = ",\n      pickOnly:\"time\"";
                break;
            default:
                $time = '';
        }

        if ((version_compare(DATEPICKER, '2.1', '>') && version_compare(VERSION, '3.1', '<'))
            || (version_compare(DATEPICKER, '2.0', '>') && version_compare(VERSION, '3.1', '>='))
        ) {
            return 'new Picker.Date($$("#ctrl_' . $objWidget->id . '"), {
                draggable:false,
                toggle:$$("#toggle_' . $objWidget->id . '"),
                format:"' . \Date::formatToJs($strFormat) . '",
                positionOffset:{x:-197,y:-182}' . $time . ',
                pickerClass:"' . (
                    version_compare(VERSION, '3.3', '>=')
                        ? 'datepicker_bootstrap'
                        : 'datepicker_dashboard'
                ) . '",
                useFadeInOut:!Browser.ie,
                startDay:' . $translator->translate('weekOffset', 'MSC') . ',
                titleFormat:"' . $translator->translate('titleFormat', 'MSC') . '"
            });';
        }

        return 'new DatePicker(' . json_encode('#ctrl_' . $objWidget->id) . ', ' . json_encode($arrConfig) . ');';
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
    protected function generateHelpText($property)
    {
        $environment = $this->getEnvironment();
        $propInfo    = $environment->getDataDefinition()->getPropertiesDefinition()->getProperty($property);
        $label       = $propInfo->getDescription();
        $widgetType  = $propInfo->getWidgetType();

        if (!$GLOBALS['TL_CONFIG']['showHelp'] || $widgetType == 'password' || !strlen($label)) {
            return '';
        }

        return '<p class="tl_help tl_tip">' . $label . '</p>';
    }

    /**
     * Render the widget for the named property.
     *
     * @param string           $property     The name of the property for which the widget shall be rendered.
     *
     * @param bool             $ignoreErrors Flag if the error property of the widget shall get cleared prior rendering.
     *
     * @param PropertyValueBag $inputValues  The input values to use (optional).
     *
     * @return string
     *
     * @throws DcGeneralRuntimeException For unknown properties.
     */
    public function renderWidget($property, $ignoreErrors = false, PropertyValueBag $inputValues = null)
    {
        $environment         = $this->getEnvironment();
        $definition          = $environment->getDataDefinition();
        $propertyDefinitions = $definition->getPropertiesDefinition();
        $propInfo            = $propertyDefinitions->getProperty($property);
        $propExtra           = $propInfo->getExtra();
        $widget              = $this->getWidget($property, $inputValues);

        /** @var \Contao\Widget $widget */
        if (!$widget) {
            throw new DcGeneralRuntimeException('No widget for property ' . $property);
        }

        if ($ignoreErrors) {
            // Clean the errors array and fix up the CSS class.
            $reflection = new \ReflectionProperty(get_class($widget), 'arrErrors');
            $reflection->setAccessible(true);
            $reflection->setValue($widget, array());
            $reflection = new \ReflectionProperty(get_class($widget), 'strClass');
            $reflection->setAccessible(true);
            $reflection->setValue($widget, str_replace('error', '', $reflection->getValue($widget)));
        } else {
            if ($inputValues && $inputValues->hasPropertyValue($property)
                && $inputValues->isPropertyValueInvalid($property)
            ) {
                foreach ($inputValues->getPropertyValueErrors($property) as $error) {
                    $widget->addError($error);
                }
            }
        }

        $strDatePicker = '';
        if (isset($propExtra['datepicker'])) {
            $strDatePicker = $this->buildDatePicker($widget);
        }

        $objTemplateFoo = new ContaoBackendViewTemplate('dcbe_general_field');
        $objTemplateFoo->setData(
            array(
                'strName'       => $property,
                'strClass'      => $widget->tl_class,
                'widget'        => $widget->parse(),
                'hasErrors'     => $widget->hasErrors(),
                'strDatepicker' => $strDatePicker,
                // We used the var blnUpdate before.
                'blnUpdate'     => false,
                'strHelp'       => $this->generateHelpText($property),
                'strId'         => $widget->id
            )
        );

        $buffer = $objTemplateFoo->parse();

        if (isset($propExtra['rte']) && strncmp($propExtra['rte'], 'tiny', 4) === 0) {
            $propertyId = 'ctrl_' . $property;

            $buffer .= <<<EOF
<script>tinyMCE.execCommand('mceAddControl', false, '{$propertyId}');$('{$propertyId}').erase('required');</script>
EOF;
        }

        return $buffer;
    }

    /**
     * {@inheritDoc}
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    public function processInput(PropertyValueBag $propertyValues)
    {
        // @codingStandardsIgnoreStart - Remember current POST data and clear it.
        $post  = $_POST;
        $_POST = array();
        // @codingStandardsIgnoreEnd
        \Input::resetCache();

        // Set all POST data, these get used within the Widget::validate() method.
        foreach ($propertyValues as $property => $propertyValue) {
            $_POST[$property] = $propertyValue;
        }

        // Now get and validate the widgets.
        foreach (array_keys($propertyValues->getArrayCopy()) as $property) {
            // NOTE: the passed input values are RAW DATA from the input provider - aka widget known values and not
            // native data as in the model.
            // Therefore we do not need to decode them but MUST encode them.
            $widget = $this->getWidget($property, $propertyValues);
            $widget->validate();

            if ($widget->hasErrors()) {
                foreach ($widget->getErrors() as $error) {
                    $propertyValues->markPropertyValueAsInvalid($property, $error);
                }
            } elseif ($widget->submitInput()) {
                try {
                    $propertyValues->setPropertyValue(
                        $property,
                        $this->encodeValue($property, $widget->value, $propertyValues)
                    );
                } catch (\Exception $e) {
                    $widget->addError($e->getMessage());
                    foreach ($widget->getErrors() as $error) {
                        $propertyValues->markPropertyValueAsInvalid($property, $error);
                    }
                }
            }
        }

        $_POST = $post;
        \Input::resetCache();
    }

    /**
     * {@inheritDoc}
     */
    public function processErrors(PropertyValueBag $propertyValues)
    {
        $propertyErrors = $propertyValues->getInvalidPropertyErrors();

        if ($propertyErrors) {
            $dispatcher = $this->getEnvironment()->getEventDispatcher();

            foreach ($propertyErrors as $property => $errors) {
                $widget = $this->getWidget($property);

                foreach ($errors as $error) {
                    $event = new ResolveWidgetErrorMessageEvent($this->getEnvironment(), $error);
                    $dispatcher->dispatch(ResolveWidgetErrorMessageEvent::NAME, $event);
                    $widget->addError($event->getError());
                }
            }
        }
    }
}
