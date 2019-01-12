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
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2013-2019 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Data;

/**
 * This class is the base implementation for LanguageInformationCollectionInterface.
 */
class DefaultLanguageInformationCollection implements LanguageInformationCollectionInterface
{
    /**
     * The language information stored in this collection.
     *
     * @var LanguageInformationInterface[]
     */
    protected $languages = [];

    /**
     * {@inheritDoc}
     */
    public function add(LanguageInformationInterface $language)
    {
        $this->languages[] = $language;

        return $this;
    }

    /**
     * Get a iterator for this collection.
     *
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->languages);
    }

    /**
     * Count the contained language information.
     *
     * @return int
     */
    public function count()
    {
        return \count($this->languages);
    }
}
