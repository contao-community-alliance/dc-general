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

/**
 * Static in memory translator implementation.
 *
 * This translator holds all values in memory.
 *
 * It is to be populated via the public setValue method.
 *
 * @package DcGeneral
 */
class StaticTranslator extends AbstractTranslator
{
	/**
	 * The translation values.
	 *
	 * @var mixed[]
	 */
	protected $values;

	/**
	 * Retrieve the value.
	 *
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
		if (!$domain)
		{
			$domain = 'default';
		}

		if (!$locale)
		{
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
	 * Set a translation value in the translator.
	 *
	 * @param string $string The string to translate.
	 *
	 * @param mixed  $value  The value to store.
	 *
	 * @param string $domain The domain to use.
	 *
	 * @param string $locale The locale (otherwise the current default locale will get used).
	 *
	 * @return StaticTranslator
	 */
	public function setValue($string, $value, $domain = null, $locale = null)
	{
		if (!$domain)
		{
			$domain = 'default';
		}

		if (!$locale)
		{
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
		$lang   = &$this->values[$locale][$domain];
		$key    = array_pop($chunks);

		while (($chunk = array_shift($chunks)) !== null)
		{
			if (!array_key_exists($chunk, $lang))
			{
				$lang[$chunk] = array();
			}

			$lang = &$lang[$chunk];
		}

		$lang[$key] = $value;

		return $this;
	}

	/**
	 * Determine the correct pluralization key.
	 *
	 * @param int|null $min The minimum value.
	 *
	 * @param int|null $max The maximum value.
	 *
	 * @return string
	 *
	 * @throws DcGeneralInvalidArgumentException When both, min and max, are null.
	 */
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

	/**
	 * Sort the given array for pluralization.
	 *
	 * @param array $lang The language array to be sorted.
	 *
	 * @return array
	 */
	protected function sortPluralized($lang)
	{
		uksort($lang, function($a, $b)
		{

			if ($a == $b)
			{
				return 0;
			}

			$rangeA = explode(':', $a);
			$rangeB = explode(':', $b);

			// Both range starts provided.
			if (isset($rangeA[0]) && isset($rangeB[0]))
			{
				return strcmp($rangeA[0], $rangeB[0]);
			}

			// Only second range has a starting point.
			if (!isset($rangeA[0]) && isset($rangeB[0]))
			{
				return -1;
			}

			// Only first range has a starting point.
			if (isset($rangeA[0]) && !isset($rangeB[0]))
			{
				return 1;
			}

			// Both are an open start range.
			if (isset($rangeA[1]) && isset($rangeB[1]))
			{
				return strcmp($rangeA[1], $rangeB[1]);
			}

			// Only second range is open => First is first.
			if (!isset($rangeA[1]) && isset($rangeB[1]))
			{
				return 1;
			}

			// Only first range is open => Second is first.
			if (isset($rangeA[1]) && !isset($rangeB[1]))
			{
				return -1;
			}

			// Just here to make the IDEs happy - is already handled above as early exit point.
			return 0;
		});

		return $lang;
	}

	/**
	 * Set a pluralized value in the translator.
	 *
	 * @param string   $string The translation string.
	 *
	 * @param int|null $min    The minimum value of the range (optional - defaults to null).
	 *
	 * @param int|null $max    The maximum value of the range (optional - defaults to null).
	 *
	 * @param string   $domain The domain (optional - defaults to null).
	 *
	 * @param string   $locale The locale  (optional - defaults to null).
	 *
	 * @return StaticTranslator
	 */
	public function setValuePluralized($string, $min = null, $max = null, $domain = null, $locale = null)
	{
		if (!$domain)
		{
			$domain = 'default';
		}

		if (!$locale)
		{
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
