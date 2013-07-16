<?php
/**
 * PHP version 5
 * @package    generalDriver
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

namespace DcGeneral;

use DcGeneral\Interfaces\InputProvider;

class ContaoInputProvider implements InputProvider
{
	public function getParameter($strKey, $blnRaw = false)
	{
		// TODO: raw handling not implemented yet.
		\Input::getInstance()->get($strKey);
	}

	public function setParameter($strKey, $varValue)
	{
		\Input::getInstance()->setGet($strKey, $varValue);
	}

	public function unsetParameter($strKey)
	{
		\Input::getInstance()->setGet($strKey, null);
	}

	public function hasParameter($strKey)
	{
		return (\Input::getInstance()->get($strKey) !== null);
	}

	public function getValue($strKey, $blnRaw = false)
	{
		if ($blnRaw)
		{
			return \Input::getInstance()->postRaw($strKey);
		} else {
			// TODO: unsure if we should use postHtml here.
			return \Input::getInstance()->post($strKey);
		}
	}

	public function setValue($strKey, $varValue)
	{
		\Input::getInstance()->setPost($strKey, $varValue);
	}

	public function unsetValue($strKey)
	{
		\Input::getInstance()->setPost($strKey, null);
	}

	public function hasValue($strKey)
	{
		return (\Input::getInstance()->post($strKey) !== null);
	}

	public function getPersistentValue($strKey)
	{
		return \Session::getInstance()->get($strKey);
	}

	public function setPersistentValue($strKey, $varValue)
	{
		\Session::getInstance()->set($strKey, $varValue);
	}

	public function hasPersistentValue($strKey)
	{
		return (\Session::getInstance()->get($strKey) !== null);
	}
}
