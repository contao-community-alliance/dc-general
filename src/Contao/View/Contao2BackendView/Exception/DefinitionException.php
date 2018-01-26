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
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2013-2018 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
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
