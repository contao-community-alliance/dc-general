<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2019 Contao Community Alliance.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    contao-community-alliance/dc-general
 * @author     Richard Henkenjohann <richardhenkenjohann@googlemail.com>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2013-2026 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Exception;

/**
 * Class NotEditableException.
 *
 * This exception is thrown if a data definition does not support edit actions.
 *
 * @api
 */
class NotEditableException extends AbstractDefinitionAccessDeniedException
{
    #[\Override]
    protected function translationKey(): string
    {
        return 'exception.not_editable';
    }

    #[\Override]
    protected function fallbackMessage(): string
    {
        return 'This record cannot be edited.';
    }
}
