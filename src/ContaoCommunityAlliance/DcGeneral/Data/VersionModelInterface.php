<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2015 Contao Community Alliance.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    contao-community-alliance/dc-general
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Tristan Lins <tristan.lins@bit3.de>
 * @copyright  2013-2015 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/LICENSE LGPL-3.0
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Data;

/**
 * This interface describes a versioned model.
 */
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
