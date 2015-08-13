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

namespace ContaoCommunityAlliance\DcGeneral;

/**
 * This class is the base foundation for a command event.
 *
 * @package DcGeneral\Event
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
