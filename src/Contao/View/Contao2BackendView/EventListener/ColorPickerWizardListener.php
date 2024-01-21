<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2023 Contao Community Alliance.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    contao-community-alliance/dc-general
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2013-2023 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\EventListener;

use Contao\StringUtil;
use Contao\Widget;
use ContaoCommunityAlliance\Contao\Bindings\ContaoEvents;
use ContaoCommunityAlliance\Contao\Bindings\Events\Image\GenerateHtmlEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\RequestScopeDeterminator;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\BuildWidgetEvent;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\Properties\PropertyInterface;
use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;
use ContaoCommunityAlliance\Translator\TranslatorInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Widget Builder to append color picker wizards to Contao backend widgets.
 */
class ColorPickerWizardListener
{
    /**
     * The request mode determinator.
     *
     * @var RequestScopeDeterminator
     */
    private $scopeDeterminator;

    /**
     * ColorPickerWizardListener constructor.
     *
     * @param RequestScopeDeterminator $scopeDeterminator The request scope determinator.
     */
    public function __construct(RequestScopeDeterminator $scopeDeterminator)
    {
        $this->scopeDeterminator = $scopeDeterminator;
    }

    /**
     * Handle the build widget event.
     *
     * @param BuildWidgetEvent $event The event.
     *
     * @return void
     */
    public function handleEvent(BuildWidgetEvent $event)
    {
        if (!$this->scopeDeterminator->currentScopeIsBackend()) {
            return;
        }

        $widget = $event->getWidget();
        if (!$widget instanceof Widget) {
            return;
        }

        $widget->wizard .= self::getWizard($event->getProperty(), $event->getEnvironment());
    }

    /**
     * Append wizard icons.
     *
     * @param PropertyInterface    $propInfo    The property for which the wizards shall be generated.
     * @param EnvironmentInterface $environment The environment.
     *
     * @return string
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    public static function getWizard($propInfo, EnvironmentInterface $environment)
    {
        $wizard    = '';
        $propExtra = $propInfo->getExtra();

        $translator = $environment->getTranslator();
        assert($translator instanceof TranslatorInterface);

        if (\array_key_exists('colorpicker', $propExtra) && $propExtra['colorpicker']) {
            $pickerText = $translator->translate('colorpicker', 'dc-general');
            $event      = new GenerateHtmlEvent(
                'pickcolor.svg',
                $pickerText,
                \sprintf(
                    'style="%s" title="%s" id="moo_%s"',
                    'vertical-align:-6px; width:20px; height:20px',
                    StringUtil::specialchars($pickerText),
                    $propInfo->getName()
                )
            );

            $dispatcher = $environment->getEventDispatcher();
            assert($dispatcher instanceof EventDispatcherInterface);

            $dispatcher->dispatch($event, ContaoEvents::IMAGE_GET_HTML);

            // Support single fields as well (see contao/core#5240)
            $strKey = $propExtra['multiple'] ? $propInfo->getName() . '_0' : $propInfo->getName();

            $wizard .= \sprintf(
                ' %1$s <script>var cl;window.addEvent("domready", function() { new MooRainbow("moo_%2$s", {' .
                'id: "ctrl_%3$s", startColor: ((cl = $("ctrl_%3$s").value.hexToRgb(true)) ? cl : [255, 0, 0]),' .
                'imgPath: "%4$s", onComplete: function(color) {$("ctrl_%3$s").value = color.hex.replace("#", "");}});' .
                '});</script>',
                $event->getHtml() ?? '',
                $propInfo->getName(),
                $strKey,
                'assets/colorpicker/images/'
            );
        }

        return $wizard;
    }
}
