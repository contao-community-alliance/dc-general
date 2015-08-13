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

namespace ContaoCommunityAlliance\DcGeneral\Data;

/**
 * This represents an iterable collection of Model elements.
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
