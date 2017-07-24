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

namespace ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\EventListener;

use ContaoCommunityAlliance\DcGeneral\Clipboard\Filter;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetGlobalButtonEvent;
use ContaoCommunityAlliance\DcGeneral\Data\ModelId;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\BasicDefinitionInterface;
use ContaoCommunityAlliance\UrlBuilder\UrlBuilder;
use Symfony\Component\DependencyInjection\ResettableContainerInterface;

/**
 * This handles the add button event in list views.
 */
class CreateModelButtonListener
{
    /**
     * The request mode if contao is in frontend or backend mode.
     *
     * @var string
     */
    private $requestMode = '';

    /**
     * CreateModelButtonListener constructor.
     *
     * @param ResettableContainerInterface $container
     */
    public function __construct(ResettableContainerInterface $container)
    {
        $requestStack   = $container->get('request_stack');
        $currentRequest = $requestStack->getCurrentRequest();
        if (null === $currentRequest) {
            return;
        }

        $scopeMatcher = $container->get('contao.routing.scope_matcher');

        if ($scopeMatcher->isBackendRequest($currentRequest)) {
            $this->requestMode = 'BE';
        }

        if ($scopeMatcher->isFrontendRequest($currentRequest)) {
            $this->requestMode = 'FE';
        }
    }

    /**
     * Handle the event.
     *
     * @param GetGlobalButtonEvent $event The event.
     *
     * @return void
     */
    public function handle(GetGlobalButtonEvent $event)
    {
        if ('BE' !== $this->requestMode) {
            return;
        }

        if ('button_new' !== $event->getKey()) {
            return;
        }

        $environment     = $event->getEnvironment();
        $definition      = $environment->getDataDefinition();
        $basicDefinition = $definition->getBasicDefinition();
        $mode            = $basicDefinition->getMode();

        if (!$basicDefinition->isCreatable()) {
            $event->setHtml('');
            return;
        }

        if (($mode == BasicDefinitionInterface::MODE_PARENTEDLIST)
            || ($mode == BasicDefinitionInterface::MODE_HIERARCHICAL)
        ) {
            $filter = new Filter();
            $filter->andModelIsFromProvider($basicDefinition->getDataProvider());
            if ($parentDataProviderName = $basicDefinition->getParentDataProvider()) {
                $filter->andParentIsFromProvider($parentDataProviderName);
            } else {
                $filter->andHasNoParent();
            }

            if ($environment->getClipboard()->isNotEmpty($filter)) {
                $event->setHtml('');
                return;
            }
        }

        $url = UrlBuilder::fromUrl($event->getHref());
        if ($serializedPid = $environment->getInputProvider()->getParameter('pid')) {
            $url->setQueryParameter('pid', ModelId::fromSerialized($serializedPid)->getSerialized());
        }

        $event->setHref($url->getUrl());
    }
}
