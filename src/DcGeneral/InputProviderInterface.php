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
 * This interface describes an input provider.
 *
 * An input provider provides access to parameters, values and persistent values.
 *
 * @package DcGeneral
 */
interface InputProviderInterface
{
	/**
	 * Retrieve a request parameter.
	 *
	 * In plain HTTP, this will be a $_GET parameter, for other implementations consult the API.
	 *
	 * @param string $strKey The name of the parameter to be retrieved.
	 *
	 * @param bool   $blnRaw Boolean flag to determine if the content shall be returned RAW or rather be stripped of
	 *                       potential malicious content.
	 *
	 * @return mixed
	 */
	public function getParameter($strKey, $blnRaw = false);

	/**
	 * Save/change a request parameter.
	 *
	 * In plain HTTP, this will be a $_GET parameter, for other implementations consult the API.
	 *
	 * @param string $strKey   The name of the parameter to be stored.
	 *
	 * @param mixed  $varValue The value to be stored.
	 *
	 * @return InputProviderInterface
	 */
	public function setParameter($strKey, $varValue);

	/**
	 * Unset a request parameter.
	 *
	 * In plain HTTP, this will be a $_GET parameter, for other implementations consult the API.
	 *
	 * @param string $strKey The name of the parameter to be removed.
	 *
	 * @return InputProviderInterface
	 */
	public function unsetParameter($strKey);

	/**
	 * Determines if a request parameter is defined.
	 *
	 * In plain HTTP, this will be a $_GET parameter, for other implementations consult the API.
	 *
	 * @param string $strKey The name of the parameter to be checked.
	 *
	 * @return bool
	 */
	public function hasParameter($strKey);

	/**
	 * Retrieve a request value.
	 *
	 * In plain HTTP, this will be a $_POST value, for other implementations consult the API.
	 *
	 * @param string $strKey The name of the value to be retrieved.
	 *
	 * @param bool   $blnRaw Boolean flag to determine if the content shall be returned RAW or rather be stripped of
	 *                       potential malicious content.
	 *
	 * @return mixed
	 */
	public function getValue($strKey, $blnRaw = false);

	/**
	 * Save/change a request value.
	 *
	 * In plain HTTP, this will be a $_POST value, for other implementations consult the API.
	 *
	 * @param string $strKey   The name of the value to be stored.
	 *
	 * @param mixed  $varValue The value to be stored.
	 *
	 * @return InputProviderInterface
	 */
	public function setValue($strKey, $varValue);

	/**
	 * Unset a request value.
	 *
	 * In plain HTTP, this will be a $_POST value, for other implementations consult the API.
	 *
	 * @param string $strKey The name of the value to be removed.
	 *
	 * @return InputProviderInterface
	 */
	public function unsetValue($strKey);

	/**
	 * Determines if a request value is defined.
	 *
	 * In plain HTTP, this will be a $_POST value, for other implementations consult the API.
	 *
	 * @param string $strKey The name of the value to be checked.
	 *
	 * @return bool
	 */
	public function hasValue($strKey);

	/**
	 * Retrieve a persistent value.
	 *
	 * Usually this value is being kept in the user session.
	 *
	 * @param string $strKey The name of the value to be retrieved.
	 *
	 * @return mixed
	 */
	public function getPersistentValue($strKey);

	/**
	 * Save/change a persistent value.
	 *
	 * Usually this value is being kept in the user session.
	 *
	 * @param string $strKey   The name of the value to be stored.
	 *
	 * @param mixed  $varValue The value to be stored.
	 *
	 * @return InputProviderInterface
	 */
	public function setPersistentValue($strKey, $varValue);

	/**
	 * Determines if a persistent value is defined.
	 *
	 * Usually this value is being kept in the user session.
	 *
	 * @param string $strKey The name of the value to be checked.
	 *
	 * @return bool
	 */
	public function hasPersistentValue($strKey);

	/**
	 * Retrieve the current request url.
	 *
	 * @return string
	 */
	public function getRequestUrl();
}
