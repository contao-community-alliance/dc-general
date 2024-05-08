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
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     Richard Henkenjohann <richardhenkenjohann@googlemail.com>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2013-2024 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\ActionHandler;

use ContaoCommunityAlliance\DcGeneral\Contao\DataDefinition\Definition\Contao2BackendViewDefinitionInterface;
use ContaoCommunityAlliance\DcGeneral\Contao\RequestScopeDeterminator;
use ContaoCommunityAlliance\DcGeneral\Contao\RequestScopeDeterminatorAwareTrait;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\BaseView;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\EditMask;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\ViewHelpers;
use ContaoCommunityAlliance\DcGeneral\Data\DataProviderInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\ContainerInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\BackCommand;
use ContaoCommunityAlliance\DcGeneral\Data\DefaultEditInformation;
use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;
use ContaoCommunityAlliance\DcGeneral\Event\ActionEvent;
use ContaoCommunityAlliance\DcGeneral\InputProviderInterface;

/**
 * Class CreateHandler
 *
 * @package ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\ActionHandler
 */
class CreateHandler
{
    use RequestScopeDeterminatorAwareTrait;

    /**
     * The default edit information.
     *
     * @var DefaultEditInformation
     */
    private DefaultEditInformation $editInformation;

    /**
     * CreateHandler constructor.
     *
     * @param RequestScopeDeterminator $scopeDeterminator The request mode determinator.
     * @param DefaultEditInformation   $editInformation   The default edit information.
     */
    public function __construct(RequestScopeDeterminator $scopeDeterminator, DefaultEditInformation $editInformation)
    {
        $this->setScopeDeterminator($scopeDeterminator);
        $this->editInformation = $editInformation;
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

        if ('create' !== $event->getAction()->getName()) {
            return;
        }

        $environment   = $event->getEnvironment();
        $inputProvider = $environment->getInputProvider();
        assert($inputProvider instanceof InputProviderInterface);

        // Only handle if we do not have a manual sorting, or we know where to insert.
        // Manual sorting is handled by clipboard.
        if (
            null !== ViewHelpers::getManualSortingProperty($environment)
            && !$inputProvider->hasParameter('after')
            && !$inputProvider->hasParameter('into')
        ) {
            return;
        }

        if (true !== ($response = $this->checkPermission($environment))) {
            $event->setResponse((string) $response);
            $event->stopPropagation();

            return;
        }

        if ('' !== ($response = $this->process($environment))) {
            $event->setResponse($response);
        }
    }

    /**
     * Handle the action.
     *
     * @param EnvironmentInterface $environment The environment.
     *
     * @return string
     */
    protected function process(EnvironmentInterface $environment)
    {
        $dataProvider = $environment->getDataProvider();
        assert($dataProvider instanceof DataProviderInterface);
        $dataDefinition = $environment->getDataDefinition();
        assert($dataDefinition instanceof ContainerInterface);

        $properties = $dataDefinition->getPropertiesDefinition()->getProperties();
        $model      = $dataProvider->getEmptyModel();
        $clone      = $dataProvider->getEmptyModel();

        // If some of the fields have a default value, set it.
        foreach ($properties as $property) {
            $propName = $property->getName();

            if ((null === $property->getDefaultValue()) || !$dataProvider->fieldExists($propName)) {
                continue;
            }

            $clone->setProperty($propName, $property->getDefaultValue());
            $model->setProperty($propName, $property->getDefaultValue());
        }

        $view = $environment->getView();
        if (!$view instanceof BaseView) {
            return '';
        }

        $provider = $environment->getInputProvider();
        assert($provider instanceof InputProviderInterface);

        if ('select' !== $provider->getParameter('act')) {
            $this->handleGlobalCommands($environment);
        }

        return (new EditMask($view, $model, $clone, null, null, $view->breadcrumb(), $this->editInformation))
            ->execute();
    }

    /**
     * Check permission for create a model.
     *
     * @param EnvironmentInterface $environment The environment.
     *
     * @return string|bool
     */
    private function checkPermission(EnvironmentInterface $environment)
    {
        $dataDefinition = $environment->getDataDefinition();
        assert($dataDefinition instanceof ContainerInterface);

        if (true === $dataDefinition->getBasicDefinition()->isCreatable()) {
            return true;
        }

        return \sprintf(
            '<div style="text-align:center; font-weight:bold; padding:40px;">
                You have no permission for create model in %s.
            </div>',
            $dataDefinition->getName()
        );
    }

    /**
     * Handle the globals commands
     *
     * @param EnvironmentInterface $environment The environment.
     *
     * @return void
     */
    protected function handleGlobalCommands(EnvironmentInterface $environment)
    {
        $dataDefinition = $environment->getDataDefinition();
        assert($dataDefinition instanceof ContainerInterface);

        $backendView = $dataDefinition->getDefinition(Contao2BackendViewDefinitionInterface::NAME);
        assert($backendView instanceof Contao2BackendViewDefinitionInterface);

        $globalCommands = $backendView->getGlobalCommands();

        $globalCommands->clearCommands();

        $backCommand = new BackCommand();
        $backCommand->setDisabled(false);
        $globalCommands->addCommand($backCommand);
    }
}
