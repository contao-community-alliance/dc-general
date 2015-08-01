<?php
/**
 * PHP version 5
 *
 * @package    generalDriver
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Exception;

/**
 * Class NotDeletableException.
 *
 * This exception is thrown if a data definition does not support delete actions.
 */
class NotDeletableException extends DefinitionException
{
    /**
     * The message template.
     *
     * @var string
     */
    protected $message = 'Not able to perform delete action for data definition "%s".';
}
