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
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Tristan Lins <tristan.lins@bit3.de>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2013-2019 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Contao;

use Contao\Environment;
use Contao\Input;
use ContaoCommunityAlliance\DcGeneral\InputProviderInterface;

/**
 * Class InputProvider.
 *
 * This class is the Contao binding of an input provider.
 */
class InputProvider implements InputProviderInterface
{
    /**
     * The parameter raw has no function.
     *
     * {@inheritDoc}
     */
    public function getParameter($key, $raw = false)
    {
        return Input::get($key);
    }

    /**
     * {@inheritDoc}
     */
    public function setParameter($key, $value)
    {
        Input::setGet($key, $value);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function unsetParameter($key)
    {
        Input::setGet($key, null);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function hasParameter($key)
    {
        return (null !== Input::get($key));
    }

    /**
     * {@inheritDoc}
     */
    public function getValue($key, $raw = false)
    {
        if ($raw) {
            return Input::postRaw($key);
        }

        return Input::post($key);
    }

    /**
     * {@inheritDoc}
     */
    public function setValue($key, $value)
    {
        Input::setPost($key, $value);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function unsetValue($key)
    {
        Input::setPost($key, null);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function hasValue($key)
    {
        return (null !== Input::post($key));
    }

    /**
     * {@inheritDoc}
     */
    public function getRequestUrl()
    {
        return Environment::get('request');
    }
}
