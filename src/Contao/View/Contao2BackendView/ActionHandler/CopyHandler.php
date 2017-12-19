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
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2013-2017 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0
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
use ContaoCommunityAlliance\DcGeneral\Data\ModelId;
use ContaoCommunityAlliance\DcGeneral\Data\ModelIdInterface;
use ContaoCommunityAlliance\DcGeneral\Data\ModelInterface;
use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;
use ContaoCommunityAlliance\DcGeneral\Event\ActionEvent;
use ContaoCommunityAlliance\DcGeneral\Event\PostDuplicateModelEvent;
use ContaoCommunityAlliance\DcGeneral\Event\PreDuplicateModelEvent;
use ContaoCommunityAlliance\DcGeneral\View\ActionHandler\ActionGuardTrait;
use ContaoCommunityAlliance\DcGeneral\View\ActionHandler\CallActionTrait;
use ContaoCommunityAlliance\UrlBuilder\Contao\BackendUrlBuilder;

/**
 * Class CopyModelController handles copy action on a model.
 */
class CopyHandler
{
    use RequestScopeDeterminatorAwareTrait;
    use ActionGuardTrait;
    use CallActionTrait;

    /**
     * PasteHandler constructor.
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

        if (!$event->getEnvironment()->getDataDefinition()->getBasicDefinition()->isCreatable()) {
            return;
        }

        if ($event->getAction()->getName() !== 'copy') {
            return;
        }

        if (true !== ($response = $this->checkPermission($event->getEnvironment()))) {
            $event->setResponse($response);
            $event->stopPropagation();

            return;
        }

        $response = $this->process($event->getEnvironment());
        if ($response !== false) {
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
        if ($environment->getDataDefinition()->getBasicDefinition()->isCreatable()) {
            return;
        }

        if ($redirect) {
            $environment->getEventDispatcher()->dispatch(
                ContaoEvents::SYSTEM_LOG,
                new LogEvent(
                    sprintf(
                        'Table "%s" is not creatable',
                        'DC_General - DefaultController - copy()',
                        $environment->getDataDefinition()->getName()
                    ),
                    __CLASS__ . '::delete()',
                    TL_ERROR
                )
            );

            $environment->getEventDispatcher()->dispatch(
                ContaoEvents::CONTROLLER_REDIRECT,
                new RedirectEvent('contao/main.php?act=error')
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

        // Dispatch pre duplicate event.
        $copyEvent = new PreDuplicateModelEvent($environment, $copyModel, $model);
        $environment->getEventDispatcher()->dispatch($copyEvent::NAME, $copyEvent);

        // Save the copy.
        $provider = $environment->getDataProvider($copyModel->getProviderName());
        $provider->save($copyModel);

        // Dispatch post duplicate event.
        $copyEvent = new PostDuplicateModelEvent($environment, $copyModel, $model);
        $environment->getEventDispatcher()->dispatch($copyEvent::NAME, $copyEvent);

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
        $url = new BackendUrlBuilder();
        $url
            ->setPath('contao/main.php')
            ->setQueryParameter('do', $environment->getInputProvider()->getParameter('do'))
            ->setQueryParameter('table', $copiedModelId->getDataProviderName())
            ->setQueryParameter('act', 'edit')
            ->setQueryParameter('id', $copiedModelId->getSerialized())
            ->setQueryParameter('pid', $environment->getInputProvider()->getParameter('pid'));

        $redirectEvent = new RedirectEvent($url->getUrl());
        $environment->getEventDispatcher()->dispatch(ContaoEvents::CONTROLLER_REDIRECT, $redirectEvent);
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

        $this->guardValidEnvironment($environment->getDataDefinition(), $modelId);
        // We want a redirect here if not creatable.
        $this->guardIsCreatable($environment, $modelId, true);

        if ($environment->getDataDefinition()->getBasicDefinition()->isEditOnlyMode()) {
            return $this->callAction($environment, 'edit');
        }

        // Manual sorting mode. The ClipboardController should pick it up.
        $manualSortingProperty = ViewHelpers::getManualSortingProperty($environment);
        if ($manualSortingProperty && $environment->getDataProvider()->fieldExists($manualSortingProperty)) {
            return false;
        }

        $copiedModel = $this->copy($environment, $modelId);

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
        $dataDefinition  = $environment->getDataDefinition();
        $basicDefinition = $dataDefinition->getBasicDefinition();

        if (true === $basicDefinition->isCreatable()) {
            return true;
        }

        $modelId = ModelId::fromSerialized($environment->getInputProvider()->getParameter('source'));

        return sprintf(
            '<div style="text-align:center; font-weight:bold; padding:40px;">
                You have no permission for copy model %s.
            </div>',
            $modelId->getSerialized()
        );
    }
}
