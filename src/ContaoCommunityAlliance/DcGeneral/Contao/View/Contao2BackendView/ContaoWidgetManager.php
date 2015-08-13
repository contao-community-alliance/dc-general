<?php
/**
 * PHP version 5
 * @package    generalDriver
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Tristan Lins <tristan.lins@bit3.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView;

use ContaoCommunityAlliance\Contao\Bindings\ContaoEvents;
use ContaoCommunityAlliance\Contao\Bindings\Events\Backend\AddToUrlEvent;
use ContaoCommunityAlliance\Contao\Bindings\Events\Image\GenerateHtmlEvent;
use ContaoCommunityAlliance\Contao\Bindings\Events\Widget\GetAttributesFromDcaEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\Compatibility\DcCompat;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\BuildWidgetEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\DecodePropertyValueForWidgetEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\EncodePropertyValueFromWidgetEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetPropertyOptionsEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\ManipulateWidgetEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\ResolveWidgetErrorMessageEvent;
use ContaoCommunityAlliance\DcGeneral\Data\ModelInterface;
use ContaoCommunityAlliance\DcGeneral\Data\PropertyValueBag;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\Properties\PropertyInterface;
use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;
use ContaoCommunityAlliance\DcGeneral\Exception\DcGeneralInvalidArgumentException;
use ContaoCommunityAlliance\DcGeneral\Exception\DcGeneralRuntimeException;

/**
 * Class ContaoWidgetManager.
 *
 * This class is responsible for creating widgets and processing data through them.
 *
 * @package DcGeneral\Contao\View\Contao2BackendView
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
	 * @param string $property The property.
	 *
	 * @param mixed  $value    The value of the property.
	 *
	 * @return mixed
	 */
	public function encodeValue($property, $value)
	{
		$environment = $this->getEnvironment();

		$event = new EncodePropertyValueFromWidgetEvent($environment, $this->model);
		$event
			->setProperty($property)
			->setValue($value);

		$environment->getEventPropagator()->propagate(
			$event::NAME,
			$event,
			array(
				$environment->getDataDefinition()->getName(),
				$property
			)
		);

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

		$environment->getEventPropagator()->propagate(
			$event::NAME,
			$event,
			array(
				$environment->getDataDefinition()->getName(),
				$property
			)
		);

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
		try
		{
			return $this->getWidget($property) !== null;
		}
		catch(\Exception $e)
		{
			// Fall though and return false.
		}
		return false;
	}

	/**
	 * Get special labels.
	 *
	 * @param PropertyInterface $propInfo The property for which the X label shall be generated.
	 *
	 * @return string
	 */
	protected function getXLabel($propInfo)
	{
		$strXLabel   = '';
		$environment = $this->getEnvironment();
		$defName     = $environment->getDataDefinition()->getName();
		$translator  = $environment->getTranslator();

		// Toggle line wrap (textarea).
		if ($propInfo->getWidgetType() === 'textarea' && !array_key_exists('rte', $propInfo->getExtra()))
		{
			$event = new GenerateHtmlEvent(
				'wrap.gif',
				$translator->translate('wordWrap', 'MSC'),
				sprintf(
					'title="%s" class="toggleWrap" onclick="Backend.toggleWrap(\'ctrl_%s\');"',
					specialchars($translator->translate('wordWrap', 'MSC')),
					$propInfo->getName()
				)
			);

			$environment->getEventPropagator()->propagate(ContaoEvents::IMAGE_GET_HTML, $event);

			$strXLabel .= ' ' . $event->getHtml();
		}

		// Add the help wizard.
		if ($propInfo->getExtra() && array_key_exists('helpwizard', $propInfo->getExtra()))
		{
			$event = new GenerateHtmlEvent(
				'about.gif',
				$translator->translate('helpWizard', 'MSC'),
				'style="vertical-align:text-bottom;"'
			);

			$environment->getEventPropagator()->propagate(ContaoEvents::IMAGE_GET_HTML, $event);

			$strXLabel .= sprintf(
				' <a href="contao/help.php?table=%s&amp;field=%s"
				title="%s"
				onclick="Backend.openWindow(this, 600, 500); return false;">%s</a>',
				$defName,
				$propInfo->getName(),
				specialchars($translator->translate('helpWizard', 'MSC')),
				$event->getHtml()
			);
		}

		// Add the popup file manager.
		if ($propInfo->getWidgetType() === 'fileTree')
		{
			// In Contao 3 it is always a file picker - no need for the button.
			if (version_compare(VERSION, '3.0', '<'))
			{
				$event = new GenerateHtmlEvent(
					'filemanager.gif',
					$translator->translate('fileManager', 'MSC'),
					'style="vertical-align:text-bottom;"'
				);

				$environment->getEventPropagator()->propagate(ContaoEvents::IMAGE_GET_HTML, $event);

				$strXLabel .= sprintf(
					' <a href="contao/files.php"
					title="%s"
					onclick="Backend.getScrollOffset(); Backend.openWindow(this, 750, 500); return false;">%s</a>',
					specialchars($translator->translate('fileManager', 'MSC')),
					$event->getHtml()
				);
			}
		}
		// Add table import wizard.
		elseif ($propInfo->getWidgetType() === 'tableWizard')
		{
			$urlEvent = new AddToUrlEvent('key=table');

			$importTableEvent = new GenerateHtmlEvent(
				'tablewizard.gif',
				$translator->translate('importTable.0', $defName),
				'style="vertical-align:text-bottom;"'
			);

			$shrinkEvent = new GenerateHtmlEvent(
				'demagnify.gif',
				$translator->translate('shrink.0', $defName),
				sprintf(
					'title="%s" style="vertical-align:text-bottom; cursor:pointer;" onclick="Backend.tableWizardResize(0.9);"',
					specialchars($translator->translate('shrink.1', $defName))
				)
			);

			$expandEvent = new GenerateHtmlEvent(
				'magnify.gif',
				$translator->translate('expand.0', $defName),
				sprintf(
					'title="%s" style="vertical-align:text-bottom; cursor:pointer;" onclick="Backend.tableWizardResize(1.1);"',
					specialchars($translator->translate('expand.1', $defName))
				)
			);

			$environment->getEventPropagator()->propagate(ContaoEvents::BACKEND_ADD_TO_URL, $urlEvent);

			$environment->getEventPropagator()->propagate(ContaoEvents::IMAGE_GET_HTML, $importTableEvent);
			$environment->getEventPropagator()->propagate(ContaoEvents::IMAGE_GET_HTML, $shrinkEvent);
			$environment->getEventPropagator()->propagate(ContaoEvents::IMAGE_GET_HTML, $expandEvent);

			$strXLabel .= sprintf(
				' <a href="%s" title="%s" onclick="Backend.getScrollOffset();">%s</a> %s%s',
				ampersand($urlEvent->getUrl()),
				specialchars($translator->translate('importTable.1', $defName)),
				$importTableEvent->getHtml(),
				$shrinkEvent->getHtml(),
				$expandEvent->getHtml()
			);
		}
		// Add list import wizard.
		elseif ($propInfo->getWidgetType() === 'listWizard')
		{
			$urlEvent = new AddToUrlEvent('key=list');

			$importListEvent = new GenerateHtmlEvent(
				'tablewizard.gif',
				$translator->translate('importList.0', $defName),
				'style="vertical-align:text-bottom;"'
			);

			$environment->getEventPropagator()->propagate(ContaoEvents::BACKEND_ADD_TO_URL, $urlEvent);
			$environment->getEventPropagator()->propagate(ContaoEvents::IMAGE_GET_HTML, $importListEvent);

			$strXLabel .= sprintf(
				' <a href="%s" title="%s" onclick="Backend.getScrollOffset();">%s</a>',
				ampersand($urlEvent->getUrl()),
				specialchars($translator->translate('importList.1', $defName)),
				$importListEvent->getHtml()
			);
		}

		return $strXLabel;
	}

	/**
	 * Function for pre-loading the tiny mce.
	 *
	 * @return void
	 *
	 * @throws \Exception When the rich text editor config file can not be found.
	 */
	public function preLoadRichTextEditor()
	{
		foreach ($this->getEnvironment()->getDataDefinition()->getPropertiesDefinition()->getProperties() as $property)
		{
			/** @var PropertyInterface $property */
			$extra = $property->getExtra();

			if (!isset($extra['rte']))
			{
				continue;
			}

			if (strncmp($extra['rte'], 'tiny', 4) !== 0)
			{
				continue;
			}

			list($file, $type) = explode('|', $extra['rte']);

			$propertyId = 'ctrl_' . $property->getName();

			if (version_compare(VERSION, '3.3', '<'))
			{
				$GLOBALS['TL_RTE'][$file][$propertyId] = array(
					'id'   => $propertyId,
					'file' => $file,
					'type' => $type
				);
			}
			else {
				if (!file_exists(TL_ROOT . '/system/config/' . $file . '.php'))
				{
					throw new \Exception(sprintf('Cannot find editor configuration file "%s.php"', $file));
				}

				// Backwards compatibility.
				$language = substr($GLOBALS['TL_LANGUAGE'], 0, 2);

				if (!file_exists(TL_ROOT . '/assets/tinymce/langs/' . $language . '.js'))
				{
					$language = 'en';
				}

				$selector = $propertyId;

				ob_start();
				// @codingStandardsIgnoreStart
				include TL_ROOT . '/system/config/' . $file . '.php';
				// @codingStandardsIgnoreEnd
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
	 * @throws DcGeneralInvalidArgumentException When an unknown property has been passed.
	 *
	 * @return \Widget
	 */
	public function getWidget($property, PropertyValueBag $inputValues = null)
	{
		$environment         = $this->getEnvironment();
		$defName             = $environment->getDataDefinition()->getName();
		$propertyDefinitions = $environment->getDataDefinition()->getPropertiesDefinition();

		if (!$propertyDefinitions->hasProperty($property))
		{
			throw new DcGeneralInvalidArgumentException('Property ' . $property . ' is not defined in propertyDefinitions.');
		}

		$event = new BuildWidgetEvent($environment, $this->model, $propertyDefinitions->getProperty($property));

		$environment->getEventPropagator()->propagate(
			$event::NAME,
			$event,
			array(
				$defName,
				$property
			)
		);

		if ($event->getWidget())
		{
			return $event->getWidget();
		}

		$propInfo  = $propertyDefinitions->getProperty($property);
		$propExtra = $propInfo->getExtra();
		$varValue  = $this->decodeValue($property, $this->model->getProperty($property));
		$xLabel    = $this->getXLabel($propInfo);

		$strClass = $GLOBALS['BE_FFL'][$propInfo->getWidgetType()];
		if (!class_exists($strClass))
		{
			return null;
		}

		// FIXME TEMPORARY WORKAROUND! To be fixed in the core: Controller::prepareForWidget(..).
		if (in_array($propExtra['rgxp'], array('date', 'time', 'datim'))
			&& !$propExtra['mandatory']
			&& is_numeric($varValue) && $varValue == 0)
		{
			$varValue = '';
		}

		// OH: why not $required = $mandatory always? source: DataContainer 226.
		// OH: the whole prepareForWidget(..) thing is an only mess
		// Widgets should parse the configuration by themselves, depending on what they need.
		$propExtra['required'] = ($varValue == '') && $propExtra['mandatory'];

		if ($inputValues)
		{
			$model = clone $this->model;
			$model->setId($this->model->getId());
			$this->environment->getController()->updateModelFromPropertyBag($model, $inputValues);
		}
		else {
			$model = $this->model;
		}

		$options = $propInfo->getOptions();
		$event   = new GetPropertyOptionsEvent($environment, $model);
		$event->setPropertyName($property);
		$event->setOptions($options);
		$environment->getEventPropagator()->propagate(
			$event::NAME,
			$event,
			$environment->getDataDefinition()->getName(),
			$property
		);

		if ($event->getOptions() !== $options)
		{
			$options = $event->getOptions();
		}

		$arrConfig = array(
			'inputType' => $propInfo->getWidgetType(),
			'label' => array(
				$propInfo->getLabel(),
				$propInfo->getDescription()
			),
			'options' => $options,
			'eval' => $propExtra,
			// TODO: populate these.
			// 'foreignKey' => null
		);


		if (isset($propExtra['reference']))
		{
			$arrConfig['reference'] = $propExtra['reference'];
		}

		$event = new GetAttributesFromDcaEvent(
			$arrConfig,
			$propInfo->getName(),
			$varValue,
			$property,
			$defName,
			new DcCompat($environment, $this->model, $property)
		);

		$environment->getEventPropagator()->propagate(
			ContaoEvents::WIDGET_GET_ATTRIBUTES_FROM_DCA,
			$event,
			$environment->getDataDefinition()->getName(),
			$property
		);

		$arrPrepared = $event->getResult();

		// Bugfix CS: ajax subpalettes are really broken.
		// Therefore we reset to the default checkbox behaviour here and submit the entire form.
		// This way, the javascript needed by the widget (wizards) will be correctly evaluated.
		if ($arrConfig['inputType'] == 'checkbox'
			&& is_array($GLOBALS['TL_DCA'][$defName]['subpalettes'])
			&& in_array($property, array_keys($GLOBALS['TL_DCA'][$defName]['subpalettes']))
			&& $arrConfig['eval']['submitOnChange']
		)
		{
			$arrPrepared['onclick'] = $arrConfig['eval']['submitOnChange'] ? "Backend.autoSubmit('".$defName."')" : '';
		}

		$objWidget = new $strClass($arrPrepared, new DcCompat($environment, $this->model, $property));
		// OH: what is this? source: DataContainer 232.
		$objWidget->currentRecord = $this->model->getId();

		$objWidget->wizard .= $xLabel;

		$event = new ManipulateWidgetEvent($environment, $this->model, $propInfo, $objWidget);
		$environment->getEventPropagator()->propagate(
			$event::NAME,
			$event,
			array(
				$defName,
				$property
			)
		);

		return $objWidget;
	}

	/**
	 * Build the date picker string.
	 *
	 * @param \Contao\Widget $objWidget The widget instance to generate the date picker string for.
	 *
	 * @return string
	 */
	protected function buildDatePicker($objWidget)
	{
		$translator = $this->getEnvironment()->getTranslator();
		// TODO: need better interface to Contao Config class here.
		$strFormat = $GLOBALS['TL_CONFIG'][$objWidget->rgxp . 'Format'];

		$arrConfig = array(
			'allowEmpty' => true,
			'toggleElements' => '#toggle_' . $objWidget->id,
			'pickerClass' => 'datepicker_dashboard',
			'format' => $strFormat,
			'inputOutputFormat' => $strFormat,
			'positionOffset' => array(
				'x' => 130,
				'y' => -185
			),
			'startDay' => $translator->translate('weekOffset', 'MSC'),
			'days' => array_values((array)$translator->translate('DAYS', 'MSC')),
			'dayShort' => $translator->translate('dayShortLength', 'MSC'),
			'months' => array_values((array)$translator->translate('MONTHS', 'MSC')),
			'monthShort' => $translator->translate('monthShortLength', 'MSC')
		);

		switch ($objWidget->rgxp)
		{
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
			|| (version_compare(DATEPICKER, '2.0', '>') && version_compare(VERSION, '3.1', '>=')))
		{
			return 'new Picker.Date($$("#ctrl_' . $objWidget->id . '"), {
				draggable:false,
				toggle:$$("#toggle_' . $objWidget->id . '"),
				format:"' . \Date::formatToJs($strFormat) . '",
				positionOffset:{x:-197,y:-182}' . $time . ',
				pickerClass:"datepicker_dashboard",
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
	 */
	protected function generateHelpText($property)
	{
		$environment = $this->getEnvironment();
		$propInfo    = $environment->getDataDefinition()->getPropertiesDefinition()->getProperty($property);
		$label       = $propInfo->getDescription();
		$widgetType  = $propInfo->getWidgetType();

		// TODO: need better interface to Contao Config class here.
		if (!$GLOBALS['TL_CONFIG']['showHelp'] || $widgetType == 'password' || !strlen($label))
		{
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
		if (!$widget)
		{
			throw new DcGeneralRuntimeException('No widget for property ' . $property);
		}

		if ($ignoreErrors)
		{
			// Clean the errors array and fix up the CSS class.
			$reflection = new \ReflectionProperty(get_class($widget), 'arrErrors');
			$reflection->setAccessible(true);
			$reflection->setValue($widget, array());
			$reflection = new \ReflectionProperty(get_class($widget), 'strClass');
			$reflection->setAccessible(true);
			$reflection->setValue($widget, str_replace('error', '', $reflection->getValue($widget)));
		}
		else
		{
			if ($inputValues && $inputValues->hasPropertyValue($property) && $inputValues->isPropertyValueInvalid($property))
			{
				foreach ($inputValues->getPropertyValueErrors($property) as $error)
				{
					$widget->addError($error);
				}
			}
		}

		$strDatePicker = '';
		if (isset($propExtra['datepicker']))
		{
			$strDatePicker = $this->buildDatePicker($widget);
		}

		$objTemplateFoo = new ContaoBackendViewTemplate('dcbe_general_field');
		$objTemplateFoo->setData(array(
			'strName'       => $property,
			'strClass'      => $propExtra['tl_class'],
			'widget'        => $widget->parse(),
			'hasErrors'     => $widget->hasErrors(),
			'strDatepicker' => $strDatePicker,
			// TODO: need 'update' value - (\Input::get('act') == 'overrideAll' && ($arrData['inputType'] == 'checkbox' || $arrData['inputType'] == 'checkboxWizard') && $arrData['eval']['multiple'])
			'blnUpdate'     => false, // $blnUpdate,
			'strHelp'       => $this->generateHelpText($property),
			'strId'         => $widget->id
		));

		$buffer = $objTemplateFoo->parse();

		if (isset($propExtra['rte']) && strncmp($propExtra['rte'], 'tiny', 4) === 0)
		{
			$propertyId = 'ctrl_' . $property;

			$buffer .= <<<EOF
<script>tinyMCE.execCommand('mceAddControl', false, '{$propertyId}');$('{$propertyId}').erase('required');</script>
EOF;
		}

		return $buffer;
	}

	/**
	 * {@inheritDoc}
	 */
	public function processInput(PropertyValueBag $propertyValues)
	{
		// reset all inputs these get used within the Widget::validate() method.
		foreach ($propertyValues as $property => $propertyValue)
		{
			\Input::setPost($property, $propertyValue);
		}

		// now get and validate the widgets
		foreach (array_keys($propertyValues->getArrayCopy()) as $property)
		{
			// NOTE: the passed input values are RAW DATA from the input provider - aka widget known values and not
			// native data as in the model.
			// Therefore we do not need to decode them but MUST encode them.

			$widget = $this->getWidget($property, $propertyValues);
			$widget->validate();

			if ($widget->hasErrors())
			{
				foreach ($widget->getErrors() as $error)
				{
					$propertyValues->markPropertyValueAsInvalid($property, $error);
				}
			}
			else
			{
				try {
					$propertyValues->setPropertyValue($property, $this->encodeValue($property, $widget->value));
				}
				catch (\Exception $e) {
					$widget->addError($e->getMessage());
					$propertyValues->markPropertyValueAsInvalid($property, $e->getMessage());
				}
			}
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function processErrors(PropertyValueBag $propertyValues)
	{
		$propertyErrors = $propertyValues->getInvalidPropertyErrors();
		$definitionName = $this->getEnvironment()->getDataDefinition()->getName();

		if ($propertyErrors)
		{
			$propagator = $this->getEnvironment()->getEventPropagator();

			foreach ($propertyErrors as $property => $errors)
			{
				$widget = $this->getWidget($property);

				foreach ($errors as $error)
				{
					$event = new ResolveWidgetErrorMessageEvent($this->getEnvironment(), $error);
					$propagator->propagate(
						$event::NAME,
						$event,
						array(
							$definitionName,
							$property
						)
					);

					$widget->addError($event->getError());
				}
			}
		}
	}
}
