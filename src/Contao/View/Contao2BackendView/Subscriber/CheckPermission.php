<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2018 Contao Community Alliance.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    contao-community-alliance/dc-general
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @copyright  2013-2018 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Subscriber;

use ContaoCommunityAlliance\DcGeneral\Contao\DataDefinition\Definition\Contao2BackendViewDefinitionInterface;
use ContaoCommunityAlliance\DcGeneral\Contao\RequestScopeDeterminator;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\CommandCollectionInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\ToggleCommandInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Condition\Property\BooleanCondition;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Condition\Property\PropertyConditionChain;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\PropertyInterface;
use ContaoCommunityAlliance\DcGeneral\Factory\Event\BuildDataDefinitionEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * The check permission subscriber.
 */
class CheckPermission implements EventSubscriberInterface
{
    /**
     * The request mode determinator.
     *
     * @var RequestScopeDeterminator
     */
    private $scopeDeterminator;

    /**
     * ClipboardController constructor.
     *
     * @param RequestScopeDeterminator $scopeDeterminator
     */
    public function __construct(RequestScopeDeterminator $scopeDeterminator)
    {
        $this->scopeDeterminator = $scopeDeterminator;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            BuildDataDefinitionEvent::NAME => [
                ['checkPermissionForProperties'],
                ['checkPermissionIsCreatable', -1],
                ['checkPermissionIsEditable', -1],
                ['checkPermissionIsDeletable', -1]
            ]
        ];
    }

    /**
     * Check permission for properties by user alexf.
     *
     * @param BuildDataDefinitionEvent $event The event.
     *
     * @return void
     */
    public function checkPermissionForProperties(BuildDataDefinitionEvent $event)
    {
        if (!$this->scopeDeterminator->currentScopeIsBackend()) {
            return;
        }

        $container          = $event->getContainer();
        $properties         = $container->getPropertiesDefinition();
        $palettesDefinition = $container->getPalettesDefinition();
        $palettes           = $palettesDefinition->getPalettes();

        foreach ($palettes as $palette) {
            foreach ($palette->getProperties() as $property) {
                if (!$properties->hasProperty($name = $property->getName())) {
                    // @codingStandardsIgnoreStart
                    @\trigger_error(
                        \sprintf(
                            'Warning: unknown property "%s" in palette: %s',
                            $name,
                            $palette->getName()
                        ),
                        E_USER_WARNING
                    );
                    // @codingStandardsIgnoreEnd
                    continue;
                }

                $chain = $this->getVisibilityConditionChain($property);

                $chain->addCondition(new BooleanCondition(!$properties->getProperty($name)->isExcluded()));
            }
        }
    }

    /**
     * Check permission is editable.
     *
     * @param BuildDataDefinitionEvent $event The event.
     *
     * @return void
     */
    public function checkPermissionIsEditable(BuildDataDefinitionEvent $event)
    {
        if (!$this->scopeDeterminator->currentScopeIsBackend()) {
            return;
        }

        $container       = $event->getContainer();
        $basicDefinition = $container->getBasicDefinition();

        if ($basicDefinition->isEditable()) {
            return;
        }

        $view          = $container->getDefinition(Contao2BackendViewDefinitionInterface::NAME);
        $modelCommands = $view->getModelCommands();

        $this->disableCommandByActionName($modelCommands, 'edit');
        $this->disableCommandByActionName($modelCommands, 'cut');
        $this->disableToggleCommand($modelCommands);
    }

    /**
     * Check permission is deletable.
     *
     * @param BuildDataDefinitionEvent $event The event.
     *
     * @return void
     */
    public function checkPermissionIsDeletable(BuildDataDefinitionEvent $event)
    {
        if (!$this->scopeDeterminator->currentScopeIsBackend()) {
            return;
        }

        $container       = $event->getContainer();
        $basicDefinition = $container->getBasicDefinition();

        if ($basicDefinition->isDeletable()) {
            return;
        }

        $view          = $container->getDefinition(Contao2BackendViewDefinitionInterface::NAME);
        $modelCommands = $view->getModelCommands();

        $this->disableCommandByActionName($modelCommands, 'delete');
    }

    /**
     * Check permission is creatable.
     *
     * @param BuildDataDefinitionEvent $event The event.
     *
     * @return void
     */
    public function checkPermissionIsCreatable(BuildDataDefinitionEvent $event)
    {
        if (!$this->scopeDeterminator->currentScopeIsBackend()) {
            return;
        }

        $container       = $event->getContainer();
        $basicDefinition = $container->getBasicDefinition();

        if ($basicDefinition->isCreatable()) {
            return;
        }

        $view          = $container->getDefinition(Contao2BackendViewDefinitionInterface::NAME);
        $modelCommands = $view->getModelCommands();

        $this->disableCommandByActionName($modelCommands, 'copy');
    }

    /**
     * Retrieve the visibility condition chain or create an empty one if none exists.
     *
     * @param PropertyInterface $property The property.
     *
     * @return PropertyConditionChain
     */
    private function getVisibilityConditionChain($property)
    {
        $chain = $property->getVisibleCondition();
        if ($chain
            && ($chain instanceof PropertyConditionChain)
            && $chain->getConjunction() === PropertyConditionChain::AND_CONJUNCTION
        ) {
            return $chain;
        }

        $chain = new PropertyConditionChain($chain ? [$chain] : []);
        $property->setVisibleCondition($chain);

        return $chain;
    }

    /**
     * Disable command by action name.
     *
     * @param CommandCollectionInterface $commands   The commands collection.
     * @param string                     $actionName The action name.
     *
     * @return void
     */
    private function disableCommandByActionName(CommandCollectionInterface $commands, $actionName)
    {
        foreach ($commands->getCommands() as $command) {
            $parameters = $command->getParameters()->getArrayCopy();

            $disableCommand = false;

            if (\array_key_exists('act', $parameters)
                && $parameters['act'] === $actionName
            ) {
                $disableCommand = true;
            }

            if (!$disableCommand && $command->getName() === $actionName) {
                $disableCommand = true;
            }

            if (!$disableCommand) {
                continue;
            }

            $command->setDisabled();
        }
    }

    /**
     * Disable the toggle command.
     *
     * @param CommandCollectionInterface $commands The commands collection.
     *
     * @return void
     */
    private function disableToggleCommand(CommandCollectionInterface $commands)
    {
        foreach ($commands->getCommands() as $command) {
            if (!($command instanceof ToggleCommandInterface)) {
                continue;
            }

            $this->disableCommandByActionName($commands, $command->getName());
        }
    }
}
