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
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Tristan Lins <tristan.lins@bit3.de>
 * @author     David Molineus <mail@netzmacht.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2013-2024 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Contao\Callback;

use Contao\CoreBundle\DataContainer\DataContainerOperation;
use ContaoCommunityAlliance\DcGeneral\Contao\Compatibility\DcCompat;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetOperationButtonEvent;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\CommandInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\ToggleCommandInterface;
use LogicException;

use function sprintf;

/**
 * Class ModelOperationButtonCallbackListener.
 *
 * Handle the button_callbacks.
 *
 * @extends AbstractReturningCallbackListener<GetOperationButtonEvent>
 */
class ModelOperationButtonCallbackListener extends AbstractReturningCallbackListener
{
    /**
     * The name of the operation button to limit execution on.
     *
     * @var null|string
     */
    protected $operationName;

    /**
     * Set the restrictions for this callback.
     *
     * @param null|string $dataContainerName The name of the data container to limit execution on.
     * @param null|string $operationName     The name of the operation button to limit execution on.
     *
     * @return void
     */
    public function setRestrictions($dataContainerName = null, $operationName = null)
    {
        parent::setRestrictions($dataContainerName);
        $this->operationName = $operationName;
    }

    /**
     * {@inheritDoc}
     */
    public function wantToExecute($event)
    {
        if ($event->getCommand() instanceof ToggleCommandInterface) {
            return false;
        }

        return parent::wantToExecute($event)
               && (null === $this->operationName || ($this->operationName === $event->getKey()));
    }

    /**
     * {@inheritDoc}
     */
    public function getArgs($event)
    {
        $command = $event->getCommand();
        assert($command instanceof CommandInterface);
        $extra = $command->getExtra();
        if (null === $definition = $event->getEnvironment()->getDataDefinition()) {
            throw new LogicException('No data definition given.');
        }

        /** @psalm-suppress InternalMethod - Class Adapter is internal, not the __call() method. Blame Contao. */
        return [
            new DataContainerOperation(
                $command->getName(),
                [
                    ($model = $event->getModel()) ? $model->getPropertiesAsArray() : [],
                    $this->buildHref($command),
                    $event->getLabel(),
                    $event->getTitle(),
                    ($extra['icon'] ?? null),
                    $event->getAttributes(),
                    $definition->getName(),
                    $definition->getBasicDefinition()->getRootEntries(),
                    $event->getChildRecordIds(),
                    $event->isCircularReference(),
                    ($previous = $event->getPrevious()) ? $previous->getId() : null,
                    ($next = $event->getNext()) ? $next->getId() : null
                ],
                ($model = $event->getModel()) ? $model->getPropertiesAsArray() : [],
                new DcCompat($event->getEnvironment())
            )
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function update($event, $value)
    {
        if (null === $value) {
            return;
        }

        $event->stopPropagation();
    }

    /**
     * Build reduced href required by legacy callbacks.
     *
     * @param CommandInterface $command The command for which the href shall get built.
     *
     * @return string
     */
    protected function buildHref(CommandInterface $command)
    {
        $arrParameters = (array) $command->getParameters();
        $strHref       = '';

        foreach ($arrParameters as $key => $value) {
            $strHref .= sprintf('&%s=%s', $key, $value);
        }

        return $strHref;
    }
}
