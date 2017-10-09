<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2017 Contao Community Alliance.
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
 * @copyright  2013-2017 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Subscriber;

use Contao\StringUtil;
use Contao\Widget;
use ContaoCommunityAlliance\Contao\Bindings\ContaoEvents;
use ContaoCommunityAlliance\Contao\Bindings\Events\Backend\AddToUrlEvent;
use ContaoCommunityAlliance\Contao\Bindings\Events\Image\GenerateHtmlEvent;
use ContaoCommunityAlliance\Contao\Bindings\Events\Widget\GetAttributesFromDcaEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\Compatibility\DcCompat;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\BuildWidgetEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\DecodePropertyValueForWidgetEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetPropertyOptionsEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\ManipulateWidgetEvent;
use ContaoCommunityAlliance\DcGeneral\Data\ModelInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\Properties\PropertyInterface;
use ContaoCommunityAlliance\DcGeneral\EnvironmentAwareInterface;
use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;
use ContaoCommunityAlliance\DcGeneral\Exception\DcGeneralRuntimeException;

/**
 * Widget Builder build Contao backend widgets.
 */
class WidgetBuilder implements EnvironmentAwareInterface
{
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
    protected $widgetMapping = array(
        'fileTree'      => 'ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Widget\FileTree',
        'fileTreeOrder' => 'ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Widget\FileTreeOrder',
        'pageTree'      => 'ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Widget\PageTree',
        'pageTreeOrder' => 'ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Widget\PageTreeOrder'
    );

    /**
     * Construct.
     *
     * @param EnvironmentInterface $environment The environment.
     */
    public function __construct(EnvironmentInterface $environment)
    {
        $this->environment = $environment;
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
        if ($event->getWidget() || TL_MODE !== 'BE') {
            return;
        }

        $builder = new static($event->getEnvironment());
        $widget  = $builder->buildWidget($event->getProperty(), $event->getModel());

        $event->setWidget($widget);
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
        if (isset($this->widgetMapping[$property->getWidgetType()])) {
            return $this->widgetMapping[$property->getWidgetType()];
        }

        if (!isset($GLOBALS['BE_FFL'][$property->getWidgetType()])) {
            return null;
        }

        $className = $GLOBALS['BE_FFL'][$property->getWidgetType()];
        if (!class_exists($className)) {
            return null;
        }

        return $className;
    }

    /**
     * Get special labels.
     *
     * @param PropertyInterface $propInfo The property for which the X label shall be generated.
     *
     * @param ModelInterface    $model    The model.
     *
     * @return string
     */
    protected function getOptionsForWidget($propInfo, $model)
    {
        if (!$this->isGetOptionsAllowed($propInfo)) {
            return null;
        }

        $environment = $this->getEnvironment();
        $dispatcher  = $environment->getEventDispatcher();
        $options     = $propInfo->getOptions();
        $event       = new GetPropertyOptionsEvent($environment, $model);
        $event->setPropertyName($propInfo->getName());
        $event->setOptions($options);
        $dispatcher->dispatch(GetPropertyOptionsEvent::NAME, $event);

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
     * @return bool True => allowed to get options | False => don't get options.
     */
    private function isGetOptionsAllowed(PropertyInterface $property)
    {
        $propExtra = $property->getExtra();
        $strClass  = $this->getWidgetClass($property);

        // Check the overwrite param.
        if (array_key_exists('fetchOptions', $propExtra) && (true === $propExtra['fetchOptions'])) {
            return true;
        }

        // Check the class.
        if ('CheckBox' !== $strClass) {
            return true;
        }

        // Check if multiple is active.
        if (array_key_exists('multiple', $propExtra) && (true === $propExtra['multiple'])) {
            return true;
        }

        return false;
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
            'tablewizard.gif',
            $translator->translate('importTable.0', $defName),
            'style="vertical-align:text-bottom;"'
        );

        $shrinkEvent = new GenerateHtmlEvent(
            'demagnify.gif',
            $translator->translate('shrink.0', $defName),
            sprintf(
                'title="%s" ' .
                'style="vertical-align:text-bottom; cursor:pointer;" ' .
                'onclick="Backend.tableWizardResize(0.9);"',
                StringUtil::specialchars($translator->translate('shrink.1', $defName))
            )
        );

        $expandEvent = new GenerateHtmlEvent(
            'magnify.gif',
            $translator->translate('expand.0', $defName),
            sprintf(
                'title="%s" ' .
                'style="vertical-align:text-bottom; cursor:pointer;" ' .
                'onclick="Backend.tableWizardResize(1.1);"',
                StringUtil::specialchars($translator->translate('expand.1', $defName))
            )
        );

        $dispatcher->dispatch(ContaoEvents::BACKEND_ADD_TO_URL, $urlEvent);

        $dispatcher->dispatch(ContaoEvents::IMAGE_GET_HTML, $importTableEvent);
        $dispatcher->dispatch(ContaoEvents::IMAGE_GET_HTML, $shrinkEvent);
        $dispatcher->dispatch(ContaoEvents::IMAGE_GET_HTML, $expandEvent);

        return sprintf(
            ' <a href="%s" title="%s" onclick="Backend.getScrollOffset();">%s</a> %s%s',
            ampersand($urlEvent->getUrl()),
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
            'tablewizard.gif',
            $translator->translate('importList.0', $defName),
            'style="vertical-align:text-bottom;"'
        );

        $dispatcher->dispatch(ContaoEvents::BACKEND_ADD_TO_URL, $urlEvent);
        $dispatcher->dispatch(ContaoEvents::IMAGE_GET_HTML, $importListEvent);

        return sprintf(
            ' <a href="%s" title="%s" onclick="Backend.getScrollOffset();">%s</a>',
            ampersand($urlEvent->getUrl()),
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
        $dispatcher  = $environment->getEventDispatcher();
        $translator  = $environment->getTranslator();

        // Toggle line wrap (textarea).
        if ($propInfo->getWidgetType() === 'textarea' && !array_key_exists('rte', $propInfo->getExtra())) {
            $event = new GenerateHtmlEvent(
                'wrap.gif',
                $translator->translate('wordWrap', 'MSC'),
                sprintf(
                    'title="%s" class="toggleWrap" onclick="Backend.toggleWrap(\'ctrl_%s\');"',
                    StringUtil::specialchars($translator->translate('wordWrap', 'MSC')),
                    $propInfo->getName()
                )
            );

            $dispatcher->dispatch(ContaoEvents::IMAGE_GET_HTML, $event);

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
        $dispatcher  = $environment->getEventDispatcher();
        $defName     = $environment->getDataDefinition()->getName();
        $translator  = $environment->getTranslator();
        // Add the help wizard.
        if ($propInfo->getExtra() && array_key_exists('helpwizard', $propInfo->getExtra())) {
            $event = new GenerateHtmlEvent(
                'about.gif',
                $translator->translate('helpWizard', 'MSC'),
                'style="vertical-align:text-bottom;"'
            );

            $dispatcher->dispatch(ContaoEvents::IMAGE_GET_HTML, $event);

            $helpWizard .= sprintf(
                ' <a href="contao/help.php?table=%s&amp;field=%s" ' .
                'title="%s" ' .
                'onclick="Backend.openWindow(this, 600, 500); return false;">%s</a>',
                $defName,
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
     *
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
        if (TL_MODE !== 'BE') {
            throw new DcGeneralRuntimeException(
                sprintf('WidgetBuilder only supports TL_MODE "BE". Running in TL_MODE "%s".', TL_MODE)
            );
        }

        $environment  = $this->getEnvironment();
        $dispatcher   = $environment->getEventDispatcher();
        $propertyName = $property->getName();
        $propExtra    = $property->getExtra();
        $defName      = $environment->getDataDefinition()->getName();
        $strClass     = $this->getWidgetClass($property);

        $event = new DecodePropertyValueForWidgetEvent($environment, $model);
        $event
            ->setProperty($propertyName)
            ->setValue($model->getProperty($propertyName));

        $dispatcher->dispatch($event::NAME, $event);
        $varValue = $event->getValue();

        if ((isset($propExtra['rgxp']) && in_array($propExtra['rgxp'], array('date', 'time', 'datim')))
            && empty($propExtra['mandatory'])
            && is_numeric($varValue) && $varValue == 0
        ) {
            $varValue = '';
        }

        $propExtra['required'] = ($varValue == '') && !empty($propExtra['mandatory']);

        $arrConfig = array(
            'inputType' => $property->getWidgetType(),
            'label'     => array(
                $property->getLabel(),
                $property->getDescription()
            ),
            'options'   => $this->getOptionsForWidget($property, $model),
            'eval'      => $propExtra,
            // 'foreignKey' => null
        );

        if (isset($propExtra['reference'])) {
            $arrConfig['reference'] = $propExtra['reference'];
        }

        $event = new GetAttributesFromDcaEvent(
            $arrConfig,
            $property->getName(),
            $varValue,
            $propertyName,
            $defName,
            new DcCompat($environment, $model, $propertyName)
        );

        $dispatcher->dispatch(ContaoEvents::WIDGET_GET_ATTRIBUTES_FROM_DCA, $event);
        $arrPrepared = $event->getResult();

        if ($arrConfig['inputType'] == 'checkbox'
            && isset($GLOBALS['TL_DCA'][$defName]['subpalettes'])
            && is_array($GLOBALS['TL_DCA'][$defName]['subpalettes'])
            && in_array($propertyName, array_keys($GLOBALS['TL_DCA'][$defName]['subpalettes']))
            && $arrConfig['eval']['submitOnChange']
        ) {
            // We have to override the onclick, do not append to it as Contao adds it's own code here in
            // Widget::getAttributesFromDca() which kills our sub palette handling!
            $arrPrepared['onclick'] = "Backend.autoSubmit('" . $defName . "');";
        }

        $objWidget = new $strClass($arrPrepared, new DcCompat($environment, $model, $propertyName));
        // OH: what is this? source: DataContainer 232.
        $objWidget->currentRecord = $model->getId();

        $objWidget->xlabel .= $this->getXLabel($property);

        $event = new ManipulateWidgetEvent($environment, $model, $property, $objWidget);
        $dispatcher->dispatch(ManipulateWidgetEvent::NAME, $event);

        return $objWidget;
    }
}
