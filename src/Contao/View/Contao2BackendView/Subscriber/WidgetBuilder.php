<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2022 Contao Community Alliance.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    contao-community-alliance/dc-general
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     Richard Henkenjohann <richardhenkenjohann@googlemail.com>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2013-2022 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Subscriber;

use Contao\CheckBox;
use Contao\StringUtil;
use Contao\Widget;
use ContaoCommunityAlliance\Contao\Bindings\ContaoEvents;
use ContaoCommunityAlliance\Contao\Bindings\Events\Backend\AddToUrlEvent;
use ContaoCommunityAlliance\Contao\Bindings\Events\Image\GenerateHtmlEvent;
use ContaoCommunityAlliance\Contao\Bindings\Events\Widget\GetAttributesFromDcaEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\Compatibility\DcCompat;
use ContaoCommunityAlliance\DcGeneral\Contao\RequestScopeDeterminator;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\BuildWidgetEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\DecodePropertyValueForWidgetEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetPropertyOptionsEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\ManipulateWidgetEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Widget\FileTree;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Widget\FileTreeOrder;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Widget\PageTree;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Widget\PageTreeOrder;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Widget\TreePickerOrder;
use ContaoCommunityAlliance\DcGeneral\Data\ModelInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\Properties\PropertyInterface;
use ContaoCommunityAlliance\DcGeneral\EnvironmentAwareInterface;
use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;
use ContaoCommunityAlliance\DcGeneral\Exception\DcGeneralRuntimeException;

/**
 * Widget Builder build Contao backend widgets.
 *
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class WidgetBuilder implements EnvironmentAwareInterface
{
    /**
     * The request mode determinator.
     *
     * @var RequestScopeDeterminator
     */
    private static $scopeDeterminator;

    /**
     * The environment.
     *
     * @var EnvironmentInterface
     */
    private $environment;

    /**
     * Mapping list of widget types where the DC General has it own widgets.
     *
     * @var array
     */
    protected static $widgetMapping = [
        'fileTree'        => FileTree::class,
        'fileTreeOrder'   => FileTreeOrder::class,
        'pageTree'        => PageTree::class,
        'pageTreeOrder'   => PageTreeOrder::class,
        'treePickerOrder' => TreePickerOrder::class
    ];

    /**
     * Construct.
     *
     * @param EnvironmentInterface          $environment       The environment.
     *
     * @param RequestScopeDeterminator|null $scopeDeterminator The request mode determinator.
     */
    public function __construct(EnvironmentInterface $environment, RequestScopeDeterminator $scopeDeterminator = null)
    {
        $this->environment = $environment;

        if (null !== $scopeDeterminator) {
            static::$scopeDeterminator = $scopeDeterminator;
        }
    }

    /**
     * Handle the build widget event.
     *
     * @param BuildWidgetEvent $event The event.
     *
     * @return void
     */
    public static function handleEvent(BuildWidgetEvent $event)
    {
        if ($event->getWidget() || !static::$scopeDeterminator->currentScopeIsBackend()) {
            return;
        }

        $event
            ->setWidget((new static($event->getEnvironment()))->buildWidget($event->getProperty(), $event->getModel()));
    }

    /**
     * Retrieve the environment.
     *
     * @return EnvironmentInterface
     */
    public function getEnvironment()
    {
        return $this->environment;
    }

    /**
     * Try to resolve the class name for the widget.
     *
     * @param PropertyInterface $property The property to get the widget class name for.
     *
     * @return string
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    protected function getWidgetClass(PropertyInterface $property)
    {
        if (isset(static::$widgetMapping[$property->getWidgetType()])) {
            return static::$widgetMapping[$property->getWidgetType()];
        }

        if (!isset($GLOBALS['BE_FFL'][$property->getWidgetType()])) {
            return null;
        }

        $className = $GLOBALS['BE_FFL'][$property->getWidgetType()];
        if (!\class_exists($className)) {
            return null;
        }

        return $className;
    }

    /**
     * Get special labels.
     *
     * @param PropertyInterface $propInfo The property for which the X label shall be generated.
     * @param ModelInterface    $model    The model.
     *
     * @return array
     */
    protected function getOptionsForWidget($propInfo, $model): ?array
    {
        if (!$this->isGetOptionsAllowed($propInfo)) {
            return null;
        }

        $environment = $this->getEnvironment();
        $options     = $propInfo->getOptions();
        $event       = new GetPropertyOptionsEvent($environment, $model);
        $event->setPropertyName($propInfo->getName());
        $event->setOptions($options);
        $environment->getEventDispatcher()->dispatch($event, GetPropertyOptionsEvent::NAME);

        if ($event->getOptions() !== $options) {
            return $event->getOptions();
        }

        return $options;
    }

    /**
     * Check if the current widget is allowed to get options.
     *
     * @param PropertyInterface $property The bag with all information.
     *
     * @return bool True => allowed to get options | False => doesn't get options.
     */
    private function isGetOptionsAllowed(PropertyInterface $property): bool
    {
        $propExtra = $property->getExtra();

        // Check the overwrite param.
        if (
            \is_array($propExtra)
            && \array_key_exists('fetchOptions', $propExtra)
            && (true === $propExtra['fetchOptions'])
        ) {
            return true;
        }

        // Check the class.
        if ('checkbox' !== $property->getWidgetType()) {
            return true;
        }

        // Check if multiple is active.
        return \array_key_exists('multiple', $propExtra) && (true === $propExtra['multiple']);
    }

    /**
     * Get the table import wizard.
     *
     * @return string
     */
    protected function getTableWizard()
    {
        $environment = $this->getEnvironment();
        $dispatcher  = $environment->getEventDispatcher();
        $defName     = $environment->getDataDefinition()->getName();
        $translator  = $environment->getTranslator();
        $urlEvent    = new AddToUrlEvent('key=table');

        $importTableEvent = new GenerateHtmlEvent(
            'tablewizard.svg',
            $translator->translate('importTable.0', $defName),
            'style="vertical-align:text-bottom;"'
        );

        $shrinkEvent = new GenerateHtmlEvent(
            'demagnify.svg',
            $translator->translate('shrink.0', $defName),
            \sprintf(
                'title="%s" ' .
                'style="vertical-align:text-bottom; cursor:pointer;" ' .
                'onclick="Backend.tableWizardResize(0.9);"',
                StringUtil::specialchars($translator->translate('shrink.1', $defName))
            )
        );

        $expandEvent = new GenerateHtmlEvent(
            'magnify.svg',
            $translator->translate('expand.0', $defName),
            \sprintf(
                'title="%s" ' .
                'style="vertical-align:text-bottom; cursor:pointer;" ' .
                'onclick="Backend.tableWizardResize(1.1);"',
                StringUtil::specialchars($translator->translate('expand.1', $defName))
            )
        );

        $dispatcher->dispatch($urlEvent, ContaoEvents::BACKEND_ADD_TO_URL);
        $dispatcher->dispatch($importTableEvent, ContaoEvents::IMAGE_GET_HTML);
        $dispatcher->dispatch($shrinkEvent, ContaoEvents::IMAGE_GET_HTML);
        $dispatcher->dispatch($expandEvent, ContaoEvents::IMAGE_GET_HTML);

        return \sprintf(
            ' <a href="%s" title="%s" onclick="Backend.getScrollOffset();">%s</a> %s%s',
            \ampersand($urlEvent->getUrl()),
            StringUtil::specialchars($translator->translate('importTable.1', $defName)),
            $importTableEvent->getHtml(),
            $shrinkEvent->getHtml(),
            $expandEvent->getHtml()
        );
    }

    /**
     * Get the list import wizard.
     *
     * @return string
     */
    protected function getListWizard()
    {
        $environment = $this->getEnvironment();
        $dispatcher  = $environment->getEventDispatcher();
        $defName     = $environment->getDataDefinition()->getName();
        $translator  = $environment->getTranslator();

        $urlEvent = new AddToUrlEvent('key=list');

        $importListEvent = new GenerateHtmlEvent(
            'tablewizard.svg',
            $translator->translate('importList.0', $defName),
            'style="vertical-align:text-bottom;"'
        );

        $dispatcher->dispatch($urlEvent, ContaoEvents::BACKEND_ADD_TO_URL);
        $dispatcher->dispatch($importListEvent, ContaoEvents::IMAGE_GET_HTML);

        return \sprintf(
            ' <a href="%s" title="%s" onclick="Backend.getScrollOffset();">%s</a>',
            \ampersand($urlEvent->getUrl()),
            StringUtil::specialchars($translator->translate('importList.1', $defName)),
            $importListEvent->getHtml()
        );
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
        $xLabel      = '';
        $environment = $this->getEnvironment();
        $translator  = $environment->getTranslator();

        // Toggle line wrap (textarea).
        if (('textarea' === $propInfo->getWidgetType()) && !\array_key_exists('rte', $propInfo->getExtra())) {
            $event = new GenerateHtmlEvent(
                'wrap.svg',
                $translator->translate('wordWrap', 'MSC'),
                \sprintf(
                    'title="%s" class="toggleWrap" onclick="Backend.toggleWrap(\'ctrl_%s\');"',
                    StringUtil::specialchars($translator->translate('wordWrap', 'MSC')),
                    $propInfo->getName()
                )
            );

            $environment->getEventDispatcher()->dispatch($event, ContaoEvents::IMAGE_GET_HTML);

            $xLabel .= ' ' . $event->getHtml();
        }

        $xLabel .= $this->getHelpWizard($propInfo);

        switch ($propInfo->getWidgetType()) {
            case 'tableWizard':
                $xLabel .= $this->getTableWizard();
                break;
            case 'listWizard':
                $xLabel .= $this->getListWizard();
                break;
            default:
        }

        return $xLabel;
    }

    /**
     * Get the help wizard.
     *
     * @param PropertyInterface $propInfo The property for which the wizard shall be generated.
     *
     * @return string
     */
    protected function getHelpWizard($propInfo)
    {
        $helpWizard  = '';
        $environment = $this->getEnvironment();
        $translator  = $environment->getTranslator();
        // Add the help wizard.
        if ($propInfo->getExtra() && \array_key_exists('helpwizard', $propInfo->getExtra())) {
            $event = new GenerateHtmlEvent(
                'about.svg',
                $translator->translate('helpWizard', 'MSC'),
                'style="vertical-align:text-bottom;"'
            );

            $environment->getEventDispatcher()->dispatch($event, ContaoEvents::IMAGE_GET_HTML);

            $helpWizard .= \sprintf(
                ' <a href="contao/help?table=%s&amp;field=%s" ' .
                'title="%s" ' .
                'onclick="Backend.openWindow(this, 600, 500); return false;">%s</a>',
                $environment->getDataDefinition()->getName(),
                $propInfo->getName(),
                StringUtil::specialchars($translator->translate('helpWizard', 'MSC')),
                $event->getHtml()
            );
        }

        return $helpWizard;
    }

    /**
     * Build a widget for a given property.
     *
     * @param PropertyInterface $property The property.
     * @param ModelInterface    $model    The current model.
     *
     * @return Widget
     *
     * @throws DcGeneralRuntimeException When not running in TL_MODE BE.
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    public function buildWidget(
        PropertyInterface $property,
        ModelInterface $model
    ) {
        if (
            static::$scopeDeterminator->currentScopeIsUnknown()
            || !static::$scopeDeterminator->currentScopeIsBackend()
        ) {
            throw new DcGeneralRuntimeException(
                \sprintf(
                    'WidgetBuilder only supports the backend mode. Running in mode "%s".',
                    static::$scopeDeterminator->currentScopeIsUnknown() ? 'unknown' : 'frontend'
                )
            );
        }

        $environment = $this->getEnvironment();
        $class       = $this->getWidgetClass($property);

        $prepareAttributes = $this->prepareWidgetAttributes($model, $property);
        $widget            = new $class($prepareAttributes, new DcCompat($environment, $model, $property->getName()));

        // OH: what is this? source: DataContainer 232.
        $widget->currentRecord = $model->getId();

        $widget->xlabel .= $this->getXLabel($property);

        $event = new ManipulateWidgetEvent($environment, $model, $property, $widget);
        $environment->getEventDispatcher()->dispatch($event, ManipulateWidgetEvent::NAME);

        return $widget;
    }

    /**
     * Decode the value for the widget.
     *
     * @param ModelInterface    $model    The model.
     * @param PropertyInterface $property The property name.
     *
     * @return mixed
     */
    private function valueToWidget(ModelInterface $model, PropertyInterface $property)
    {
        $environment = $this->getEnvironment();

        $event = new DecodePropertyValueForWidgetEvent($environment, $model);
        $event
            ->setProperty($property->getName())
            ->setValue($model->getProperty($property->getName()));

        $environment->getEventDispatcher()->dispatch($event, $event::NAME);
        $value = $event->getValue();

        $propExtra = $property->getExtra();

        if (
            (0 === (int) $value)
            && \is_numeric($value)
            && empty($propExtra['mandatory'])
            && (isset($propExtra['rgxp']) && \in_array($propExtra['rgxp'], ['date', 'time', 'datim']))
        ) {
            $value = '';
        }

        return $value;
    }

    /**
     * Prepare the attributes for the widget.
     *
     * @param ModelInterface    $model    The model.
     * @param PropertyInterface $property The property for the widget.
     *
     * @return array
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    private function prepareWidgetAttributes(ModelInterface $model, PropertyInterface $property)
    {
        $environment = $this->getEnvironment();
        $defName     = $environment->getDataDefinition()->getName();

        $propExtra = $property->getExtra();

        $value = $this->valueToWidget($model, $property);

        $propExtra['required'] = ('' === $value) && !empty($propExtra['mandatory']);

        $propExtra = $this->setPropExtraDisabled($property, $propExtra);

        $widgetConfig = [
            'inputType' => $property->getWidgetType(),
            'label'     => [
                $property->getLabel(),
                $property->getDescription()
            ],
            'options'   => $this->getOptionsForWidget($property, $model),
            'eval'      => $propExtra,
        ];

        if (isset($propExtra['reference'])) {
            $widgetConfig['reference'] = $propExtra['reference'];
        }

        $event = new GetAttributesFromDcaEvent(
            $widgetConfig,
            $property->getName(),
            $value,
            $property->getName(),
            $defName,
            new DcCompat($environment, $model, $property->getName())
        );

        $environment->getEventDispatcher()->dispatch($event, ContaoEvents::WIDGET_GET_ATTRIBUTES_FROM_DCA);
        $prepareAttributes = $event->getResult();

        if (
            ('checkbox' === $widgetConfig['inputType'])
            && isset($widgetConfig['eval']['submitOnChange'])
            && $widgetConfig['eval']['submitOnChange']
            && isset($GLOBALS['TL_DCA'][$defName]['subpalettes'])
            && \is_array($GLOBALS['TL_DCA'][$defName]['subpalettes'])
            && \array_key_exists($property->getName(), $GLOBALS['TL_DCA'][$defName]['subpalettes'])
        ) {
            // We have to override the onclick, do not append to it as Contao adds it's own code here in
            // Widget::getAttributesFromDca() which kills our sub palette handling!
            $prepareAttributes['onclick'] = "Backend.autoSubmit('" . $defName . "');";
        }

        return $prepareAttributes;
    }

    /**
     * Set "disabled" attribute for certain widgets being readonly.
     *
     * @param PropertyInterface $property  The property for the widget.
     * @param array             $propExtra The property extra.
     *
     * @return array
     */
    private function setPropExtraDisabled(PropertyInterface $property, array $propExtra): array
    {
        if (
            isset($propExtra['readonly'])
            && $propExtra['readonly']
            && \in_array($property->getWidgetType(), ['checkbox', 'select', 'radio'], true)
        ) {
            $propExtra['disabled'] = true;
            unset($propExtra['chosen']);
        }

        return $propExtra;
    }
}
