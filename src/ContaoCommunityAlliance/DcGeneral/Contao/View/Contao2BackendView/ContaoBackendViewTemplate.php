<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2015 Contao Community Alliance.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    contao-community-alliance/dc-general
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Tristan Lins <tristan.lins@bit3.de>
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @copyright  2013-2015 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/LICENSE LGPL-3.0
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView;

use ContaoCommunityAlliance\DcGeneral\View\ViewTemplateInterface;
use ContaoCommunityAlliance\Translator\TranslatorInterface;

/**
 * This class is used for the contao backend view as template.
 */
class ContaoBackendViewTemplate extends \BackendTemplate implements ViewTemplateInterface, TranslatorInterface
{
    /**
     * The translator.
     *
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * Get the translator.
     *
     * @return TranslatorInterface
     */
    public function getTranslator()
    {
        return $this->translator;
    }

    /**
     * Set the translator.
     *
     * @param TranslatorInterface $translator The translator.
     *
     * @return $this
     */
    public function setTranslator(TranslatorInterface $translator)
    {
        $this->translator = $translator;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function setData($data)
    {
        parent::setData($data);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function set($name, $value)
    {
        $this->$name = $value;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function get($name)
    {
        return $this->$name;
    }

    /**
     * {@inheritdoc}
     */
    public function translate($string, $domain = null, array $parameters = array(), $locale = null)
    {
        if ($this->translator) {
            return $this->translator->translate($string, $domain, $parameters, $locale);
        }

        return $string;
    }

    /**
     * {@inheritdoc}
     */
    public function translatePluralized($string, $number, $domain = null, array $parameters = array(), $locale = null)
    {
        if ($this->translator) {
            return $this->translator->translatePluralized($string, $number, $domain, $parameters, $locale);
        }

        return $string;
    }
}
