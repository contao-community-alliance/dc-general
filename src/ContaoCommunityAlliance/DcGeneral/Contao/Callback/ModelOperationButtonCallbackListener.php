<?php
/**
 * PHP version 5
 *
 * @package    generalDriver
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Tristan Lins <tristan.lins@bit3.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Contao\Callback;

use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetOperationButtonEvent;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\CommandInterface;

/**
 * Class ModelOperationButtonCallbackListener.
 *
 * Handle the button_callbacks.
 *
 * @package DcGeneral\Contao\Callback
 */
class ModelOperationButtonCallbackListener extends AbstractReturningCallbackListener
{
    /**
     * The name of the operation button to limit execution on.
     *
     * @var null|string
     */
    protected $operationName = null;

    /**
     * Set the restrictions for this callback.
     *
     * @param null|string $dataContainerName The name of the data container to limit execution on.
     *
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
     * Check the restrictions against the information within the event and determine if the callback shall be executed.
     *
     * @param GetOperationButtonEvent $event The Event for which the callback shall be invoked.
     *
     * @return bool
     */
    public function wantToExecute($event)
    {
        return parent::wantToExecute($event)
        && (empty($this->operationName)
            || ($event->getKey() == $this->operationName)
        );
    }

    /**
     * Retrieve the arguments for the callback.
     *
     * @param GetOperationButtonEvent $event The event being emitted.
     *
     * @return array
     */
    public function getArgs($event)
    {
        $extra = $event->getCommand()->getExtra();

        return array(
            $event->getModel()->getPropertiesAsArray(),
            $this->buildHref($event->getCommand()),
            $event->getLabel(),
            $event->getTitle(),
            isset($extra['icon']) ? $extra['icon'] : null,
            $event->getAttributes(),
            $event->getEnvironment()->getDataDefinition()->getName(),
            $event->getEnvironment()->getDataDefinition()->getBasicDefinition()->getRootEntries(),
            $event->getChildRecordIds(),
            $event->getCircularReference(),
            $event->getPrevious() ? $event->getPrevious()->getId() : null,
            $event->getNext() ? $event->getNext()->getId() : null
        );
    }

    /**
     * Set the value in the event.
     *
     * @param GetOperationButtonEvent $event The event being emitted.
     *
     * @param string                  $value The value returned by the callback.
     *
     * @return void
     */
    public function update($event, $value)
    {
        if ($value === null) {
            return;
        }

        $event->setHtml($value);
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
        $arrParameters = (array)$command->getParameters();
        $strHref       = '';

        foreach ($arrParameters as $key => $value) {
            $strHref .= sprintf('&%s=%s', $key, $value);
        }

        return $strHref;
    }
}
