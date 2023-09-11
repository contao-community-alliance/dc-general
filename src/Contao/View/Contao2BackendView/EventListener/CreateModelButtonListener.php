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
 * @copyright  2013-2023 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\EventListener;

use ContaoCommunityAlliance\DcGeneral\Clipboard\ClipboardInterface;
use ContaoCommunityAlliance\DcGeneral\Clipboard\Filter;
use ContaoCommunityAlliance\DcGeneral\Contao\RequestScopeDeterminatorAwareTrait;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetGlobalButtonEvent;
use ContaoCommunityAlliance\DcGeneral\Data\ModelId;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\ContainerInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\BasicDefinitionInterface;
use ContaoCommunityAlliance\DcGeneral\InputProviderInterface;
use ContaoCommunityAlliance\UrlBuilder\UrlBuilder;

/**
 * This handles the add button event in list views.
 */
class CreateModelButtonListener
{
    use RequestScopeDeterminatorAwareTrait;

    /**
     * Handle the event.
     *
     * @param GetGlobalButtonEvent $event The event.
     *
     * @return void
     */
    public function handle(GetGlobalButtonEvent $event)
    {
        if (!$this->getScopeDeterminator()->currentScopeIsBackend()) {
            return;
        }

        if ('button_new' !== $event->getKey()) {
            return;
        }

        $environment     = $event->getEnvironment();

        $definition = $environment->getDataDefinition();
        assert($definition instanceof ContainerInterface);

        $basicDefinition = $definition->getBasicDefinition();
        assert($basicDefinition instanceof BasicDefinitionInterface);

        $mode = $basicDefinition->getMode();

        if (!$basicDefinition->isCreatable()) {
            $event->setHtml('');

            return;
        }

        if (
            (BasicDefinitionInterface::MODE_PARENTEDLIST === $mode)
            || (BasicDefinitionInterface::MODE_HIERARCHICAL === $mode)
        ) {
            $filter = new Filter();

            $provider = $basicDefinition->getDataProvider();
            assert(\is_string($provider));

            $filter->andModelIsFromProvider($provider);
            if ($parentProviderName = $basicDefinition->getParentDataProvider()) {
                $filter->andParentIsFromProvider($parentProviderName);
            } else {
                $filter->andHasNoParent();
            }

            $clipboard = $environment->getClipboard();
            assert($clipboard instanceof ClipboardInterface);

            if ($clipboard->isNotEmpty($filter)) {
                $event->setHtml('');

                return;
            }
        }

        $url = UrlBuilder::fromUrl($event->getHref());

        $inputProvider = $environment->getInputProvider();
        assert($inputProvider instanceof InputProviderInterface);

        if ($serializedPid = $inputProvider->getParameter('pid')) {
            $url->setQueryParameter('pid', ModelId::fromSerialized($serializedPid)->getSerialized());
        }

        $event->setHref($url->getUrl());
    }
}
