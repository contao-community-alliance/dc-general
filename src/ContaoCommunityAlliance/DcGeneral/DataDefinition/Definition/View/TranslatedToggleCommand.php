<?php
/**
 * PHP version 5
 *
 * @package    generalDriver
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View;

/**
 * Translated toggle command - language aware command for toggling a boolean property between '1' and '' (empty string).
 *
 * @package DcGeneral\DataDefinition\Definition\View
 */
class TranslatedToggleCommand extends ToggleCommand implements TranslatedToggleCommandInterface
{
    /**
     * The property name to toggle.
     *
     * @var string
     */
    protected $language;

    /**
     * {@inheritDoc}
     */
    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * {@inheritDoc}
     */
    public function setLanguage($language)
    {
        $this->language = $language;

        return $this;
    }
}
