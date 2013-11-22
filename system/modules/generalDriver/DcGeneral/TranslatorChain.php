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

class TranslatorChain implements TranslatorInterface
{
	/**
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
	 * @return TranslatorChain
	 */
	public function clear()
	{
		$this->translators = array();

		return $this;
	}

	/**
	 * @param array $translators
	 *
	 * @return TranslatorChain
	 */
	public function addAll(array $translators)
	{
		foreach ($translators as $translator) {
			$this->add($translator);
		}

		return $this;
	}

	/**
	 * @param TranslatorInterface $translator
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
	 * @param TranslatorInterface $translator
	 *
	 * @return TranslatorChain
	 */
	public function remove(TranslatorInterface $translator)
	{
		$hash = spl_object_hash($translator);
		unset($this->translators[$hash]);

		return $this;
	}

	public function getAll()
	{
		return array_values($this->translators);
	}

	/**
	 * Set keep going status.
	 *
	 * @param boolean $keepGoing
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

		for (
			$translator = reset($this->translators);
			$translator && ($this->keepGoing || $string == $original);
			$translator = next($this->translators)
		) {
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

		for (
			$translator = reset($this->translators);
			$this->keepGoing || $string == $original;
			$translator = next($this->translators)
		) {
			$string = $translator->translatePluralized($string, $number, $domain, $parameters, $locale);
		}

		return $string;
	}
}
