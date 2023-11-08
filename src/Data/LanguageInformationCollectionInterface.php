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
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2013-2023 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Data;

/**
 * This represents an iterable collection of Model elements.
 *
 * @extends \IteratorAggregate<int, LanguageInformationInterface>
 */
interface LanguageInformationCollectionInterface extends \IteratorAggregate, \Countable
{
    /**
     * Append a language to this collection.
     *
     * @param LanguageInformationInterface $language The language information to add.
     *
     * @return LanguageInformationCollectionInterface
     */
    public function add(LanguageInformationInterface $language);
}
