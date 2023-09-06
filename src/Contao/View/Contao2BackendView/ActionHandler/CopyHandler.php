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
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     Richard Henkenjohann <richardhenkenjohann@googlemail.com>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2013-2023 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\ActionHandler;

use Contao\System;
use ContaoCommunityAlliance\Contao\Bindings\ContaoEvents;
use ContaoCommunityAlliance\Contao\Bindings\Events\Controller\RedirectEvent;
use ContaoCommunityAlliance\Contao\Bindings\Events\System\LogEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\RequestScopeDeterminator;
use ContaoCommunityAlliance\DcGeneral\Contao\RequestScopeDeterminatorAwareTrait;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Exception\NotCreatableException;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\ViewHelpers;
use ContaoCommunityAlliance\DcGeneral\Data\ModelId;
use ContaoCommunityAlliance\DcGeneral\Data\ModelIdInterface;
use ContaoCommunityAlliance\DcGeneral\Data\ModelInterface;
use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;
use ContaoCommunityAlliance\DcGeneral\Event\ActionEvent;
use ContaoCommunityAlliance\DcGeneral\Event\PostDuplicateModelEvent;
use ContaoCommunityAlliance\DcGeneral\Event\PreDuplicateModelEvent;
use ContaoCommunityAlliance\DcGeneral\View\ActionHandler\ActionGuardTrait;
use ContaoCommunityAlliance\DcGeneral\View\ActionHandler\CallActionTrait;
use ContaoCommunityAlliance\UrlBuilder\Contao\CsrfUrlBuilderFactory;
use ContaoCommunityAlliance\UrlBuilder\UrlBuilder;

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
    private $securityUrlBuilder;

    /**
     * PasteHandler constructor.
     *
     * @param RequestScopeDeterminator $scopeDeterminator  The request mode determinator.
     * @param CsrfUrlBuilderFactory    $securityUrlBuilder The URL builder factory for URLs with security token.
     */
    public function __construct(RequestScopeDeterminator $scopeDeterminator, CsrfUrlBuilderFactory $securityUrlBuilder)
    {
        $this->setScopeDeterminator($scopeDeterminator);

        $this->securityUrlBuilder = $securityUrlBuilder;
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
        if (!$environment->getDataDefinition()->getBasicDefinition()->isCreatable()) {
            return;
        }

        if ('copy' !== $event->getAction()->getName()) {
            return;
        }

        if (true !== ($response = $this->checkPermission($environment))) {
            $event->setResponse($response);
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
        if ($dataDefinition->getBasicDefinition()->isCreatable()) {
            return;
        }

        if ($redirect) {
            $eventDispatcher = $environment->getEventDispatcher();

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
        $this->guardNotEditOnly($environment->getDataDefinition(), $modelId);
        $this->guardIsCreatable($environment, $modelId);

        $dataProvider = $environment->getDataProvider();
        $model        = $dataProvider->fetch($dataProvider->getEmptyConfig()->setId($modelId->getId()));

        // We need to keep the original data here.
        $copyModel = $environment->getController()->createClonedModel($model);

        $eventDispatcher = $environment->getEventDispatcher();
        // Dispatch pre duplicate event.
        $preCopyEvent = new PreDuplicateModelEvent($environment, $copyModel, $model);
        $eventDispatcher->dispatch($preCopyEvent, $preCopyEvent::NAME);

        // Save the copy.
        $environment->getDataProvider($copyModel->getProviderName())->save($copyModel);

        // Dispatch post duplicate event.
        $postCopyEvent = new PostDuplicateModelEvent($environment, $copyModel, $model);
        $eventDispatcher->dispatch($postCopyEvent, $postCopyEvent::NAME);

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
        // Build a clean url to remove the copy related arguments instead of using the AddToUrlEvent.
        $urlBuilder = new UrlBuilder();
        $urlBuilder
            ->setPath('contao')
            ->setQueryParameter('do', $environment->getInputProvider()->getParameter('do'))
            ->setQueryParameter('table', $copiedModelId->getDataProviderName())
            ->setQueryParameter('act', 'edit')
            ->setQueryParameter('id', $copiedModelId->getSerialized())
            ->setQueryParameter('pid', $environment->getInputProvider()->getParameter('pid'));

        $redirectEvent = new RedirectEvent($this->securityUrlBuilder->create($urlBuilder->getUrl())->getUrl());
        $environment->getEventDispatcher()->dispatch($redirectEvent, ContaoEvents::CONTROLLER_REDIRECT);
    }

    /**
     * Process the action.
     *
     * @param EnvironmentInterface $environment Current dc-general environment.
     *
     * @return string|bool|null
     */
    protected function process(EnvironmentInterface $environment)
    {
        $modelId = ModelId::fromSerialized($environment->getInputProvider()->getParameter('source'));

        $dataDefinition = $environment->getDataDefinition();
        $this->guardValidEnvironment($dataDefinition, $modelId);
        // We want a redirect here if not creatable.
        $this->guardIsCreatable($environment, $modelId, true);

        if ($dataDefinition->getBasicDefinition()->isEditOnlyMode()) {
            return $this->callAction($environment, 'edit');
        }

        // Manual sorting mode. The ClipboardController should pick it up.
        $manualSorting = ViewHelpers::getManualSortingProperty($environment);
        if ($manualSorting && $environment->getDataProvider()->fieldExists($manualSorting)) {
            return false;
        }

        $copiedModel = $this->copy($environment, $modelId);

        // If edit several don´t redirect do home.
        if ('select' === $environment->getInputProvider()->getParameter('act')) {
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
    private function checkPermission(EnvironmentInterface $environment)
    {
        if (true === $environment->getDataDefinition()->getBasicDefinition()->isCreatable()) {
            return true;
        }

        return \sprintf(
            '<div style="text-align:center; font-weight:bold; padding:40px;">
                You have no permission for copy model %s.
            </div>',
            ModelId::fromSerialized($environment->getInputProvider()->getParameter('source'))->getSerialized()
        );
    }
}
