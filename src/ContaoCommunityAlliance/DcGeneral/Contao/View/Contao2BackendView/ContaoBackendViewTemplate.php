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
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Tristan Lins <tristan.lins@bit3.de>
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2013-2018 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView;

use Contao\BackendTemplate;
use ContaoCommunityAlliance\DcGeneral\Clipboard\Clipboard;
use ContaoCommunityAlliance\DcGeneral\Contao\InputProvider;
use ContaoCommunityAlliance\DcGeneral\DefaultEnvironment;
use ContaoCommunityAlliance\DcGeneral\View\ViewTemplateInterface;
use ContaoCommunityAlliance\Translator\TranslatorInterface;

/**
 * This class is used for the contao backend view as template.
 */
class ContaoBackendViewTemplate extends BackendTemplate implements ViewTemplateInterface, TranslatorInterface
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
    public function getData()
    {
        return parent::getData();
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
    public function translate($string, $domain = null, array $parameters = [], $locale = null)
    {
        if ($this->translator) {
            return $this->translator->translate($string, $domain, $parameters, $locale);
        }

        return $string;
    }

    /**
     * {@inheritdoc}
     */
    public function translatePluralized($string, $number, $domain = null, array $parameters = [], $locale = null)
    {
        if ($this->translator) {
            return $this->translator->translatePluralized($string, $number, $domain, $parameters, $locale);
        }

        return $string;
    }

    /**
     * Get the back button.
     *
     * @return string
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    public function getBackButton()
    {
        $container = $GLOBALS['container'];

        $dataContainer  = $container['dc-general.data-definition-container'];
        $dataDefinition = $dataContainer->getDefinition($this->table);

        $environment = new DefaultEnvironment();
        $environment->setDataDefinition($dataDefinition);
        $environment->setTranslator($container['translator']);
        $environment->setEventDispatcher($container['event-dispatcher']);
        $environment->setInputProvider(new InputProvider());
        $environment->setClipboard(new Clipboard());

        $renderer = new GlobalButtonRenderer($environment);
        return $renderer->render();
    }

    /**
     * {@inheritDoc}
     */
    public function parse()
    {
        return parent::parse();
    }

    /**
     * {@inheritDoc}
     */
    public function output()
    {
        parent::output();
    }
}
