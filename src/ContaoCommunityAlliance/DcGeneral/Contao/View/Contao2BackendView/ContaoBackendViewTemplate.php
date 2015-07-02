<?php
/**
 * PHP version 5
 *
 * @package    generalDriver
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Tristan Lins <tristan.lins@bit3.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView;

use ContaoCommunityAlliance\DcGeneral\View\ViewTemplateInterface;
use ContaoCommunityAlliance\Translator\TranslatorInterface;

/**
 * This class is used for the contao backend view as template.
 *
 * @package DcGeneral\View
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
     * Construct.
     *
     * @param string              $strTemplate    The template name.
     * @param string              $strContentType the content type.
     */
    public function __construct($strTemplate = '', $strContentType = 'text/html')
    {
        parent::__construct($strTemplate, $strContentType);
    }

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
            return $this->translator->translate($string, $number, $domain, $parameters, $locale);
        }

        return $string;
    }
}
