<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2017 Contao Community Alliance.
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
 * @copyright  2013-2017 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Event;

use ContaoCommunityAlliance\DcGeneral\Data\ModelInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\CommandInterface;
use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;

/**
 * Abstract base class for a command event referencing a model.
 */
abstract class AbstractModelCommandEvent extends AbstractCommandEvent implements ModelCommandEventInterface
{
    /**
     * The attached model.
     *
     * @var ModelInterface
     */
    protected $model;

    /**
     * Create a new instance.
     *
     * @param CommandInterface     $command     The command.
     *
     * @param ModelInterface       $model       The model.
     *
     * @param EnvironmentInterface $environment The environment.
     */
    public function __construct(CommandInterface $command, ModelInterface $model, EnvironmentInterface $environment)
    {
        parent::__construct($command, $environment);
        $this->model = $model;
    }

    /**
     * {@inheritDoc}
     */
    public function getModel()
    {
        return $this->model;
    }
}
