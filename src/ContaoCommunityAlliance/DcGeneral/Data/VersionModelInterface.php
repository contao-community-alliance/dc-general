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

namespace ContaoCommunityAlliance\DcGeneral\Data;

interface VersionModelInterface extends ModelInterface
{
	/**
	 * Return the version string of this model version.
	 *
	 * @return string
	 */
	public function getVersion();

	/**
	 * Determine if this is the current version of the model.
	 *
	 * @return bool
	 */
	public function isCurrent();

	/**
	 * Return the data time this version was created.
	 *
	 * @return \DateTime
	 */
	public function getDateTime();

	/**
	 * Return the name of the version's author, at the moment the version was created.
	 *
	 * @return string
	 */
	public function getAuthorName();

	/**
	 * Return the username of the version's author, at the moment the version was created.
	 *
	 * @return string
	 */
	public function getAuthorUsername();

	/**
	 * Return the email of the version's author, at the moment the version was created.
	 *
	 * @return string
	 */
	public function getAuthorEmail();
}
