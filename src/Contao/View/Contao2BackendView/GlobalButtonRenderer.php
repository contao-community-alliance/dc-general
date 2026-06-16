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
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @copyright  2013-2026 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView;

use Contao\StringUtil;
use Contao\System;
use ContaoCommunityAlliance\Contao\Bindings\ContaoEvents;
use ContaoCommunityAlliance\Contao\Bindings\Events\Backend\AddToUrlEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\DataDefinition\Definition\Contao2BackendViewDefinitionInterface;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetGlobalButtonEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetGlobalButtonsEvent;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\ContainerInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\CommandInterface;
use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;
use ContaoCommunityAlliance\Translator\TranslatorInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Translation\TranslatorInterface as SymfonyTranslatorInterface;

/**
 * This class is an helper for rendering the global operation buttons in the views.
 *
 * @api
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class GlobalButtonRenderer
{
    /**
     * The environment.
     *
     * @var EnvironmentInterface
     */
    private EnvironmentInterface $environment;

    /**
     * The dispatcher.
     *
     * @var EventDispatcherInterface
     */
    private EventDispatcherInterface $dispatcher;

    /**
     * The translator.
     *
     * @var TranslatorInterface
     */
    private TranslatorInterface $translator;

    /**
     * Create a new instance.
     *
     * @param EnvironmentInterface $environment The environment.
     */
    public function __construct(EnvironmentInterface $environment)
    {
        $this->environment = $environment;
        assert($environment instanceof EnvironmentInterface);

        $dispatcher = $environment->getEventDispatcher();
        assert($dispatcher instanceof EventDispatcherInterface);
        $this->dispatcher = $dispatcher;

        $translator = $environment->getTranslator();
        assert($translator instanceof TranslatorInterface);
        $this->translator = $translator;
    }

    /**
     * Generate all global operation buttons for a view.
     *
     * @return string
     */
    public function render()
    {
        $definition = $this->environment->getDataDefinition();
        assert($definition instanceof ContainerInterface);

        $backendView = $definition->getDefinition(Contao2BackendViewDefinitionInterface::NAME);
        assert($backendView instanceof Contao2BackendViewDefinitionInterface);

        $commands = $backendView->getGlobalCommands()->getCommands();

        $buttons = [];
        foreach ($commands as $command) {
            if ($command->isDisabled()) {
                continue;
            }
            $buttons[$command->getName()] = $this->renderButton($command);
        }

        $buttonsEvent = new GetGlobalButtonsEvent($this->environment);
        $buttonsEvent->setButtons($buttons);
        $this->dispatcher->dispatch($buttonsEvent, GetGlobalButtonsEvent::NAME);

        return '<div id="tl_buttons">'
            . \implode('', $buttonsEvent->getButtons())
            . $this->renderFilterToggle($backendView)
            . '</div>';
    }

    /**
     * Render the toggle button that reveals the off-canvas filter panel on narrow viewports.
     *
     * The button is hidden by default and revealed below 1280px by Contao's flexible theme
     * (`li:has(>.header_filter_toggle)`); it drives the `.content-filter` element via the
     * contao--toggle-sender/contao--toggle-receiver Stimulus controllers.
     *
     * @param Contao2BackendViewDefinitionInterface $backendView The backend view definition.
     *
     * @return string
     */
    private function renderFilterToggle(Contao2BackendViewDefinitionInterface $backendView): string
    {
        // Only show the toggle when there actually is a filter/search panel.
        if (0 === $backendView->getPanelLayout()->getRows()->getRowCount()) {
            return '';
        }

        $translator = System::getContainer()->get('translator');
        assert($translator instanceof SymfonyTranslatorInterface);

        $label     = $translator->trans('DCA.toggleFilter.0', [], 'contao_default');
        $titleShow = $translator->trans('DCA.toggleFilter.1', [], 'contao_default');
        $titleHide = $translator->trans('DCA.toggleFilter.2', [], 'contao_default');

        return \sprintf(
            '<li style="display:none"><button type="button" class="header_filter_toggle" title="%s"'
            . ' data-controller="contao--toggle-sender"'
            . ' data-contao--toggle-sender-contao--toggle-receiver-outlet="#tl_content_filter"'
            . ' data-contao--toggle-sender-active-title-value="%s"'
            . ' data-contao--toggle-sender-inactive-title-value="%s"'
            . ' data-action="contao--toggle-sender#toggle:prevent">%s</button></li>',
            StringUtil::specialchars($titleShow),
            StringUtil::specialchars($titleHide),
            StringUtil::specialchars($titleShow),
            $label
        );
    }

    /**
     * Render a single header button.
     *
     * @param CommandInterface $command The command definition.
     *
     * @return string
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    private function renderButton(CommandInterface $command)
    {
        $extra = $command->getExtra();
        $label = $this->translate($command->getLabel());
        // Translation fallback to old Contao translations.
        // @deprecated Remove in 3.0
        if (str_ends_with($label, '.label') && $label === $command->getLabel()) {
            $label = $this->translate(substr($command->getLabel(), 0, -6) . '.0');
        }

        $description = $this->translate($command->getDescription());
        // Translation fallback to old Contao translations.
        // @deprecated Remove in 3.0
        if (str_ends_with($description, '.description') && $description === $command->getDescription()) {
            $description = $this->translate(substr($command->getDescription(), 0, -12) . '.1');
        }

        if (isset($extra['href'])) {
            $href = $extra['href'];
        } else {
            $href = '';
            foreach ($command->getParameters() as $key => $value) {
                $href .= '&' . $key . '=' . $value;
            }

            /** @var AddToUrlEvent $event */
            $event = $this->dispatcher->dispatch(
                new AddToUrlEvent($href),
                ContaoEvents::BACKEND_ADD_TO_URL
            );

            $href = $event->getUrl();
        }

        if ('' === $label) {
            $label = $command->getName();
        }

        $buttonEvent = new GetGlobalButtonEvent($this->environment);
        $buttonEvent
            ->setAccessKey(isset($extra['accesskey']) ? \trim($extra['accesskey']) : '')
            ->setAttributes(' ' . \ltrim($extra['attributes'] ?? ''))
            ->setClass($extra['class'] ?? '')
            ->setKey($command->getName())
            ->setHref($href)
            ->setLabel($label)
            ->setTitle($description);
        $this->dispatcher->dispatch($buttonEvent, GetGlobalButtonEvent::NAME);

        // Allow to override the button entirely - if someone sets empty string, we keep it.
        if (null !== ($html = $buttonEvent->getHtml())) {
            return $html;
        }

        // Use the view native button building.
        return \sprintf(
            '<a href="%s" class="%s" title="%s"%s>%s</a> ',
            $buttonEvent->getHref(),
            $buttonEvent->getClass(),
            StringUtil::specialchars($buttonEvent->getTitle()),
            $buttonEvent->getAttributes(),
            $buttonEvent->getLabel()
        );
    }

    /**
     * Translate a string via the translator.
     *
     * @param string $path The path within the translation where the string can be found.
     *
     * @return string
     */
    private function translate(string $path): string
    {
        $definition = $this->environment->getDataDefinition();
        assert($definition instanceof ContainerInterface);

        $domain = $definition->getName();
        if ($path !== ($value = $this->translator->translate($path, $domain))) {
            return $value;
        }

        // Fallback translate for non symfony domain.
        if (
            $domain . '.' . $path !== ($value =
                $this->translator->translate($domain . '.' . $path, 'contao_' . $domain))
        ) {
            return $value;
        }

        return $this->translator->translate($path, 'dc-general');
    }
}
