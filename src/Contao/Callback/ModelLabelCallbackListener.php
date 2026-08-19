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
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2013-2024 Contao Community Alliance.
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
            // Contao hands the prepared label fields over as a fourth argument, and its own
            // callbacks declare them as required - "tl_member::addIcon()" is one of several.
            // Without it, every table carrying such a callback was fatal here. Callbacks that
            // declare only three parameters stay unaffected; PHP passes the surplus argument
            // and they never look at it.
            $event->getArgs(),
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function update($event, $value)
    {
        if (\is_array($value)) {
            /** @var list<string> $value */

            // An array means the callback handed the label fields back - regardless of how the
            // list happens to be grouped. Tying this to the table mode dropped the return value
            // everywhere else, which is why a Contao callback left the label empty.
            $groupingInformation = ViewHelpers::getGroupingMode($event->getEnvironment());
            $isTableMode         =
                isset($groupingInformation['mode'])
                && ($groupingInformation['mode'] === GroupAndSortingInformationInterface::GROUP_NONE);

            $this->updateArguments($event, $value, $isTableMode);

            return;
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
     * Take the label fields a callback returned over into the event.
     *
     * @param ModelToLabelEvent   $event       The event being emitted.
     * @param string|list<string> $arguments   The label arguments.
     * @param bool                $byPosition  Whether numeric keys may be matched by position.
     *
     * @return void
     */
    private function updateArguments(ModelToLabelEvent $event, array|string $arguments, bool $byPosition): void
    {
        if (empty($arguments)) {
            return;
        }

        $updateArguments = $event->getArgs();

        // By name - this always means the same field on both sides.
        foreach ($event->getFormatter()->getPropertyNames() as $propertyName) {
            if (!isset($arguments[$propertyName])) {
                continue;
            }

            $updateArguments[$propertyName] = $arguments[$propertyName];
        }

        // By position - only where both sides describe the same list of columns, which is the
        // table mode. Anywhere else the two have nothing to do with each other: a picker shows
        // the one property it was configured with, while a Contao callback counts along its own
        // "label/fields" - "tl_member" reserves the first of those for the icon and would push
        // that markup into the picker's only column, replacing the value one is meant to pick.
        if (!$byPosition) {
            $event->setArgs($updateArguments);

            return;
        }

        foreach ($event->getFormatter()->getPropertyNames() as $index => $propertyName) {
            if (!isset($arguments[$index])) {
                continue;
            }

            $updateArguments[$propertyName] = $arguments[$index];
        }

        $event->setArgs($updateArguments);
    }
}
