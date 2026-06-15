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
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2013-2026 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\EventListener;

use Contao\Widget;
use ContaoCommunityAlliance\DcGeneral\Contao\RequestScopeDeterminator;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\BuildWidgetEvent;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\Properties\PropertyInterface;
use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;

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

        $propExtra = $event->getProperty()->getExtra();
        if (!\array_key_exists('colorpicker', $propExtra) || !$propExtra['colorpicker']) {
            return;
        }

        // Contao 5.7 renders the color picker through the "contao--color-picker" Stimulus controller
        // (added on the widget wrapper, see the dcbe_general_field template). The controller needs an
        // "input" target on the field and a "button" target as its trigger inside the same scope.
        $widget->addAttribute('data-contao--color-picker-target', 'input');
        $widget->wizard .= self::getWizard($event->getProperty(), $event->getEnvironment());
    }

    /**
     * Append the color picker wizard markup.
     *
     * @param PropertyInterface    $propInfo    The property for which the wizards shall be generated.
     * @param EnvironmentInterface $environment The environment.
     *
     * @return string
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public static function getWizard($propInfo, EnvironmentInterface $environment)
    {
        $propExtra = $propInfo->getExtra();

        if (!\array_key_exists('colorpicker', $propExtra) || !$propExtra['colorpicker']) {
            return '';
        }

        // Button target for the contao--color-picker Stimulus controller (Contao 5.7).
        return '<div data-contao--color-picker-target="button"></div>';
    }
}
