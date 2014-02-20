<?php
/**
 * PHP version 5
 * @package    generalDriver
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Tristan Lins <tristan.lins@bit3.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

namespace DcGeneral\Data;

use DcGeneral\Exception\DcGeneralRuntimeException;

/**
 * This represents an iterable collection of Model elements.
 */
interface LanguageInformationCollectionInterface
	extends \IteratorAggregate, \Countable
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
