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
 * @author     Tristan Lins <tristan.lins@bit3.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2013-2023 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Contao\Callback;

use ContaoCommunityAlliance\DcGeneral\Contao\Compatibility\DcCompat;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\ModelToLabelEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\ViewHelpers;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\GroupAndSortingInformationInterface;

/**
 * Class ModelLabelCallbackListener.
 *
 * Handle the label_callbacks.
 *
 * @extends AbstractReturningCallbackListener<ModelToLabelEvent>
 */
class ModelLabelCallbackListener extends AbstractReturningCallbackListener
{
    /**
     * {@inheritDoc}
     */
    public function getArgs($event)
    {
        return [
            $event->getModel()->getPropertiesAsArray(),
            $event->getLabel(),
            new DcCompat($event->getEnvironment(), $event->getModel()),
            $event->getArgs()
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function update($event, $value)
    {
        $groupingInformation = ViewHelpers::getGroupingMode($event->getEnvironment());
        if (
            isset($groupingInformation['mode'])
            && ($groupingInformation['mode'] === GroupAndSortingInformationInterface::GROUP_NONE)
        ) {
            if (!\is_array($value)) {
                return;
            }
            /** @var list<string> $value */

            $this->updateTableMode($event, $value);
        }

        if (!\is_string($value)) {
            return;
        }

        $this->updateNonTableMode($event, $value);
    }

    /**
     * Set the value in the event.
     *
     * @param ModelToLabelEvent $event The event being emitted.
     * @param string|null       $value The label text to use.
     *
     * @return void
     */
    private function updateNonTableMode(ModelToLabelEvent $event, ?string $value): void
    {
        if (null === $value) {
            return;
        }

        // HACK: we need to escape all % chars but preserve the %s and the like.
        $value = \str_replace('%', '%%', $value);
        $value = \preg_replace(
            '#%(%([0-9]+\$)?(\'.|0| )?-?([0-9]+)?(.[0-9]+)?(b|c|d|e|E|f|F|g|G|o|s|u|x|X))#',
            '\\1',
            $value
        );

        assert(\is_string($value));
        $event->setLabel($value);
    }

    /**
     * Set the value in the event.
     *
     * @param ModelToLabelEvent   $event     The event being emitted.
     * @param string|list<string> $arguments The label arguments.
     *
     * @return void
     */
    private function updateTableMode(ModelToLabelEvent $event, array|string $arguments): void
    {
        if (empty($arguments)) {
            return;
        }

        $updateArguments = $event->getArgs();

        // Step 1 update arguments by index as propertyName
        foreach ($event->getFormatter()->getPropertyNames() as $index => $propertyName) {
            if (!isset($arguments[$propertyName])) {
                continue;
            }

            $updateArguments[$propertyName] = $arguments[$propertyName];
        }

        // Step 2 update arguments by index as integer
        foreach ($event->getFormatter()->getPropertyNames() as $index => $propertyName) {
            if (!isset($arguments[$index])) {
                continue;
            }

            $updateArguments[$propertyName] = $arguments[$index];
        }

        $event->setArgs($updateArguments);
    }
}
