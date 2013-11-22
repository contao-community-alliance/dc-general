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

use DcGeneral\Exception\DcGeneralInvalidArgumentException;

class StaticTranslator extends AbstractTranslator
{
	/**
	 * @var mixed[]
	 */
	protected $values;

	/**
	 * @param string $string The string to translate.
	 *
	 * @param string $domain The domain to use.
	 *
	 * @param string $locale The locale (otherwise the current default locale will get used).
	 *
	 * @return mixed
	 */
	protected function getValue($string, $domain, $locale)
	{
		if (!$domain) {
			$domain = 'default';
		}

		if (!$locale) {
			$locale = 'default';
		}

		if (!(is_array($this->values[$locale]) && is_array($this->values[$locale][$domain])))
		{
			return $string;
		}

		$chunks = explode('.', $string);
		$lang   = $this->values[$locale][$domain];

		while (($chunk = array_shift($chunks)) !== null)
		{
			if (!array_key_exists($chunk, $lang))
			{
				return $string;
			}

			$lang = $lang[$chunk];
		}

		return $lang;
	}

	/**
	 * @param string $string The string to translate.
	 *
	 * @param mixed  $value The value to store.
	 *
	 * @param string $domain The domain to use.
	 *
	 * @param string $locale The locale (otherwise the current default locale will get used).
	 *
	 * @return StaticTranslator
	 */
	public function setValue($string, $value, $domain = null, $locale = null)
	{
		if (!$domain) {
			$domain = 'default';
		}

		if (!$locale) {
			$locale = 'default';
		}

		if (!is_array($this->values[$locale]))
		{
			$this->values[$locale] = array();
		}

		if (!is_array($this->values[$locale][$domain]))
		{
			$this->values[$locale][$domain] = array();
		}

		$chunks = explode('.', $string);
		$lang   = $this->values[$locale][$domain];

		while (($chunk = array_shift($chunks)) !== null)
		{
			if (!array_key_exists($chunk, $lang))
			{
				$lang[$chunk] = array();
			}

			$lang = &$lang[$chunk];
		}

		$lang = $value;

		return $this;
	}

	protected function determineKey($min, $max)
	{
		// Exact number.
		if (($min !== null) && ($max !== null) && ($min === $max))
		{
			return $min;
		}
		// Full defined range.
		elseif (($min !== null) && ($max !== null) && ($min === $max))
		{
			return $min . ':' . $max;
		}
		// Open end range.
		elseif (($min !== null) && ($max === null))
		{
			return $min . ':';
		}
		// Open start range.
		elseif (($min === null) && ($max !== null))
		{
			return ':' . $max;
		}

		throw new DcGeneralInvalidArgumentException('You must either specify min or max value.');
	}

	protected function sortPluralized($lang)
	{
		uksort($lang, function($a, $b) {

			if ($a == $b)
			{
				return 0;
			}

			$range_a = explode(':', $a);
			$range_b = explode(':', $b);

			// Both range starts provided.
			if (isset($range_a[0]) && isset($range_b[0]))
			{
				return strcmp($range_a[0], $range_b[0]);
			}

			// Only second range has a starting point.
			if (!isset($range_a[0]) && isset($range_b[0]))
			{
				return -1;
			}

			// Only first range has a starting point.
			if (isset($range_a[0]) && !isset($range_b[0]))
			{
				return 1;
			}

			// Both are an open start range.
			if (isset($range_a[1]) && isset($range_b[1]))
			{
				return strcmp($range_a[1], $range_b[1]);
			}

			// Only second range is open => First is first.
			if (!isset($range_a[1]) && isset($range_b[1]))
			{
				return 1;
			}

			// Only first range is open => Second is first.
			if (isset($range_a[1]) && !isset($range_b[1]))
			{
				return -1;
			}

			// Just here to make the IDEs happy - is already handled above as early exit point.
			return 0;
		});

		return $lang;
	}

	public function setValuePluralized($string, $min = null, $max = null, $domain = null, $locale = null)
	{
		if (!$domain) {
			$domain = 'default';
		}

		if (!$locale) {
			$locale = 'default';
		}

		if (!is_array($this->values[$locale]))
		{
			$this->values[$locale] = array();
		}

		if (!is_array($this->values[$locale]))
		{
			$this->values[$locale][$domain] = array();
		}

		$chunks = explode('.', $string);
		$lang   = $this->values[$locale][$domain];

		while (($chunk = array_shift($chunks)) !== null)
		{
			if (!array_key_exists($chunk, $lang))
			{
				$lang[$chunk] = array();
			}

			$lang = $lang[$chunk];
		}

		// NOTE: we kill any value previously stored as there is no way to tell which value to use.
		if (!is_array($lang))
		{
			$lang = array();
		}

		$lang[$this->determineKey($min, $max)] = $string;

		$lang = $this->sortPluralized($lang);

		$this->setValue($string, $lang, $domain, $locale);

		return $this;
	}
}
