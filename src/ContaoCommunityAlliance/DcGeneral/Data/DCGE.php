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
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Tristan Lins <tristan.lins@bit3.de>
 * @author     Andreas Isaak <andy.jared@googlemail.com>
 * @copyright  2013-2015 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0
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
    const MODEL_SORTING_ASC = 'ASC';

    /**
     * DataProvider sorting order desc
     */
    const MODEL_SORTING_DESC = 'DESC';

    /**
     * Title of an item in a tree view.
     */
    const TREE_VIEW_TITLE = 'dc_gen_tv_title';

    /**
     * The current level in a tree view.
     */
    const TREE_VIEW_LEVEL = 'dc_gen_tv_level';

    /**
     * Is the tree item open.
     */
    const TREE_VIEW_IS_OPEN = 'dc_gen_tv_open';

    /**
     * Child Collection.
     */
    const TREE_VIEW_CHILD_COLLECTION = 'dc_gen_children_collection';

    /**
     * @deprecated Use ModelInterface::LABEL_ARGS
     * @see        ModelInterface::LABEL_ARGS
     */
    const MODEL_LABEL_ARGS = ModelInterface::LABEL_ARGS;

    /**
     * @deprecated Use ModelInterface::GROUP_HEADER
     * @see        ModelInterface::GROUP_HEADER
     */
    const MODEL_GROUP_HEADER = ModelInterface::GROUP_HEADER;

    /**
     * @deprecated Use ModelInterface::GROUP_VALUE
     * @see        ModelInterface::GROUP_VALUE
     */
    const MODEL_GROUP_VALUE = ModelInterface::GROUP_VALUE;

    /**
     * @deprecated Use ModelInterface::CSS_CLASS
     * @see        ModelInterface::CSS_CLASS
     */
    const MODEL_CLASS = ModelInterface::CSS_CLASS;

    /**
     * @deprecated Use ModelInterface::IS_CHANGED
     * @see        ModelInterface::IS_CHANGED
     */
    const MODEL_IS_CHANGED = ModelInterface::IS_CHANGED;

    /**
     * @deprecated Use ModelInterface::CSS_ROW_CLASS
     * @see        ModelInterface::CSS_ROW_CLASS
     */
    const MODEL_EVEN_ODD_CLASS = ModelInterface::CSS_ROW_CLASS;

    /**
     * @deprecated Use ModelInterface::PARENT_ID
     * @see        ModelInterface::PARENT_ID
     */
    const MODEL_PID = ModelInterface::PARENT_ID;

    /**
     * @deprecated Use ModelInterface::PARENT_PROVIDER_NAME
     * @see        ModelInterface::PARENT_PROVIDER_NAME
     */
    const MODEL_PTABLE = ModelInterface::PARENT_PROVIDER_NAME;
}
