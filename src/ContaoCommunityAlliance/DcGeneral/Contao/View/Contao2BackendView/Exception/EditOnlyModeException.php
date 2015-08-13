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
 * Exception is thrown if an data definition is in edit only mode.
 */
class EditOnlyModeException extends DefinitionException
{
    /**
     * The message template.
     *
     * @var string
     */
    protected $message = 'Not able to perform action as definition only supports edit actions "%s".';
}
