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

namespace ContaoCommunityAlliance\DcGeneral;

/**
 * This class is the base foundation for a command event.
 */
class Action
{
    /**
     * The action name.
     *
     * @var string
     */
    protected $name;

    /**
     * The action arguments.
     *
     * @var array
     */
    protected $arguments;

    /**
     * Create a new instance.
     *
     * @param string $name      The action name.
     * @param array  $arguments A set of action arguments.
     */
    public function __construct($name, array $arguments = array())
    {
        $this->name      = $name;
        $this->arguments = $arguments;
    }

    /**
     * Return the name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Return the arguments.
     *
     * @return array
     */
    public function getArguments()
    {
        return $this->arguments;
    }
}
