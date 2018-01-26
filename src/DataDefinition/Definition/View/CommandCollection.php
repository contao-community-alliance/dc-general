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
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Tristan Lins <tristan.lins@bit3.de>
 * @author     David Molineus <mail@netzmacht.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2013-2018 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View;

use ContaoCommunityAlliance\DcGeneral\Exception\DcGeneralInvalidArgumentException;

/**
 * Class CommandCollection.
 *
 * Implementation of a command collection.
 */
class CommandCollection implements CommandCollectionInterface
{
    /**
     * The commands contained within the collection.
     *
     * @var CommandInterface[]
     */
    protected $commands = array();

    /**
     * {@inheritdoc}
     */
    public function clearCommands()
    {
        $this->commands = array();

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setCommands(array $commands)
    {
        $this->clearCommands();
        $this->addCommands($commands);

        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @throws DcGeneralInvalidArgumentException When the command passed as $before can not be found.
     */
    public function addCommands(array $commands, CommandInterface $before = null)
    {
        foreach ($commands as $command) {
            $this->addCommand($command, $before);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function removeCommands(array $commands)
    {
        foreach ($commands as $command) {
            $this->removeCommand($command);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function hasCommand(CommandInterface $command)
    {
        $hash = spl_object_hash($command);

        return isset($this->commands[$hash]);
    }

    /**
     * {@inheritdoc}
     */
    public function hasCommandNamed($name)
    {
        foreach ($this->commands as $command) {
            if ($command->getName() == $name) {
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
    public function addCommand(CommandInterface $command, CommandInterface $before = null)
    {
        $hash = spl_object_hash($command);

        if ($before) {
            $beforeHash = spl_object_hash($before);

            if (isset($this->commands[$beforeHash])) {
                $hashes   = array_keys($this->commands);
                $position = array_search($beforeHash, $hashes);

                $this->commands = array_merge(
                    array_slice($this->commands, 0, $position),
                    array($hash => $command),
                    array_slice($this->commands, $position)
                );
            } else {
                throw new DcGeneralInvalidArgumentException(
                    sprintf(
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
    public function removeCommand(CommandInterface $command)
    {
        $hash = spl_object_hash($command);
        unset($this->commands[$hash]);

        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @throws DcGeneralInvalidArgumentException When the requested command could not be found.
     */
    public function removeCommandNamed($name)
    {
        foreach ($this->commands as $command) {
            if ($command->getName() == $name) {
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
    public function getCommandNamed($name)
    {
        foreach ($this->commands as $command) {
            if ($command->getName() == $name) {
                return $command;
            }
        }

        throw new DcGeneralInvalidArgumentException('Command with name ' . $name . ' not found');
    }

    /**
     * {@inheritdoc}
     */
    public function getCommands()
    {
        return $this->commands;
    }
}
