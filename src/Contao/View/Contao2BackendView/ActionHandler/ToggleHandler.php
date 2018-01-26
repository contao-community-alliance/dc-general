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
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @copyright  2013-2018 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\ActionHandler;

use ContaoCommunityAlliance\Contao\Bindings\ContaoEvents;
use ContaoCommunityAlliance\Contao\Bindings\Events\Controller\RedirectEvent;
use ContaoCommunityAlliance\Contao\Bindings\Events\System\GetReferrerEvent;
use ContaoCommunityAlliance\DcGeneral\Action;
use ContaoCommunityAlliance\DcGeneral\Contao\DataDefinition\Definition\Contao2BackendViewDefinitionInterface;
use ContaoCommunityAlliance\DcGeneral\Contao\RequestScopeDeterminator;
use ContaoCommunityAlliance\DcGeneral\Contao\RequestScopeDeterminatorAwareTrait;
use ContaoCommunityAlliance\DcGeneral\Data\ModelId;
use ContaoCommunityAlliance\DcGeneral\Data\ModelIdInterface;
use ContaoCommunityAlliance\DcGeneral\Data\MultiLanguageDataProviderInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\ToggleCommandInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\TranslatedToggleCommandInterface;
use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;
use ContaoCommunityAlliance\DcGeneral\Event\ActionEvent;
use ContaoCommunityAlliance\DcGeneral\InputProviderInterface;

/**
 * This class handles toggle commands.
 */
class ToggleHandler
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
     * Handle the event to process the action.
     *
     * @param ActionEvent $event The action event.
     *
     * @return void
     */
    public function handleEvent(ActionEvent $event)
    {
        if (!$this->scopeDeterminator->currentScopeIsBackend()) {
            return;
        }

        $environment  = $event->getEnvironment();
        $serializedId = $this->getModelId($environment);

        if (empty($serializedId)) {
            return;
        }

        $operation = $this->getOperation($event->getAction(), $environment);
        if (!($operation instanceof ToggleCommandInterface)) {
            return;
        }

        if (false === $this->checkPermission($event)) {
            $event->stopPropagation();

            return;
        }

        $this->process($environment, $operation, $serializedId);
    }

    /**
     * Process the action.
     *
     * @param EnvironmentInterface   $environment  The environment.
     * @param ToggleCommandInterface $operation    The operation.
     * @param ModelIdInterface|null  $serializedId The model id.
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.ExitExpression)
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    protected function process(
        EnvironmentInterface $environment,
        ToggleCommandInterface $operation,
        ModelIdInterface $serializedId = null
    ) {
        $dataProvider = $environment->getDataProvider();
        $newState     = $this->determineNewState($environment->getInputProvider(), $operation->isInverse());

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
     * @param ActionEvent $event The action event.
     *
     * @return bool
     */
    private function checkPermission(ActionEvent $event)
    {
        $environment     = $event->getEnvironment();
        $dataDefinition  = $environment->getDataDefinition();
        $basicDefinition = $dataDefinition->getBasicDefinition();

        if (true === $basicDefinition->isEditable()) {
            return true;
        }

        // TODO find a way for output the permission message.
        $event->setResponse(
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
     * @param EnvironmentInterface $environment The environment.
     *
     * @return ModelIdInterface|null
     */
    private function getModelId(EnvironmentInterface $environment)
    {
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
     * @param Action               $action      The action.
     * @param EnvironmentInterface $environment The environment.
     *
     * @return ToggleCommandInterface
     */
    private function getOperation(Action $action, EnvironmentInterface $environment)
    {
        /** @var Contao2BackendViewDefinitionInterface $definition */
        $definition = $environment
            ->getDataDefinition()
            ->getDefinition(Contao2BackendViewDefinitionInterface::NAME);
        $name       = $action->getName();
        $commands   = $definition->getModelCommands();

        if (!$commands->hasCommandNamed($name)) {
            return null;
        }

        return $commands->getCommandNamed($name);
    }

    /**
     * Determine the new state from the input data.
     *
     * @param InputProviderInterface $inputProvider The input provider.
     * @param bool                   $isInverse     Flag if the state shall be evaluated as inverse toggler.
     *
     * @return string
     */
    private function determineNewState(InputProviderInterface $inputProvider, $isInverse)
    {
        $state = $inputProvider->getParameter('state') == 1;

        if ($isInverse) {
            return $state ? '' : '1';
        }

        return $state ? '1' : '';
    }
}
