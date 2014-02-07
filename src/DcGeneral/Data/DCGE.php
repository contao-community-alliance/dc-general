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

namespace DcGeneral\Data;

/**
 * This class is a holder for all const vars.
 */
class DCGE
{
	/**
	 * Single language
	 */

	const LANGUAGE_SL = 1;

	/**
	 * Multi language
	 */
	const LANGUAGE_ML = 2;

	/**
	 * Move/Insert after Start
	 */
	const INSERT_AFTER_START = 'start';

	/**
	 * Move/Insert after End
	 */
	const INSERT_AFTER_END = 'end';

	/**
	 * Move/Insert into root
	 */
	const INSERT_INTO_ROOT = 'root';

	/**
	 * DataProvider sorting order asc
	 */
	const MODEL_SORTING_ASC = 'ASC';

	/**
	 * DataProvider sorting order desc
	 */
	const MODEL_SORTING_DESC = 'DESC';

	/**
	 * Sorting unsorted.
	 */
	const MODE_NON_SORTING = 0;

	/**
	 * Sorting by a fixed field.
	 */
	const MODE_FIXED_FIELD = 1;

	/**
	 * Sorting by a variable field.
	 */
	const MODE_VARIABLE_FIELD = 2;

	/**
	 * Sorting by the parent record.
	 */
	const MODE_PARENT_VIEW = 3;

	// TODO: SH: CS: mode 4 missing, no idea for a good name.
	/**
	 * Sorting as a simple tree.
	 */
	const MODE_SIMPLE_TREEVIEW = 5;

	/**
	 * Sorting as a parented tree.
	 */
	const MODE_PARENT_TREEVIEW = 6;

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
	 * State if we have children.
	 */
	const TREE_VIEW_HAS_CHILDS = 'dc_gen_tv_children';
	const MODEL_BUTTONS        = '%buttons%';
	const MODEL_LABEL_ARGS     = '%args%';
	const MODEL_LABEL_VALUE    = '%content%';
	const MODEL_GROUP_HEADER   = '%header%';
	const MODEL_GROUP_VALUE    = '%group%';
	const MODEL_CLASS          = '%class%';

	/**
	 * State if the model is changed
	 */
	const MODEL_IS_CHANGED = 'isChanged';

	// TODO: Merge with MODEL_CLASS?
	const MODEL_EVEN_ODD_CLASS = '%rowClass%';

	/**
	 * parents id value.
	 */
	const MODEL_PID = 'pid';

	/**
	 * parents provider name.
	 */
	const MODEL_PTABLE = 'ptable';
}
