<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2026 Contao Community Alliance.
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
 * @copyright  2013-2026 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View;

use ContaoCommunityAlliance\DcGeneral\Exception\DcGeneralInvalidArgumentException;

/**
 * Class CommandCollection.
 *
 * Implementation of a command collection.
 *
 * @api
 */
class CommandCollection implements CommandCollectionInterface
{
    /**
     * The commands contained within the collection.
     *
     * @var array<string, CommandInterface>
     */
    protected array $commands = [];

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function clearCommands()
    {
        $this->commands = [];

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function setCommands(array $commands)
    {
        $this->clearCommands()->addCommands($commands);

        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @throws DcGeneralInvalidArgumentException When the command passed as $before can not be found.
     */
    #[\Override]
    public function addCommands(array $commands, ?CommandInterface $before = null)
    {
        /** @var CommandInterface[] $commands */
        foreach ($commands as $command) {
            $this->addCommand($command, $before);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function removeCommands(array $commands)
    {
        /** @var CommandInterface[] $commands */
        foreach ($commands as $command) {
            $this->removeCommand($command);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function hasCommand(CommandInterface $command)
    {
        return isset($this->commands[\spl_object_hash($command)]);
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function hasCommandNamed($name)
    {
        foreach ($this->commands as $command) {
            if ($name === $command->getName()) {
                return true;
            }
        }

        return false;
    }

    /**
     * {@inheritdoc}
     *
     * @throws DcGeneralInvalidArgumentException When the command passed as $before can not be found.
     */
    #[\Override]
    public function addCommand(CommandInterface $command, ?CommandInterface $before = null)
    {
        $hash = \spl_object_hash($command);

        if ($before) {
            $beforeHash = \spl_object_hash($before);

            if (isset($this->commands[$beforeHash])) {
                $hashes   = \array_keys($this->commands);
                $position = \array_search($beforeHash, $hashes, true);

                $this->commands = \array_merge(
                    \array_slice($this->commands, 0, (int) $position),
                    [$hash => $command],
                    \array_slice($this->commands, (int) $position)
                );
            } else {
                throw new DcGeneralInvalidArgumentException(
                    \sprintf(
                        'Command %s not contained command collection - can not add %s after it.',
                        $before->getName(),
                        $command->getName()
                    )
                );
            }
        } else {
            $this->commands[$hash] = $command;
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function removeCommand(CommandInterface $command)
    {
        unset($this->commands[\spl_object_hash($command)]);

        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @throws DcGeneralInvalidArgumentException When the requested command could not be found.
     */
    #[\Override]
    public function removeCommandNamed($name)
    {
        foreach ($this->commands as $command) {
            if ($name === $command->getName()) {
                $this->removeCommand($command);

                return $this;
            }
        }

        throw new DcGeneralInvalidArgumentException('Command with name ' . $name . ' not found');
    }

    /**
     * {@inheritdoc}
     *
     * @throws DcGeneralInvalidArgumentException When the requested command could not be found.
     */
    #[\Override]
    public function getCommandNamed($name)
    {
        foreach ($this->commands as $command) {
            if ($name === $command->getName()) {
                return $command;
            }
        }

        throw new DcGeneralInvalidArgumentException('Command with name ' . $name . ' not found');
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function getCommands()
    {
        return $this->commands;
    }
}
