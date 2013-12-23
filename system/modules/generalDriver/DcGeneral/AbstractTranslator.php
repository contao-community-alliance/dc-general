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
 * Abstract base implementation of a translator.
 *
 * @package DcGeneral
 */
abstract class AbstractTranslator implements TranslatorInterface
{
	/**
	 * Retrieve a value from the translator backend.
	 *
	 * @param string      $string The string to translate.
	 *
	 * @param string|null $domain The domain in which to search for the string.
	 *
	 * @param string|null $locale The locale in which to search.
	 *
	 * @return mixed
	 */
	abstract protected function getValue($string, $domain, $locale);

	/**
	 * {@inheritdoc}
	 */
	public function translate($string, $domain = null, array $parameters = array(), $locale = null)
	{
		$newString = $this->getValue($string, $domain, $locale);

		if ($newString == $string)
		{
			return $string;
		}

		if (count($parameters))
		{
			$newString = vsprintf($newString, $parameters);
		}

		return $newString;
	}

	/**
	 * {@inheritdoc}
	 */
	public function translatePluralized($string, $number, $domain = null, array $parameters = array(), $locale = null)
	{
		$choices = $this->getValue($string, $domain, $locale);

		if (is_array($choices))
		{
			if (isset($choices[$number]))
			{
				$newString = $choices[$number];
			}
			else {
				$array = array();

				foreach ($choices as $range => $choice)
				{
					$range = explode(':', $range);

					if (count($range) < 2)
					{
						$range[] = '';
					}

					$array[] = (object)array(
						'range' => (object)array(
								'from' => $range[0],
								'to'   => $range[1],
							),
						'string' => $choice
					);
				}

				$count = count($array);
				for ($i = 0; $i < $count; $i++)
				{
					$choice = $array[$i];

					// Set from number, if not set (notation ":X").
					if (!$choice->range->from)
					{
						if ($i > 0)
						{
							$choice->range->from = ($array[($i - 1)]->range->to + 1);
						}
						else {
							$choice->range->from = ( - PHP_INT_MAX);
						}
					}
					// Set to number, if not set (notation "X" or "X:").
					if (!$choice->range->to)
					{
						if ($i < ($count - 1))
						{
							$choice->range->to = ($array[($i + 1)]->range->from - 1);
						}
						else {
							$choice->range->to = PHP_INT_MAX;
						}
					}

					if ($number >= $choice->range->from && $number <= $choice->range->to)
					{
						$newString = $choice->string;
						break;
					}
				}
			}
		}

		if (!isset($newString))
		{
			return $string;
		}

		if (count($parameters))
		{
			$newString = vsprintf($newString, $parameters);
		}

		return $newString;
	}
}



