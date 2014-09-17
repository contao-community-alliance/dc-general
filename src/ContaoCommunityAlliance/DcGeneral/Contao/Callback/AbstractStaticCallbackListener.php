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

use Symfony\Component\EventDispatcher\Event;

/**
 * Class AbstractStaticCallbackListener.
 *
 * Abstract base class for callbacks with static arguments that are independent from the event.
 * The parameters are passed as optional list to the constructor.
 *
 * @package DcGeneral\Contao\Callback
 */
abstract class AbstractStaticCallbackListener extends AbstractCallbackListener
{

    /**
     * Arguments for the callback.
     *
     * @var array
     */
    protected $args;

    /**
     * {@inheritdoc}
     *
     * @param mixed $_ [optional] A variable list of arguments to be passed to the callback.
     */
    public function __construct($callback, $_ = null)
    {
        parent::__construct($callback);

        $args = func_get_args();
        array_shift($args);

        $this->args = $args;
    }

    /**
     * {@inheritdoc}
     */
    public function getArgs($event)
    {
        return $this->args;
    }
}
