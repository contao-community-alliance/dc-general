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
 * @author     Cliff Parnitzky <github@cliff-parnitzky.de>
 * @copyright  2013-2024 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\ActionHandler;

use ContaoCommunityAlliance\Contao\Bindings\ContaoEvents;
use ContaoCommunityAlliance\Contao\Bindings\Events\Controller\RedirectEvent;
use ContaoCommunityAlliance\Contao\Bindings\Events\System\LogEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\RequestScopeDeterminator;
use ContaoCommunityAlliance\DcGeneral\Contao\RequestScopeDeterminatorAwareTrait;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Exception\NotCreatableException;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\ViewHelpers;
use ContaoCommunityAlliance\DcGeneral\Controller\ControllerInterface;
use ContaoCommunityAlliance\DcGeneral\Data\DataProviderInterface;
use ContaoCommunityAlliance\DcGeneral\Data\ModelId;
use ContaoCommunityAlliance\DcGeneral\Data\ModelIdInterface;
use ContaoCommunityAlliance\DcGeneral\Data\ModelInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\ContainerInterface;
use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;
use ContaoCommunityAlliance\DcGeneral\Event\ActionEvent;
use ContaoCommunityAlliance\DcGeneral\Event\PostDuplicateModelEvent;
use ContaoCommunityAlliance\DcGeneral\Event\PreDuplicateModelEvent;
use ContaoCommunityAlliance\DcGeneral\Exception\DcGeneralRuntimeException;
use ContaoCommunityAlliance\DcGeneral\View\ActionHandler\ActionGuardTrait;
use ContaoCommunityAlliance\DcGeneral\View\ActionHandler\CallActionTrait;
use ContaoCommunityAlliance\UrlBuilder\Contao\CsrfUrlBuilderFactory;
use ContaoCommunityAlliance\UrlBuilder\UrlBuilder;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Class CopyModelController handles copy action on a model.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CopyHandler
{
    use RequestScopeDeterminatorAwareTrait;
    use ActionGuardTrait;
    use CallActionTrait;

    /**
     * The URL builder factory for URLs with security token.
     *
     * @var CsrfUrlBuilderFactory
     */
    private CsrfUrlBuilderFactory $securityUrlBuilder;

    /**
     * The request stack.
     *
     * @var RequestStack
     */
    private RequestStack $requestStack;

    /**
     * The URL generator.
     *
     * @var UrlGeneratorInterface
     */
    private UrlGeneratorInterface $urlGenerator;

    /**
     * PasteHandler constructor.
     *
     * @param RequestScopeDeterminator $scopeDeterminator  The request mode determinator.
     * @param CsrfUrlBuilderFactory    $securityUrlBuilder The URL builder factory for URLs with security token.
     * @param RequestStack             $requestStack       The request stack.
     * @param UrlGeneratorInterface    $urlGenerator       The URL generator.
     */
    public function __construct(
        RequestScopeDeterminator $scopeDeterminator,
        CsrfUrlBuilderFactory $securityUrlBuilder,
        RequestStack $requestStack,
        UrlGeneratorInterface $urlGenerator,
    ) {
        $this->setScopeDeterminator($scopeDeterminator);

        $this->securityUrlBuilder = $securityUrlBuilder;
        $this->requestStack       = $requestStack;
        $this->urlGenerator       = $urlGenerator;
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

        if (null === ($definition = $environment->getDataDefinition())) {
            return;
        }

        if (!$definition->getBasicDefinition()->isCreatable()) {
            return;
        }

        if ('copy' !== $event->getAction()->getName()) {
            return;
        }

        if (true !== ($response = $this->checkPermission($environment))) {
            $event->setResponse((string) $response);
            $event->stopPropagation();

            return;
        }

        if (false !== ($response = $this->process($environment))) {
            $event->setResponse($response);
        }
    }

    /**
     * Check if is it allowed to create a new record. This is necessary to create the copy.
     *
     * @param EnvironmentInterface $environment The environment.
     * @param ModelIdInterface     $modelId     The model id.
     * @param bool                 $redirect    If true it redirects to error page instead of throwing an exception.
     *
     * @return void
     *
     * @throws NotCreatableException If deletion is disabled.
     */
    protected function guardIsCreatable(EnvironmentInterface $environment, ModelIdInterface $modelId, $redirect = false)
    {
        $dataDefinition = $environment->getDataDefinition();
        assert($dataDefinition instanceof ContainerInterface);

        if ($dataDefinition->getBasicDefinition()->isCreatable()) {
            return;
        }

        if ($redirect) {
            $eventDispatcher = $environment->getEventDispatcher();
            assert($eventDispatcher instanceof EventDispatcherInterface);

            $eventDispatcher->dispatch(
                new LogEvent(
                    \sprintf(
                        'Table "%s" is not creatable, DC_General - DefaultController - copy()',
                        $dataDefinition->getName()
                    ),
                    __CLASS__ . '::delete()',
                    'ERROR'
                ),
                ContaoEvents::SYSTEM_LOG
            );

            $eventDispatcher->dispatch(
                new RedirectEvent('contao?act=error'),
                ContaoEvents::CONTROLLER_REDIRECT
            );
        }

        throw new NotCreatableException($modelId->getDataProviderName());
    }

    /**
     * Copy a model by using.
     *
     * @param EnvironmentInterface $environment The environment.
     * @param ModelIdInterface     $modelId     The model id.
     *
     * @return ModelInterface
     */
    public function copy(EnvironmentInterface $environment, ModelIdInterface $modelId)
    {
        $definition = $environment->getDataDefinition();
        assert($definition instanceof ContainerInterface);

        $this->guardNotEditOnly($definition, $modelId);
        $this->guardIsCreatable($environment, $modelId);

        $dataProvider = $environment->getDataProvider();
        assert($dataProvider instanceof DataProviderInterface);
        $model = $dataProvider->fetch($dataProvider->getEmptyConfig()->setId($modelId->getId()));

        if (!$model) {
            throw new DcGeneralRuntimeException(
                'Model not found with ID ' . $modelId->getId()
            );
        }

        $controller = $environment->getController();
        assert($controller instanceof ControllerInterface);
        // We need to keep the original data here.
        $copyModel = $controller->createClonedModel($model);

        $dispatcher = $environment->getEventDispatcher();
        if (null !== $dispatcher) {
            // Dispatch pre duplicate event.
            $preCopyEvent = new PreDuplicateModelEvent($environment, $copyModel, $model);
            $dispatcher->dispatch($preCopyEvent, $preCopyEvent::NAME);
        }

        // Save the copy.
        $dataProvider = $environment->getDataProvider($copyModel->getProviderName());
        assert($dataProvider instanceof DataProviderInterface);
        $dataProvider->save($copyModel);

        if (null !== $dispatcher) {
            // Dispatch post duplicate event.
            $postCopyEvent = new PostDuplicateModelEvent($environment, $copyModel, $model);
            $dispatcher->dispatch($postCopyEvent, $postCopyEvent::NAME);
        }

        return $copyModel;
    }

    /**
     * Redirect to edit mask.
     *
     * @param EnvironmentInterface $environment   The environment.
     * @param ModelIdInterface     $copiedModelId The model id.
     *
     * @return void
     */
    protected function redirect($environment, $copiedModelId)
    {
        if (null === ($inputProvider = $environment->getInputProvider())) {
            return;
        }

        if (null === ($dispatcher = $environment->getEventDispatcher())) {
            return;
        }

        $request   = $this->requestStack->getCurrentRequest();
        $routeName = $request?->attributes->get('_route');
        // Build a clean url to remove the copy related arguments instead of using the AddToUrlEvent.
        $urlBuilder = new UrlBuilder();
        if ($routeName !== 'contao_backend') {
            $params = [
                'tableName' => $copiedModelId->getDataProviderName(),
                'act'       => 'edit',
                'id'        => $copiedModelId->getSerialized(),
            ];
            if (null !== ($pid = $inputProvider->getParameter('pid'))) {
                $params['pid'] = $pid;
            }
            $url = $this->urlGenerator->generate($routeName, $params);
        } else {
            $urlBuilder
                ->setPath('contao')
                ->setQueryParameter('do', $inputProvider->getParameter('do'))
                ->setQueryParameter('table', $copiedModelId->getDataProviderName())
                ->setQueryParameter('act', 'edit')
                ->setQueryParameter('id', $copiedModelId->getSerialized());
            if (null !== ($pid = $inputProvider->getParameter('pid'))) {
                $urlBuilder->setQueryParameter('pid', $pid);
            }
            $url = $urlBuilder->getUrl();
        }
        $redirectEvent = new RedirectEvent($this->securityUrlBuilder->create($url)->getUrl());

        $dispatcher->dispatch($redirectEvent, ContaoEvents::CONTROLLER_REDIRECT);
    }

    /**
     * Process the action.
     *
     * @param EnvironmentInterface $environment Current dc-general environment.
     *
     * @return string|false|null
     */
    protected function process(EnvironmentInterface $environment)
    {
        if (null === ($inputProvider = $environment->getInputProvider())) {
            return false;
        }

        $modelId = ModelId::fromSerialized($inputProvider->getParameter('source'));

        if (null === ($definition = $environment->getDataDefinition())) {
            return false;
        }

        $this->guardValidEnvironment($definition, $modelId);
        // We want a redirect here if not creatable.
        $this->guardIsCreatable($environment, $modelId, true);

        if ($definition->getBasicDefinition()->isEditOnlyMode()) {
            return $this->callAction($environment, 'edit');
        }

        // Manual sorting mode. The ClipboardController should pick it up.
        $manualSorting = ViewHelpers::getManualSortingProperty($environment);

        if (null === ($provider = $environment->getDataProvider())) {
            return false;
        }

        if (null !== $manualSorting && $provider->fieldExists($manualSorting)) {
            return false;
        }

        $copiedModel = $this->copy($environment, $modelId);

        // If edit several don´t redirect do home.
        if ('select' === $inputProvider->getParameter('act')) {
            return false;
        }

        $this->redirect($environment, ModelId::fromModel($copiedModel));

        return null;
    }

    /**
     * Check permission for copy a model.
     *
     * @param EnvironmentInterface $environment The environment.
     *
     * @return string|bool
     */
    private function checkPermission(EnvironmentInterface $environment): bool|string
    {
        if (null === ($definition = $environment->getDataDefinition())) {
            return false;
        }

        if (true === $definition->getBasicDefinition()->isCreatable()) {
            return true;
        }

        if (null === ($inputProvider = $environment->getInputProvider())) {
            return '';
        }

        return \sprintf(
            '<div style="text-align:center; font-weight:bold; padding:40px;">
                You have no permission for copy model %s.
            </div>',
            ModelId::fromSerialized($inputProvider->getParameter('source'))->getSerialized()
        );
    }
}
