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
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @author     Benedict Zinke <bz@presentprogressive.de>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2013-2023 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\ActionHandler;

use ContaoCommunityAlliance\DcGeneral\Action;
use ContaoCommunityAlliance\DcGeneral\Contao\DataDefinition\Definition\Contao2BackendViewDefinitionInterface;
use ContaoCommunityAlliance\DcGeneral\Contao\RequestScopeDeterminator;
use ContaoCommunityAlliance\DcGeneral\Contao\RequestScopeDeterminatorAwareTrait;
use ContaoCommunityAlliance\DcGeneral\Data\ConfigInterface;
use ContaoCommunityAlliance\DcGeneral\Data\DataProviderInterface;
use ContaoCommunityAlliance\DcGeneral\Data\ModelId;
use ContaoCommunityAlliance\DcGeneral\Data\ModelIdInterface;
use ContaoCommunityAlliance\DcGeneral\Data\ModelInterface;
use ContaoCommunityAlliance\DcGeneral\Data\MultiLanguageDataProviderInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\ContainerInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\CommandInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\ToggleCommandInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\TranslatedToggleCommandInterface;
use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;
use ContaoCommunityAlliance\DcGeneral\Event\ActionEvent;
use ContaoCommunityAlliance\DcGeneral\Event\PostPersistModelEvent;
use ContaoCommunityAlliance\DcGeneral\Event\PrePersistModelEvent;
use ContaoCommunityAlliance\DcGeneral\InputProviderInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * This class handles toggle commands.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
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
        if (!$this->getScopeDeterminator()->currentScopeIsBackend()) {
            return;
        }

        $environment = $event->getEnvironment();

        if (null === ($serializedId = $this->getModelId($environment))) {
            return;
        }

        $operation = $this->getOperation($event->getAction(), $environment);
        if (!($operation instanceof ToggleCommandInterface)) {
            return;
        }

        if (false === $this->checkPermission($event, $operation)) {
            $event->stopPropagation();

            return;
        }

        $this->process($environment, $operation, $serializedId);
    }

    /**
     * Process the action.
     *
     * @param EnvironmentInterface   $environment The environment.
     * @param ToggleCommandInterface $operation   The operation.
     * @param ModelIdInterface|null  $modelId     The model id.
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
        ModelIdInterface $modelId = null
    ) {
        $dataProvider = $environment->getDataProvider();
        assert($dataProvider instanceof DataProviderInterface);

        $inputProvider = $environment->getInputProvider();
        assert($inputProvider instanceof InputProviderInterface);

        $newState = $this->determineNewState($inputProvider, $operation->isInverse());

        // Override the language for language aware toggling.
        if (
            ($operation instanceof TranslatedToggleCommandInterface)
            && ($dataProvider instanceof MultiLanguageDataProviderInterface)
        ) {
            $language = $dataProvider->getCurrentLanguage();
            /** @var TranslatedToggleCommandInterface $operation */
            $dataProvider->setCurrentLanguage($operation->getLanguage());
        }

        $provider = $dataProvider->getEmptyConfig();
        assert($provider instanceof ConfigInterface);
        assert($modelId instanceof ModelIdInterface);
        $config = $provider->setId($modelId->getId());
        assert($config instanceof ConfigInterface);

        $model = $dataProvider->fetch($config);
        assert($model instanceof ModelInterface);

        $originalModel = clone $model;
        $originalModel->setId($model->getId());
        $model->setProperty($operation->getToggleProperty(), $newState);

        $dispatcher = $environment->getEventDispatcher();
        assert($dispatcher instanceof EventDispatcherInterface);

        $dispatcher->dispatch(
            new PrePersistModelEvent($environment, $model, $originalModel),
            PrePersistModelEvent::NAME
        );

        $dataProvider->save($model);

        $dispatcher->dispatch(
            new PostPersistModelEvent($environment, $model, $originalModel),
            PostPersistModelEvent::NAME
        );

        // Select the previous language.
        if (isset($language)) {
            /** @var MultiLanguageDataProviderInterface $dataProvider */
            $dataProvider->setCurrentLanguage($language);
        }
    }

    /**
     * Check permission for toggle property.
     *
     * @param ActionEvent            $event   The action event.
     *
     * @param ToggleCommandInterface $command The executed operation.
     *
     * @return bool
     */
    private function checkPermission(ActionEvent $event, ToggleCommandInterface $command)
    {
        $environment = $event->getEnvironment();

        $definition = $environment->getDataDefinition();
        assert($definition instanceof ContainerInterface);

        if (true === !$command->isDisabled() && $definition->getBasicDefinition()->isEditable()) {
            return true;
        }

        $operation = $this->getOperation($event->getAction(), $environment);
        assert($operation instanceof ToggleCommandInterface);

        $event->setResponse(
            \sprintf(
                '<div style="text-align:center; font-weight:bold; padding:40px;">
                    You have no permission for toggle %s.
                </div>',
                $operation->getToggleProperty()
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
        assert($inputProvider instanceof InputProviderInterface);

        if ($inputProvider->hasParameter('id') && $inputProvider->getParameter('id')) {
            $modelId = ModelId::fromSerialized($inputProvider->getParameter('id'));
        }

        $definition = $environment->getDataDefinition();
        assert($definition instanceof ContainerInterface);

        if (!(isset($modelId) && ($definition->getName() === $modelId->getDataProviderName()))) {
            return null;
        }

        return $modelId;
    }

    /**
     * Retrieve the toggle operation being executed.
     *
     * @param Action               $action      The action.
     * @param EnvironmentInterface $environment The environment.
     *
     * @return CommandInterface|null
     */
    private function getOperation(Action $action, EnvironmentInterface $environment)
    {
        $dataDefinition = $environment->getDataDefinition();
        assert($dataDefinition instanceof ContainerInterface);

        $definition = $dataDefinition->getDefinition(Contao2BackendViewDefinitionInterface::NAME);
        assert($definition instanceof Contao2BackendViewDefinitionInterface);

        $name     = $action->getName();
        $commands = $definition->getModelCommands();

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
        $state = 1 === (int) $inputProvider->getParameter('state');

        if ($isInverse) {
            return $state ? '' : '1';
        }

        return $state ? '1' : '';
    }
}
