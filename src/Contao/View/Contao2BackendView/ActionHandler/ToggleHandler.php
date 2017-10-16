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
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2013-2017 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\ActionHandler;

use ContaoCommunityAlliance\Contao\Bindings\ContaoEvents;
use ContaoCommunityAlliance\Contao\Bindings\Events\Controller\RedirectEvent;
use ContaoCommunityAlliance\Contao\Bindings\Events\System\GetReferrerEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\DataDefinition\Definition\Contao2BackendViewDefinitionInterface;
use ContaoCommunityAlliance\DcGeneral\Contao\RequestScopeDeterminator;
use ContaoCommunityAlliance\DcGeneral\Contao\RequestScopeDeterminatorAwareTrait;
use ContaoCommunityAlliance\DcGeneral\Data\ModelId;
use ContaoCommunityAlliance\DcGeneral\Data\MultiLanguageDataProviderInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\ToggleCommandInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\TranslatedToggleCommandInterface;
use ContaoCommunityAlliance\DcGeneral\View\ActionHandler\AbstractHandler;

/**
 * This class handles toggle commands.
 */
class ToggleHandler extends AbstractHandler
{
    use RequestScopeDeterminatorAwareTrait;

    /**
     * ToggleHandler constructor.
     *
     * @param RequestScopeDeterminator $scopeDeterminator The request mode determinator.
     */
    public function __construct(RequestScopeDeterminator $scopeDeterminator)
    {
        $this->setScopeDeterminator($scopeDeterminator);
    }

    /**
     * {@inheritDoc}
     *
     * @SuppressWarnings(PHPMD.ExitExpression)
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    public function process()
    {
        if (!$this->scopeDeterminator->currentScopeIsBackend()) {
            return;
        }

        $environment  = $this->getEnvironment();
        $serializedId = $this->getModelId();

        if (empty($serializedId)) {
            return;
        }

        $operation = $this->getOperation();
        if (!($operation instanceof ToggleCommandInterface)) {
            return;
        }

        if (false === $this->checkPermission()) {
            $this->getEvent()->stopPropagation();

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
            header('HTTP/1.1 204 No Content');
            exit;
        }

        $dispatcher  = $environment->getEventDispatcher();
        $newUrlEvent = new GetReferrerEvent();
        $dispatcher->dispatch(ContaoEvents::SYSTEM_GET_REFERRER, $newUrlEvent);
        $dispatcher->dispatch(ContaoEvents::CONTROLLER_REDIRECT, new RedirectEvent($newUrlEvent->getReferrerUrl()));
    }

    /**
     * Check permission for toggle property.
     *
     * @return bool
     */
    private function checkPermission()
    {
        $environment     = $this->getEnvironment();
        $dataDefinition  = $environment->getDataDefinition();
        $basicDefinition = $dataDefinition->getBasicDefinition();

        if (true === $basicDefinition->isEditable()) {
            return true;
        }

        // TODO find a way for output the permission message.
        $this->getEvent()->setResponse(
            sprintf(
                '<div style="text-align:center; font-weight:bold; padding:40px;">
                    You have no permission for toggle %s.
                </div>',
                $this->getOperation()->getToggleProperty()
            )
        );

        return false;
    }

    /**
     * Retrieve the model id from the input provider and validate it.
     *
     * @return ModelId|null
     */
    private function getModelId()
    {
        $environment   = $this->getEnvironment();
        $inputProvider = $environment->getInputProvider();

        if ($inputProvider->hasParameter('id') && $inputProvider->getParameter('id')) {
            $serializedId = ModelId::fromSerialized($inputProvider->getParameter('id'));
        }

        if (!(isset($serializedId)
              && ($serializedId->getDataProviderName() == $environment->getDataDefinition()->getName()))
        ) {
            return null;
        }

        return $serializedId;
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
