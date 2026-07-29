<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2026 Contao Community Alliance.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    contao-community-alliance/dc-general
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2013-2026 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

declare(strict_types=1);

namespace ContaoCommunityAlliance\DcGeneral\Data;

/**
 * A data provider that only serves the edit mode.
 *
 * Such a provider aggregates the rows belonging to a parent into a single record and cannot answer
 * a listing - calling fetchAll() on it is an error rather than an empty result. Anything that would
 * otherwise route to the list of such a table has to know this: the list handler forwards to the
 * edit action instead, and the back url leaves the table behind rather than pointing at a list that
 * cannot be rendered.
 *
 * @api
 */
interface EditOnlyDataProviderInterface
{
    /**
     * Retrieve the id of the record that aggregates the rows of the passed parent.
     *
     * The edit mask is reached with that id. Implementations that key their aggregate by the parent
     * itself simply hand the value back.
     *
     * @param string $parentId The id of the parent, without the provider prefix.
     *
     * @return string
     */
    public function getIdForParent(string $parentId): string;
}
