<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2018 Contao Community Alliance.
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
 * @copyright  2013-2018 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Filter;

use ContaoCommunityAlliance\Contao\Bindings\ContaoEvents;
use ContaoCommunityAlliance\Contao\Bindings\Events\Controller\ReloadEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\RequestScopeDeterminator;
use ContaoCommunityAlliance\DcGeneral\Data\ModelId;
use ContaoCommunityAlliance\DcGeneral\Data\MultiLanguageDataProviderInterface;
use ContaoCommunityAlliance\DcGeneral\DcGeneralEvents;
use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;
use ContaoCommunityAlliance\DcGeneral\Event\ActionEvent;
use ContaoCommunityAlliance\DcGeneral\InputProviderInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Handler class for handling the show events.
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
            DcGeneralEvents::ACTION => [['handleAction', 500]],
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

        $this->checkLanguage($event->getEnvironment());
    }

    /**
     * Check if the data provider is multi language and prepare the data provider with the selected language.
     *
     * @param EnvironmentInterface $environment The environment.
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    private function checkLanguage($environment)
    {
        $inputProvider  = $environment->getInputProvider();
        $sessionStorage = $environment->getSessionStorage();
        $dataProvider   = $environment->getDataProvider();
        $providerName   = $environment->getDataDefinition()->getName();
        $modelId        = $this->modelIdFromInput($inputProvider);
        $languages      = $environment->getController()->getSupportedLanguages($modelId);

        if (!$languages) {
            return;
        }

        // Exit out when not a multi language provider.
        if (!($dataProvider instanceof MultiLanguageDataProviderInterface)) {
            return;
        }

        // If a new item, we MUST reset to the fallback as that is the first language that has to be stored
        // and set this language to session.
        if (null === $modelId) {
            $dataProvider->setCurrentLanguage($dataProvider->getFallbackLanguage(null)->getLocale());
            $session['ml_support'][$providerName] = $dataProvider->getCurrentLanguage();
            $sessionStorage->set('dc_general', $session);
            return;
        }

        $this->checkLanguageSubmit($environment, $languages);

        // Load language from Session.
        $session = (array) $sessionStorage->get('dc_general');

        // Try to get the language from session.
        if (isset($session['ml_support'][$providerName])) {
            $currentLanguage = $session['ml_support'][$providerName];
        } else {
            $currentLanguage = $GLOBALS['TL_LANGUAGE'];
        }

        if (!\array_key_exists($currentLanguage, $languages)) {
            $currentLanguage = $dataProvider->getFallbackLanguage($modelId)->getLocale();
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
        $sessionStorage = $environment->getSessionStorage();
        $inputProvider  = $environment->getInputProvider();

        if ($inputProvider->getValue('FORM_SUBMIT') !== 'language_switch') {
            return;
        }

        $providerName = $environment->getDataDefinition()->getName();

        // Get/Check the new language.
        if ($inputProvider->hasValue('language')
            && \array_key_exists($inputProvider->getValue('language'), $languages)
        ) {
            $session['ml_support'][$providerName] = $inputProvider->getValue('language');
            $sessionStorage->set('dc_general', $session);
        }

        $environment->getEventDispatcher()->dispatch(ContaoEvents::CONTROLLER_RELOAD, new ReloadEvent());
    }

    /**
     * Obtain the model id from the input provider.
     *
     * @param InputProviderInterface $inputProvider The input provider.
     *
     * @return mixed|null
     */
    private function modelIdFromInput(InputProviderInterface $inputProvider)
    {
        if ($inputProvider->hasParameter('id') && $inputProvider->getParameter('id')) {
            return ModelId::fromSerialized($inputProvider->getParameter('id'))->getId();
        }

        return null;
    }
}
