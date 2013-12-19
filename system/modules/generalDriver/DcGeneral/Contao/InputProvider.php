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

namespace DcGeneral\Contao;

use DcGeneral\InputProviderInterface;

/**
 * Class InputProvider.
 *
 * This class is the Contao binding of an input provider.
 *
 * @package DcGeneral\Contao
 */
class InputProvider implements InputProviderInterface
{
	/**
	 * {@inheritDoc}
	 */
	public function getParameter($strKey, $blnRaw = false)
	{
		// TODO: raw handling not implemented yet.
		return \Input::getInstance()->get($strKey);
	}

	/**
	 * {@inheritDoc}
	 */
	public function setParameter($strKey, $varValue)
	{
		\Input::getInstance()->setGet($strKey, $varValue);

		return $this;
	}

	/**
	 * {@inheritDoc}
	 */
	public function unsetParameter($strKey)
	{
		\Input::getInstance()->setGet($strKey, null);

		return $this;
	}

	/**
	 * {@inheritDoc}
	 */
	public function hasParameter($strKey)
	{
		return (\Input::getInstance()->get($strKey) !== null);
	}

	/**
	 * {@inheritDoc}
	 */
	public function getValue($strKey, $blnRaw = false)
	{
		if ($blnRaw)
		{
			return \Input::getInstance()->postRaw($strKey);
		}

		// TODO: unsure if we should use postHtml here.
		return \Input::getInstance()->post($strKey);
	}

	/**
	 * {@inheritDoc}
	 */
	public function setValue($strKey, $varValue)
	{
		\Input::getInstance()->setPost($strKey, $varValue);

		return $this;
	}

	/**
	 * {@inheritDoc}
	 */
	public function unsetValue($strKey)
	{
		\Input::getInstance()->setPost($strKey, null);

		return $this;
	}

	/**
	 * {@inheritDoc}
	 */
	public function hasValue($strKey)
	{
		return (\Input::getInstance()->post($strKey) !== null);
	}

	/**
	 * {@inheritDoc}
	 */
	public function getPersistentValue($strKey)
	{
		return \Session::getInstance()->get($strKey);
	}

	/**
	 * {@inheritDoc}
	 */
	public function setPersistentValue($strKey, $varValue)
	{
		\Session::getInstance()->set($strKey, $varValue);

		return $this;
	}

	/**
	 * {@inheritDoc}
	 */
	public function hasPersistentValue($strKey)
	{
		return (\Session::getInstance()->get($strKey) !== null);
	}

	/**
	 * {@inheritDoc}
	 */
	public function getRequestUrl()
	{

		return \Environment::getInstance()->request;
	}
}
