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

namespace DcGeneral;

/**
 * This translator is a chain of translators.
 *
 * When a translation is requested, the chain tries all stored translators and returns the first value not equal to the
 * input.
 *
 * @package DcGeneral
 */
class TranslatorChain
	implements TranslatorInterface
{
	/**
	 * The list of stored translators.
	 *
	 * @var TranslatorInterface[]
	 */
	protected $translators = array();

	/**
	 * Keep going over translators, even if a translation was found.
	 *
	 * @var bool
	 */
	protected $keepGoing = false;

	/**
	 * Clear the chain.
	 *
	 * @return TranslatorChain
	 */
	public function clear()
	{
		$this->translators = array();

		return $this;
	}

	/**
	 * Add all passed translators to the chain.
	 *
	 * @param array $translators The translators to add.
	 *
	 * @return TranslatorChain
	 */
	public function addAll(array $translators)
	{
		foreach ($translators as $translator)
		{
			$this->add($translator);
		}

		return $this;
	}

	/**
	 * Add a translator to the chain.
	 *
	 * @param TranslatorInterface $translator The translator to add.
	 *
	 * @return TranslatorChain
	 */
	public function add(TranslatorInterface $translator)
	{
		$hash = spl_object_hash($translator);

		$this->translators[$hash] = $translator;

		return $this;
	}

	/**
	 * Remove a translator from the chain.
	 *
	 * @param TranslatorInterface $translator The translator.
	 *
	 * @return TranslatorChain
	 */
	public function remove(TranslatorInterface $translator)
	{
		$hash = spl_object_hash($translator);

		unset($this->translators[$hash]);

		return $this;
	}

	/**
	 * Get an array of all translators.
	 *
	 * @return array
	 */
	public function getAll()
	{
		return array_values($this->translators);
	}

	/**
	 * Set keep going status.
	 *
	 * @param bool $keepGoing Set the keep going status.
	 *
	 * @return TranslatorChain
	 */
	public function setKeepGoing($keepGoing)
	{
		$this->keepGoing = $keepGoing;

		return $this;
	}

	/**
	 * Determinate if keep going is enabled.
	 *
	 * @return boolean
	 */
	public function isKeepGoing()
	{
		return $this->keepGoing;
	}

	/**
	 * {@inheritdoc}
	 */
	public function translate($string, $domain = null, array $parameters = array(), $locale = null)
	{
		$original = $string;

		for ($translator = reset($this->translators);
			$translator && ($this->keepGoing || $string == $original);
			$translator = next($this->translators))
		{
			$string = $translator->translate($string, $domain, $parameters, $locale);
		}

		return $string;
	}

	/**
	 * {@inheritdoc}
	 */
	public function translatePluralized($string, $number, $domain = null, array $parameters = array(), $locale = null)
	{
		$original = $string;

		for ($translator = reset($this->translators);
			$this->keepGoing || $string == $original;
			$translator = next($this->translators))
		{
			$string = $translator->translatePluralized($string, $number, $domain, $parameters, $locale);
		}

		return $string;
	}
}
