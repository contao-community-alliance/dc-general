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
 * @copyright  2013-2015 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/LICENSE LGPL-3.0
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View;

/**
 * Translated toggle command - language aware command for toggling a boolean property between '1' and '' (empty string).
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
