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
interface TranslatedToggleCommandInterface extends ToggleCommandInterface
{
    /**
     * Set the language to toggle.
     *
     * @param string $language The language key.
     *
     * @return ToggleCommandInterface
     */
    public function setLanguage($language);

    /**
     * Get the language to toggle.
     *
     * @return string
     */
    public function getLanguage();
}
