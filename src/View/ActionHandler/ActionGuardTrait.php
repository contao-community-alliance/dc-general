<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2019 Contao Community Alliance.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    contao-community-alliance/dc-general
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2013-2019 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\View\ActionHandler;

use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Exception\EditOnlyModeException;
use ContaoCommunityAlliance\DcGeneral\Data\ModelIdInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\ContainerInterface;
use ContaoCommunityAlliance\DcGeneral\Exception\DcGeneralRuntimeException;

/**
 * This traits provides some guards to protect your action handler.
 *
 * @package ContaoCommunityAlliance\DcGeneral\View\ActionHandler
 */
trait ActionGuardTrait
{
    /**
     * Guard that the environment is prepared for models data definition.
     *
     * @param ContainerInterface $definition The definition container.
     * @param ModelIdInterface   $modelId    The model id.
     *
     * @return void
     *
     * @throws DcGeneralRuntimeException If definition name does not match the model id data provider name.
     */
    protected function guardValidEnvironment(ContainerInterface $definition, ModelIdInterface $modelId)
    {
        if ($definition->getName() !== $modelId->getDataProviderName()) {
            throw new DcGeneralRuntimeException(
                \sprintf(
                    'Not able to perform action. Environment is not prepared for model "%s"',
                    $modelId->getSerialized()
                )
            );
        }
    }

    /**
     * Guard that the data container is not in edit only mode.
     *
     * @param ContainerInterface $definition The definition container.
     * @param ModelIdInterface   $modelId    The model id.
     *
     * @return void
     *
     * @throws EditOnlyModeException If data container is in edit only mode.
     */
    protected function guardNotEditOnly(ContainerInterface $definition, ModelIdInterface $modelId)
    {
        if ($definition->getBasicDefinition()->isEditOnlyMode()) {
            throw new EditOnlyModeException($modelId->getDataProviderName());
        }
    }
}
