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
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @copyright  2013-2017 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0
 * @filesource
 */
namespace ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\ActionHandler;

use ContaoCommunityAlliance\DcGeneral\Contao\RequestScopeDeterminator;

/**
 * Base action handler class providing the scope determinator.
 *
 * @package ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\ActionHandler
 */
abstract class AbstractRequestScopeDeterminatorHandler
{
    /**
     * The request mode determinator.
     *
     * @var RequestScopeDeterminator
     */
    protected $scopeDeterminator;

    /**
     * CopyHandler constructor.
     *
     * @param RequestScopeDeterminator $scopeDeterminator The request mode determinator.
     */
    public function __construct(RequestScopeDeterminator $scopeDeterminator)
    {
        $this->scopeDeterminator = $scopeDeterminator;
    }
}
