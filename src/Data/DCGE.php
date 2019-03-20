<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2019 Contao Community Alliance.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    contao-community-alliance/dc-general
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Tristan Lins <tristan.lins@bit3.de>
 * @author     Andreas Isaak <andy.jared@googlemail.com>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2013-2019 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Data;

/**
 * This class is a holder for all const vars.
 */
class DCGE
{
    /**
     * DataProvider sorting order asc
     */
    public const MODEL_SORTING_ASC = 'ASC';

    /**
     * DataProvider sorting order desc
     */
    public const MODEL_SORTING_DESC = 'DESC';

    /**
     * The current level in a tree view.
     */
    public const TREE_VIEW_LEVEL = 'dc_gen_tv_level';

    /**
     * Is the tree item open.
     *
     * @deprecated Use \ContaoCommunityAlliance\DcGeneral\Data\ModelInterface::SHOW_CHILDREN
     */
    public const TREE_VIEW_IS_OPEN = ModelInterface::SHOW_CHILDREN;

    /**
     * Child Collection.
     *
     * @deprecated Use \ContaoCommunityAlliance\DcGeneral\Data\ModelInterface::CHILD_COLLECTIONS
     */
    public const TREE_VIEW_CHILD_COLLECTION = ModelInterface::CHILD_COLLECTIONS;
}
