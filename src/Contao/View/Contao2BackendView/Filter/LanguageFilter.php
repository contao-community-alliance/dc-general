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
 * @author     Tristan Lins <tristan.lins@bit3.de>
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2013-2023 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Filter;

use ContaoCommunityAlliance\Contao\Bindings\ContaoEvents;
use ContaoCommunityAlliance\Contao\Bindings\Events\Controller\ReloadEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\RequestScopeDeterminator;
use ContaoCommunityAlliance\DcGeneral\Controller\ControllerInterface;
use ContaoCommunityAlliance\DcGeneral\Data\LanguageInformationInterface;
use ContaoCommunityAlliance\DcGeneral\Data\ModelId;
use ContaoCommunityAlliance\DcGeneral\Data\ModelIdInterface;
use ContaoCommunityAlliance\DcGeneral\Data\MultiLanguageDataProviderInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\ContainerInterface;
use ContaoCommunityAlliance\DcGeneral\DcGeneralEvents;
use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;
use ContaoCommunityAlliance\DcGeneral\Event\ActionEvent;
use ContaoCommunityAlliance\DcGeneral\InputProviderInterface;
use ContaoCommunityAlliance\DcGeneral\SessionStorageInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Handler class for handling the show events.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class LanguageFilter implements EventSubscriberInterface
{
    /**
     * The request mode determinator.
     *
     * @var RequestScopeDeterminator
     */
    private $scopeDeterminator;

    /**
     * ClipboardController constructor.
     *
     * @param RequestScopeDeterminator $scopeDeterminator
     */
    public function __construct(RequestScopeDeterminator $scopeDeterminator)
    {
        $this->scopeDeterminator = $scopeDeterminator;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            DcGeneralEvents::ACTION => [['handleAction', 500]]
        ];
    }

    /**
     * Handle action events.
     *
     * @param ActionEvent $event The action event.
     *
     * @return void
     */
    public function handleAction(ActionEvent $event)
    {
        if (!$this->scopeDeterminator->currentScopeIsBackend()) {
            return;
        }

        $this->checkLanguage($event->getEnvironment(), 'create' === $event->getAction()->getName());
    }

    /**
     * Check if the data provider is multi-language and prepare the data provider with the selected language.
     *
     * @param EnvironmentInterface $environment     The environment.
     * @param bool                 $resetToFallback Flag if the language must be reset to the fallback.
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    private function checkLanguage($environment, $resetToFallback)
    {
        $sessionStorage = $environment->getSessionStorage();
        assert($sessionStorage instanceof SessionStorageInterface);

        $dataProvider = $environment->getDataProvider();

        $definition = $environment->getDataDefinition();
        assert($definition instanceof ContainerInterface);

        $providerName = $definition->getName();

        $inputProvider = $environment->getInputProvider();
        assert($inputProvider instanceof InputProviderInterface);

        $modelId      = $this->modelIdFromInput($inputProvider);

        $controller = $environment->getController();
        assert($controller instanceof ControllerInterface);

        $languages = $controller->getSupportedLanguages($modelId);

        if (!$languages) {
            return;
        }

        // Exit out when not a multi-language provider.
        if (!($dataProvider instanceof MultiLanguageDataProviderInterface)) {
            return;
        }

        // If a new item, we MUST reset to the fallback as that is the first language that has to be stored
        // and set this language to session.
        $session = [];
        if ((null === $modelId) && $resetToFallback) {
            $fallbackLanguage = $dataProvider->getFallbackLanguage(null);
            assert($fallbackLanguage instanceof LanguageInformationInterface);

            $dataProvider->setCurrentLanguage($fallbackLanguage->getLocale());
            $session['ml_support'][$providerName] = $dataProvider->getCurrentLanguage();
            $sessionStorage->set('dc_general', $session);

            return;
        }

        $this->checkLanguageSubmit($environment, $languages);

        // Load language from Session.
        $session = (array) $sessionStorage->get('dc_general');

        // Try to get the language from session.
        $currentLanguage = ($session['ml_support'][$providerName] ?? $GLOBALS['TL_LANGUAGE']);

        if (!\array_key_exists($currentLanguage, $languages)) {
            $fallbackLanguage = $dataProvider->getFallbackLanguage($modelId);
            assert($fallbackLanguage instanceof LanguageInformationInterface);

            $currentLanguage = $fallbackLanguage->getLocale();
        }

        $session['ml_support'][$providerName] = $currentLanguage;
        $sessionStorage->set('dc_general', $session);

        $dataProvider->setCurrentLanguage($currentLanguage);
    }

    /**
     * Check if the language has been switched.
     *
     * If so, the value in the session will be updated and the page reloaded.
     *
     * @param EnvironmentInterface $environment The environment.
     * @param array                $languages   The valid languages.
     *
     * @return void
     */
    private function checkLanguageSubmit($environment, $languages)
    {
        $inputProvider = $environment->getInputProvider();
        assert($inputProvider instanceof InputProviderInterface);

        if ('language_switch' !== $inputProvider->getValue('FORM_SUBMIT')) {
            return;
        }

        // Get/Check the new language.
        $session = [];
        if (
            $inputProvider->hasValue('language')
            && \array_key_exists($inputProvider->getValue('language'), $languages)
        ) {
            $definition = $environment->getDataDefinition();
            assert($definition instanceof ContainerInterface);

            $session['ml_support'][$definition->getName()] = $inputProvider->getValue('language');

            $sessionStorage = $environment->getSessionStorage();
            assert($sessionStorage instanceof SessionStorageInterface);

            $sessionStorage->set('dc_general', $session);
        }

        $dispatcher = $environment->getEventDispatcher();
        assert($dispatcher instanceof EventDispatcherInterface);

        $dispatcher->dispatch(new ReloadEvent(), ContaoEvents::CONTROLLER_RELOAD);
    }

    /**
     * Obtain the model id from the input provider.
     *
     * @param InputProviderInterface $inputProvider The input provider.
     *
     * @return ModelIdInterface|null
     */
    private function modelIdFromInput(InputProviderInterface $inputProvider)
    {
        if ($inputProvider->hasParameter('id') && $inputProvider->getParameter('id')) {
            return ModelId::fromSerialized($inputProvider->getParameter('id'))->getId();
        }

        return null;
    }
}
