<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2019 Contao Community Alliance.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    contao-community-alliance/dc-general
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     binron <rtb@gmx.ch>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2013-2019 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView;

use ContaoCommunityAlliance\Contao\Bindings\ContaoEvents;
use ContaoCommunityAlliance\Contao\Bindings\Events\Backend\GetThemeEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetPanelElementTemplateEvent;
use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;
use ContaoCommunityAlliance\DcGeneral\Exception\DcGeneralRuntimeException;
use ContaoCommunityAlliance\DcGeneral\Panel\PanelElementInterface;
use ContaoCommunityAlliance\DcGeneral\Panel\PanelInterface;

/**
 * This class renders a backend view panel including all elements.
 */
class PanelRenderer
{
    /**
     * The backend view for which the panel is to be rendered.
     *
     * @var BackendViewInterface
     */
    protected $view;

    /**
     * Create a new instance.
     *
     * @param BackendViewInterface $view The view for which the panel is to be rendered.
     */
    public function __construct($view)
    {
        $this->view = $view;
    }

    /**
     * Retrieve the environment.
     *
     * @return EnvironmentInterface
     */
    protected function getEnvironment()
    {
        return $this->view->getEnvironment();
    }

    /**
     * Render a single panel element.
     *
     * @param PanelElementInterface $element  The element to render.
     * @param string                $cssClass The CSS class to use for this element (even, odd, first, last, ...).
     *
     * @return string
     */
    protected function renderPanelElement($element, $cssClass)
    {
        $environment = $this->getEnvironment();

        $event = new GetPanelElementTemplateEvent($environment, $element);
        $environment->getEventDispatcher()->dispatch($event::NAME, $event);

        $template = $event->getTemplate();

        if (null === $template) {
            return '';
        }

        $template->set('rowClass', $cssClass);
        $element->render($template);

        return $template->parse();
    }

    /**
     * Check if the current element is in the ignored list.
     *
     * @param PanelElementInterface $element       A panel Element.
     * @param string[]              $ignoredPanels A list with ignored elements.
     *
     * @return boolean True => Element is on the ignored list. | False => Nope not in the list.
     */
    protected function isIgnoredPanel(PanelElementInterface $element, $ignoredPanels)
    {
        if (empty($ignoredPanels)) {
            return false;
        }

        foreach ((array) $ignoredPanels as $class) {
            if ($element instanceof $class) {
                return true;
            }
        }

        return false;
    }

    /**
     * Calculate the correct CSS class for a panel element.
     *
     * @param int $index The index of the element in it's panel.
     * @param int $max   The index of the last element in the panel.
     *
     * @return string
     */
    protected function calculatePanelElementCssClass($index, $max)
    {
        return
            (($index % 2) ? 'odd' : 'even') .
            ((0 === $index) ? ' first' : '') .
            (($max === $index) ? ' last' : '');
    }

    /**
     * Render a panel.
     *
     * @param PanelInterface $panel         The panel to render.
     * @param string[]       $ignoredPanels Array of class names that shall be ignored.
     *
     * @return array
     */
    protected function renderPanelRow($panel, $ignoredPanels)
    {
        $parsedElements = [];
        $index          = 0;
        $max            = (\count($panel) - 1);
        foreach ($panel as $element) {
            /** @var PanelElementInterface $element */
            // If the current class in the list of ignored panels go to the next one.
            if ($this->isIgnoredPanel($element, $ignoredPanels)) {
                $max--;
                continue;
            }

            $parsedElements[] = $this->renderPanelElement(
                $element,
                $this->calculatePanelElementCssClass($index, $max)
            );
            $index++;
        }

        return $parsedElements;
    }

    /**
     * Render the panels.
     *
     * @param array $ignoredPanels A list with ignored elements [Optional].
     *
     * @throws DcGeneralRuntimeException When no information of panels can be obtained from the data container.
     *
     * @return string
     */
    public function render($ignoredPanels = [])
    {
        $environment = $this->getEnvironment();

        // If in edit/override all mode and list all properties, the panel filter isn´t in use.
        if ('properties' === $environment->getInputProvider()->getParameter('select')) {
            return '';
        }

        if (null === $this->view->getPanel()) {
            throw new DcGeneralRuntimeException('No panel information stored in data container.');
        }

        $panels = [];
        foreach ($this->view->getPanel() as $panel) {
            $panels[] = $this->renderPanelRow($panel, $ignoredPanels);
        }

        if (\count($panels)) {
            $template   = new ContaoBackendViewTemplate('dcbe_general_panel');
            $themeEvent = new GetThemeEvent();

            $environment->getEventDispatcher()->dispatch(ContaoEvents::BACKEND_GET_THEME, $themeEvent);

            $template
                ->set('action', \ampersand($environment->getInputProvider()->getRequestUrl(), true))
                ->set('theme', $themeEvent->getTheme())
                ->set('panel', $panels);

            return $template->parse();
        }

        return '';
    }
}
