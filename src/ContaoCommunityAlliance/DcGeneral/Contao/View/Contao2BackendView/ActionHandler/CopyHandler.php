<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2015 Contao Community Alliance.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    contao-community-alliance/dc-general
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @copyright  2013-2015 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\ActionHandler;

use ContaoCommunityAlliance\Contao\Bindings\ContaoEvents;
use ContaoCommunityAlliance\Contao\Bindings\Events\Controller\RedirectEvent;
use ContaoCommunityAlliance\Contao\Bindings\Events\System\LogEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Exception\NotCreatableException;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\ViewHelpers;
use ContaoCommunityAlliance\DcGeneral\Data\ModelId;
use ContaoCommunityAlliance\DcGeneral\Data\ModelIdInterface;
use ContaoCommunityAlliance\DcGeneral\Data\ModelInterface;
use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;
use ContaoCommunityAlliance\DcGeneral\Event\PostDuplicateModelEvent;
use ContaoCommunityAlliance\DcGeneral\Event\PreDuplicateModelEvent;
use ContaoCommunityAlliance\DcGeneral\View\ActionHandler\AbstractEnvironmentAwareHandler;
use ContaoCommunityAlliance\UrlBuilder\Contao\BackendUrlBuilder;

/**
 * Class CopyModelController handles copy action on a model.
 */
class CopyHandler extends AbstractEnvironmentAwareHandler
{
    /**
     * Check if is it allowed to create a new record. This is necessary to create the copy.
     *
     * @param ModelIdInterface $modelId  The model id.
     * @param bool             $redirect If true it redirects to error page instead of throwing an exception.
     *
     * @return void
     *
     * @throws NotCreatableException If deletion is disabled.
     */
    protected function guardIsCreatable(ModelIdInterface $modelId, $redirect = false)
    {
        if ($this->getEnvironment()->getDataDefinition()->getBasicDefinition()->isCreatable()) {
            return;
        }

        if ($redirect) {
            $this->getEnvironment()->getEventDispatcher()->dispatch(
                ContaoEvents::SYSTEM_LOG,
                new LogEvent(
                    sprintf(
                        'Table "%s" is not creatable',
                        'DC_General - DefaultController - copy()',
                        $this->getEnvironment()->getDataDefinition()->getName()
                    ),
                    __CLASS__ . '::delete()',
                    TL_ERROR
                )
            );

            $this->getEnvironment()->getEventDispatcher()->dispatch(
                ContaoEvents::CONTROLLER_REDIRECT,
                new RedirectEvent('contao/main.php?act=error')
            );
        }

        throw new NotCreatableException($modelId->getDataProviderName());
    }

    /**
     * Copy a model by using.
     *
     * @param ModelIdInterface  $modelId   The model id.
     *
     * @return ModelInterface
     */
    public function copy(ModelIdInterface $modelId)
    {
        $this->guardNotEditOnly($modelId);
        $this->guardIsCreatable($modelId);

        $environment  = $this->getEnvironment();
        $dataProvider = $environment->getDataProvider();
        $model        = $dataProvider->fetch($dataProvider->getEmptyConfig()->setId($modelId->getId()));

        // We need to keep the original data here.
        $copyModel = $environment->getController()->createClonedModel($model);

        // Dispatch pre duplicate event.
        $copyEvent = new PreDuplicateModelEvent($environment, $copyModel, $model);
        $environment->getEventDispatcher()->dispatch($copyEvent::NAME, $copyEvent);

        // Save the copy.
        $provider = $this->getEnvironment()->getDataProvider($copyModel->getProviderName());
        $provider->save($copyModel);

        // FIXME: Copy each language multi language models.
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
        // Build a clean url to remove the copy related arguments instad of using the AddToUrlEvent.
        $url = new BackendUrlBuilder();
        $url
            ->setPath('contao/main.php')
            ->setQueryParameter('do', $environment->getInputProvider()->getParameter('do'))
            ->setQueryParameter('table', $copiedModelId->getDataProviderName())
            ->setQueryParameter('act', 'edit')
            ->setQueryParameter('id', $copiedModelId->getSerialized());

        $redirectEvent = new RedirectEvent($url->getUrl());
        $environment->getEventDispatcher()->dispatch(ContaoEvents::CONTROLLER_REDIRECT, $redirectEvent);
    }

    /**
     * {@inheritdoc}
     */
    public function process()
    {
        $event = $this->getEvent();
        if ($event->getAction()->getName() !== 'copy') {
            return;
        }

        $environment = $this->getEnvironment();
        $modelId     = ModelId::fromSerialized($environment->getInputProvider()->getParameter('source'));

        $this->guardValidEnvironment($modelId);
        // We want a redirect here if not creatable.
        $this->guardIsCreatable($modelId, true);

        if ($this->isEditOnlyResponse()) {
            return;
        }

        // Manual sorting mode. The ClipboardController should pick it up.
        $manualSortingProperty = ViewHelpers::getManualSortingProperty($environment);
        if ($manualSortingProperty && $this->environment->getDataProvider()->fieldExists($manualSortingProperty)) {
            return;
        }

        $copiedModel = $this->copy($modelId);

        $this->redirect($environment, ModelId::fromModel($copiedModel));
    }
}
