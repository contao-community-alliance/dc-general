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

use ContaoCommunityAlliance\DcGeneral\Exception\DcGeneralRuntimeException;

/**
 * Class DefinitionException.
 *
 * Exception is thrown if any
 */
class DefinitionException extends DcGeneralRuntimeException
{
    /**
     * The message template.
     *
     * @var string
     */
    protected $message = 'Not able to perform action as is not supported by the definition of "%s".';

    /**
     * The definition name of the affected definition.
     *
     * @var string
     */
    protected $name;

    /**
     * Create instance.
     *
     * @param string          $definitionName The definition name of the affected definition.
     * @param int             $code           The error code.
     * @param \Exception|null $previous       The previous exception.
     */
    public function __construct($definitionName, $code = 0, \Exception $previous = null)
    {
        $this->name = $definitionName;

        parent::__construct(sprintf($this->message, $definitionName), $code, $previous);
    }
}
