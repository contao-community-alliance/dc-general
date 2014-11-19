<?php
/**
 * PHP version 5
 *
 * @package    generalDriver
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Tristan Lins <tristan.lins@bit3.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\ActionHandler;

use ContaoCommunityAlliance\Contao\Bindings\ContaoEvents;
use ContaoCommunityAlliance\Contao\Bindings\Events\Controller\ReloadEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\IdSerializer;
use ContaoCommunityAlliance\DcGeneral\Data\MultiLanguageDataProviderInterface;
use ContaoCommunityAlliance\DcGeneral\Event\ActionEvent;
use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;

/**
 * Handler class for handling the show events.
 *
 * @package ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\ActionHandler
 */
abstract class AbstractHandler
{
    /**
     * The event.
     *
     * @var ActionEvent
     */
    private $event = null;

    /**
     * Method to buffer the event and then process it.
     *
     * @param ActionEvent $event The event to process.
     *
     * @return void
     */
    public function handleEvent(ActionEvent $event)
    {
        $this->event = $event;
        $this->process();
        $this->event = null;
    }

    /**
     * Retrieve the environment.
     *
     * @return ActionEvent
     */
    protected function getEvent()
    {
        return $this->event;
    }

    /**
     * Retrieve the environment.
     *
     * @return EnvironmentInterface
     */
    protected function getEnvironment()
    {
        return $this->getEvent()->getEnvironment();
    }

    /**
     * Handle the action.
     *
     * @return mixed
     */
    abstract public function process();

    /**
     * Check if the language has been switched.
     *
     * If so, the value in the session will be updated and the page reloaded.
     *
     * @param EnvironmentInterface $environment The environment.
     *
     * @return void
     */
    public static function checkLanguageSubmit($environment)
    {
        $sessionStorage = $environment->getSessionStorage();
        $inputProvider  = $environment->getInputProvider();

        if ($inputProvider->getValue('FORM_SUBMIT') !== 'language_switch') {
            return;
        }

        $modelId      = $inputProvider->hasParameter('id')
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
    public static function checkLanguage($environment)
    {
        $inputProvider  = $environment->getInputProvider();
        $sessionStorage = $environment->getSessionStorage();
        $dataProvider   = $environment->getDataProvider();
        $providerName   = $environment->getDataDefinition()->getName();
        $modelId        = $inputProvider->hasParameter('id')
            ? IdSerializer::fromSerialized($inputProvider->getParameter('id'))->getId()
            : null;
        $languages      = $environment->getController()->getSupportedLanguages($modelId);

        if (!$languages) {
            return;
        }

        static::checkLanguageSubmit($environment);

        // Load language from Session.
        $session = (array)$sessionStorage->get('dc_general');
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
}
