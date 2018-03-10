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
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2013-2018 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View;

/**
 * Translated toggle command - language aware command for toggling a boolean property between '1' and '' (empty string).
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
