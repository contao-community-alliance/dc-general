<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2023 Contao Community Alliance.
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
 * @author     Richard Henkenjohann <richardhenkenjohann@googlemail.com>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2013-2023 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView;

use Contao\BackendTemplate;
use Contao\System;
use ContaoCommunityAlliance\DcGeneral\Clipboard\Clipboard;
use ContaoCommunityAlliance\DcGeneral\Contao\InputProvider;
use ContaoCommunityAlliance\DcGeneral\DataDefinitionContainerInterface;
use ContaoCommunityAlliance\DcGeneral\DefaultEnvironment;
use ContaoCommunityAlliance\DcGeneral\View\ViewTemplateInterface;
use ContaoCommunityAlliance\Translator\TranslatorInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * This class is used for the contao backend view as template.
 *
 * @psalm-suppress PropertyNotSetInConstructor
 * @psalm-suppress DeprecatedClass
 */
class ContaoBackendViewTemplate extends BackendTemplate implements ViewTemplateInterface, TranslatorInterface
{
    /**
     * The translator.
     *
     * @var TranslatorInterface|null
     */
    protected $translator = null;

    /**
     * Get the translator.
     *
     * @return TranslatorInterface
     */
    public function getTranslator()
    {
        if (null === $this->translator) {
            throw new \RuntimeException('Translator not set.');
        }
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
        return $this->getTranslator()->translate($string, $domain, $parameters, $locale);
    }

    /**
     * {@inheritdoc}
     */
    public function translatePluralized($string, $number, $domain = null, array $parameters = [], $locale = null)
    {
        return $this->getTranslator()->translatePluralized($string, $number, $domain, $parameters, $locale);
    }

    /**
     * Get the back button.
     *
     * @return string
     *
     * @deprecated Do not use
     */
    public function getBackButton(): string
    {
        $dataContainer = System::getContainer()->get('cca.dc-general.data-definition-container');
        assert($dataContainer instanceof DataDefinitionContainerInterface);

        /** @psalm-suppress UndefinedThisPropertyFetch */
        $table = $this->table;
        assert(\is_string($table));
        $dataDefinition = $dataContainer->getDefinition($table);

        $translator = System::getContainer()->get('cca.translator.contao_translator');
        assert($translator instanceof TranslatorInterface);

        $dispatcher = System::getContainer()->get('event_dispatcher');
        assert($dispatcher instanceof EventDispatcherInterface);

        $environment = new DefaultEnvironment();
        $environment->setDataDefinition($dataDefinition);
        $environment->setTranslator($translator);
        $environment->setEventDispatcher($dispatcher);
        $environment->setInputProvider(new InputProvider());
        $environment->setClipboard(new Clipboard());

        return (new GlobalButtonRenderer($environment))->render();
    }

    // @codingStandardsIgnoreStart
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
    public function setData($arrData)
    {
        parent::setData($arrData);

        return $this;
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
        /** @psalm-suppress DeprecatedMethod */
        parent::output();
    }
    // @codingStandardsIgnoreEnd
}
