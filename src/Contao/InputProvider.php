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
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2013-2017 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Contao;

use ContaoCommunityAlliance\DcGeneral\InputProviderInterface;

/**
 * Class InputProvider.
 *
 * This class is the Contao binding of an input provider.
 */
class InputProvider implements InputProviderInterface
{
    /**
     * {@inheritDoc}
     */
    public function getParameter($strKey, $blnRaw = false)
    {
        return \Input::getInstance()->get($strKey);
    }

    /**
     * {@inheritDoc}
     */
    public function setParameter($strKey, $varValue)
    {
        \Input::getInstance()->setGet($strKey, $varValue);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function unsetParameter($strKey)
    {
        \Input::getInstance()->setGet($strKey, null);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function hasParameter($strKey)
    {
        return (\Input::getInstance()->get($strKey) !== null);
    }

    /**
     * {@inheritDoc}
     */
    public function getValue($strKey, $blnRaw = false)
    {
        if ($blnRaw) {
            return \Input::getInstance()->postRaw($strKey);
        }

        return \Input::getInstance()->post($strKey);
    }

    /**
     * {@inheritDoc}
     */
    public function setValue($strKey, $varValue)
    {
        \Input::getInstance()->setPost($strKey, $varValue);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function unsetValue($strKey)
    {
        \Input::getInstance()->setPost($strKey, null);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function hasValue($strKey)
    {
        return (\Input::getInstance()->post($strKey) !== null);
    }

    /**
     * {@inheritDoc}
     */
    public function getRequestUrl()
    {

        return \Environment::getInstance()->request;
    }
}
