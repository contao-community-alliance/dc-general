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

/**
 * This event occurs when an action should handled.
 */
class ActionEvent extends AbstractActionAwareEvent
{
    /**
     * The action response, if any is set.
     *
     * @var string
     */
    protected $response;

    /**
     * Set the action response.
     *
     * @param string $response The response.
     *
     * @return ActionEvent
     */
    public function setResponse($response)
    {
        $this->response = ($response !== null) ? (string) $response : null;
        return $this;
    }

    /**
     * Return the action response.
     *
     * @return string
     */
    public function getResponse()
    {
        return $this->response;
    }
}
