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
 * @author     Tristan Lins <tristan.lins@bit3.de>
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2013-2017 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Filter;

use ContaoCommunityAlliance\Contao\Bindings\ContaoEvents;
use ContaoCommunityAlliance\Contao\Bindings\Events\Controller\ReloadEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\RequestScopeDeterminator;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\IdSerializer;
use ContaoCommunityAlliance\DcGeneral\Data\MultiLanguageDataProviderInterface;
use ContaoCommunityAlliance\DcGeneral\DcGeneralEvents;
use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;
use ContaoCommunityAlliance\DcGeneral\Event\ActionEvent;
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
        return array(
            DcGeneralEvents::ACTION => array(array('handleAction', 500)),
        );
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
        $modelId        = ($inputProvider->hasParameter('id') && $inputProvider->getParameter('id'))
            ? IdSerializer::fromSerialized($inputProvider->getParameter('id'))->getId()
            : null;
        $languages      = $environment->getController()->getSupportedLanguages($modelId);

        if (!$languages) {
            return;
        }

        static::checkLanguageSubmit($environment);

        // Load language from Session.
        $session = (array) $sessionStorage->get('dc_general');
        /** @var MultiLanguageDataProviderInterface $dataProvider */

        // Try to get the language from session.
        if (isset($session['ml_support'][$providerName][$modelId])) {
            $currentLanguage = $session['ml_support'][$providerName][$modelId];
        } else {
            $currentLanguage = $GLOBALS['TL_LANGUAGE'];
        }

        if (!array_key_exists($currentLanguage, $languages)) {
            $currentLanguage = $dataProvider->getFallbackLanguage($modelId)->getLocale();
        }

        $session['ml_support'][$providerName][$modelId] = $currentLanguage;
        $sessionStorage->set('dc_general', $session);

        $dataProvider->setCurrentLanguage($currentLanguage);
    }

    /**
     * Check if the language has been switched.
     *
     * If so, the value in the session will be updated and the page reloaded.
     *
     * @param EnvironmentInterface $environment The environment.
     *
     * @return void
     */
    private function checkLanguageSubmit($environment)
    {
        $sessionStorage = $environment->getSessionStorage();
        $inputProvider  = $environment->getInputProvider();

        if ($inputProvider->getValue('FORM_SUBMIT') !== 'language_switch') {
            return;
        }

        $modelId      = ($inputProvider->getParameter('id') && $inputProvider->getParameter('id'))
            ? IdSerializer::fromSerialized($inputProvider->getParameter('id'))->getId()
            : null;
        $languages    = $environment->getController()->getSupportedLanguages($modelId);
        $providerName = $environment->getDataDefinition()->getName();

        // Get/Check the new language.
        if ($inputProvider->hasValue('language')
            && array_key_exists($inputProvider->getValue('language'), $languages)
        ) {
            $session['ml_support'][$providerName][$modelId] = $inputProvider->getValue('language');
            $sessionStorage->set('dc_general', $session);
        }

        $environment->getEventDispatcher()->dispatch(ContaoEvents::CONTROLLER_RELOAD, new ReloadEvent());
    }
}
