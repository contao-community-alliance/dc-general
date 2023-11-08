<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2020 Contao Community Alliance.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    contao-community-alliance/dc-general
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2013-2020 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

declare(strict_types=1);

namespace ContaoCommunityAlliance\DcGeneral\Contao\Cache\Http;

use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;
use ContaoCommunityAlliance\DcGeneral\Event\AbstractModelAwareEvent;
use ContaoCommunityAlliance\DcGeneral\Factory\DcGeneralFactory;
use LogicException;

/**
 * The persist invalidate http cache tags, is for a model be deleted.
 */
final class DeleteModelInvalidateCacheTags extends AbstractInvalidateCacheTags
{
    /**
     * {@inheritDoc}
     */
    protected function getEnvironment(AbstractModelAwareEvent $event): EnvironmentInterface
    {
        $environment = $event->getEnvironment();
        $definition = $environment->getDataDefinition();
        $providerName = $event->getModel()->getProviderName();
        if (
            $definition
            && $definition->getBasicDefinition()->getDataProvider() === $providerName
        ) {
            return $environment;
        }
        if (null === $dispatcher = $environment->getEventDispatcher()) {
            throw new LogicException('No event dispatcher given');
        }
        if (null === $translator = $environment->getTranslator()) {
            throw new LogicException('No translator given');
        }
        return $this->factory
            ->createFactory()
            ->setContainerName($providerName)
            ->setEventDispatcher($dispatcher)
            ->setTranslator($translator)
            ->createDcGeneral()
            ->getEnvironment();
    }
}
