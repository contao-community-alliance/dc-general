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
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @copyright  2013-2024 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView;

use Contao\StringUtil;
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

/**
 * This class is an helper for rendering the global operation buttons in the views.
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

        return '<div id="tl_buttons">' . \implode('', $buttonsEvent->getButtons()) . '</div>';
    }

    /**
     * Render a single header button.
     *
     * @param CommandInterface $command The command definition.
     *
     * @return string
     */
    private function renderButton(CommandInterface $command)
    {
        $extra = $command->getExtra();
        $label = $this->translate($command->getLabel());

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
            ->setClass($extra['class'])
            ->setKey($command->getName())
            ->setHref($href)
            ->setLabel($label)
            ->setTitle($this->translate($command->getDescription()));
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

        $value = $this->translator->translate($path, $definition->getName());
        if ($path !== $value) {
            return $value;
        }

        return $this->translator->translate($path, 'dc-general');
    }
}
