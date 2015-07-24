<?php
/**
 * PHP version 5
 *
 * @package    generalDriver
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\ActionHandler;

use ContaoCommunityAlliance\Contao\Bindings\ContaoEvents;
use ContaoCommunityAlliance\Contao\Bindings\Events\Controller\RedirectEvent;
use ContaoCommunityAlliance\Contao\Bindings\Events\System\GetReferrerEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\DataDefinition\Definition\Contao2BackendViewDefinitionInterface;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\IdSerializer;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\ToggleCommandInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\TranslatedToggleCommandInterface;
use ContaoCommunityAlliance\DcGeneral\Data\MultiLanguageDataProviderInterface;

/**
 * This class handles toggle commands.
 */
class ToggleHandler extends AbstractHandler
{
    /**
     * {@inheritDoc}
     */
    public function process()
    {
        $environment   = $this->getEnvironment();
        $inputProvider = $environment->getInputProvider();

        if ($inputProvider->hasParameter('id') && $inputProvider->getParameter('id')) {
            $serializedId = IdSerializer::fromSerialized($inputProvider->getParameter('id'));
        }

        if (!(isset($serializedId)
            && ($serializedId->getDataProviderName() == $environment->getDataDefinition()->getName()))
        ) {
            return;
        }

        $operation = $this->getOperation();
        if (!($operation instanceof ToggleCommandInterface)) {
            return;
        }

        $dataProvider = $environment->getDataProvider();
        $newState     = $this->determineNewState($operation->isInverse());

        // Override the language for language aware toggling.
        if ($operation instanceof TranslatedToggleCommandInterface
            && $dataProvider instanceof MultiLanguageDataProviderInterface
        ) {
            $language = $dataProvider->getCurrentLanguage();
            /** @var TranslatedToggleCommandInterface $operation */
            $dataProvider->setCurrentLanguage($operation->getLanguage());
        }

        $model = $dataProvider->fetch($dataProvider->getEmptyConfig()->setId($serializedId->getId()));
        $model->setProperty($operation->getToggleProperty(), $newState);
        $dataProvider->save($model);
        // Select the previous language.
        if (isset($language)) {
            $dataProvider->setCurrentLanguage($language);
        }

        // Sad that we can not determine ajax requests better.
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && ($_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest')) {
            // HTTP 204 - No content would be more sufficient here.
            header('HTTP/1.1 204 No Content');
            exit;
        }

        $dispatcher  = $environment->getEventDispatcher();
        $newUrlEvent = new GetReferrerEvent();
        $dispatcher->dispatch(ContaoEvents::SYSTEM_GET_REFERRER, $newUrlEvent);
        $dispatcher->dispatch(ContaoEvents::CONTROLLER_REDIRECT, new RedirectEvent($newUrlEvent->getReferrerUrl()));
    }

    /**
     * Retrieve the toggle operation being executed.
     *
     * @return ToggleCommandInterface
     */
    private function getOperation()
    {
        /** @var Contao2BackendViewDefinitionInterface $definition */
        $definition = $this
            ->getEnvironment()
            ->getDataDefinition()
            ->getDefinition(Contao2BackendViewDefinitionInterface::NAME);
        $name       = $this->getEvent()->getAction()->getName();
        $commands   = $definition->getModelCommands();

        if (!$commands->hasCommandNamed($name)) {
            return null;
        }

        return $commands->getCommandNamed($name);
    }

    /**
     * Determine the new state from the input data.
     *
     * @param bool $isInverse Flag if the state shall be evaluated as inverse toggler.
     *
     * @return string
     */
    private function determineNewState($isInverse)
    {
        $state = $this->getEnvironment()->getInputProvider()->getParameter('state') == 1;

        if ($isInverse) {
            return $state ? '' : '1';
        }

        return $state ? '1' : '';
    }
}
