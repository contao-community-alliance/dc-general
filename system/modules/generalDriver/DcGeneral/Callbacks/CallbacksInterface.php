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

namespace DcGeneral\Callbacks;

use DcGeneral\Data\ModelInterface;

interface CallbacksInterface
{

	/**
	 * Set the DC.
	 *
	 * @param \DcGeneral\DC_General $objDC
	 */
	public function setDC($objDC);

	/**
	 * Get the DC.
	 *
	 * @return \DcGeneral\DC_General
	 */
	public function getDC();

	/**
	 * Execute the passed callbacks.
	 *
	 * The returned array will hold all result values from all via $varCallbacks defined callbacks.
	 *
	 * @param mixed $varCallbacks Either the name of an HOOK defined in $GLOBALS['TL_HOOKS'] or an array of
	 *                            array('Class', 'method') pairs.
	 *
	 * @return array
	 */
	public function executeCallbacks($varCallbacks);

	/**
	 * Call the customer label callback.
	 *
	 * @param ModelInterface  $objModelRow The current model for which the label shall get generated for.
	 *
	 * @param string $mixedLabel  The label string (as defined in DCA).
	 *
	 * @param array  $args        The arguments for the label string.
	 *
	 * @return string
	 */
	public function labelCallback(ModelInterface $objModelRow, $mixedLabel, $args);

	/**
	 * Call the button callback for the regular operations.
	 *
	 * @param ModelInterface   $objModelRow          The current model instance for which the button shall be
	 *                                      generated.
	 *
	 * @param array   $arrOperation         The operation for which a button shall be generated
	 *                                      (excerpt from DCA).
	 *
	 * @param string  $strLabel             The label for the button.
	 *
	 * @param string  $strTitle             The title for the button.
	 *
	 * @param array   $arrAttributes        Attributes for the generated button.
	 *
	 * @param string  $strTable             The dataprovider name of the view.
	 *
	 * @param array   $arrRootIds           The root ids
	 *
	 * @param array   $arrChildRecordIds    Ids of the direct children to the model in $objModelRow.
	 *
	 * @param boolean $blnCircularReference TODO: document parameter $blnCircularReference
	 *
	 * @param string  $strPrevious          TODO: document parameter $strPrevious
	 *
	 * @param string  $strNext              TODO: document parameter $strNext
	 *
	 * @return string|null
	 */
	public function buttonCallback($objModelRow, $arrOperation, $strLabel, $strTitle, $arrAttributes, $strTable, $arrRootIds, $arrChildRecordIds, $blnCircularReference, $strPrevious, $strNext);

	/**
	 * Call the button callback for the global operations.
	 *
	 * @param string $strLabel      Label for the button.
	 *
	 * @param string $strTitle      Title for the button.
	 *
	 * @param array  $arrAttributes Attributes for the button
	 *
	 * @param string $strTable      Name of the current data provider.
	 *
	 * @param array  $arrRootIds    Ids of the root elements in the data provider.
	 *
	 * @return string|null
	 */
	public function globalButtonCallback($strLabel, $strTitle, $arrAttributes, $strTable, $arrRootIds);

	/**
	 * Call the button callback for the paste operations
	 * TODO: this should be included in the interface when the signature has been finished.
	 *
	 * @param array         $row      Array with current data
	 *
	 * @param string        $table    Table name
	 *
	 * @param bool          $cr        TODO: document parameter $cr
	 *
	 * @param array         $objClipboard   Clipboard information
	 *
	 * @param string        $previous  TODO: document parameter $previous
	 *
	 * @param string        $next      TODO: document parameter $next
	 *
	 * @return string
	 */
	public function pasteButtonCallback($row, $table, $cr, $objClipboard, $previous, $next);

	/**
	 * Call the header callback.
	 *
	 * @param array $arrAdd TODO: document parameter $arrAdd
	 *
	 * @return array|null
	 */
	public function headerCallback($arrAdd);

	/**
	 * Call the child record callback.
	 *
	 * @param ModelInterface $objModel TODO: document parameter $objModel
	 *
	 * @return string|null
	 */
	public function childRecordCallback(ModelInterface $objModel);

	/**
	 * Call the options callback for given the field.
	 *
	 * @param string $strField Name of the field for which to call the options callback.
	 *
	 * @return array|null
	 */
	public function optionsCallback($strField);

	/**
	 * Call the onrestore callback.
	 *
	 * @param integer $intID      ID of current dataset.
	 *
	 * @param string  $strTable   Name of current Table.
	 *
	 * @param array   $arrData    Array with all Data.
	 *
	 * @param integer $intVersion Version which was restored.
	 *
	 * @return void
	 */
	public function onrestoreCallback($intID, $strTable, $arrData, $intVersion);

	/**
	 * Call the load callback.
	 *
	 * @param string $strField Name of the field for which to call the load callback.
	 *
	 * @param mixed $varValue  Current value to be transformed.
	 *
	 * @return mixed|null
	 */
	public function loadCallback($strField, $varValue);

	/**
	 * Call onload_callback (e.g. to check permissions).
	 *
	 * @return void
	 */
	public function onloadCallback();

	/**
	 * Call the group callback.
	 *
	 * @param type  $group TODO: document parameter $group
	 *
	 * @param type  $mode  TODO: document parameter $mode
	 *
	 * @param type  $field TODO: document parameter $field
	 *
	 * @param ModelInterface $objModelRow
	 *
	 * @return type  TODO: document result
	 */
	public function groupCallback($group, $mode, $field, $objModelRow);

	/**
	 * Call the save callback for a widget.
	 *
	 * @param array $arrConfig Configuration of the widget.
	 *
	 * @param mixed $varNew    The new value that shall be transformed.
	 *
	 * @return mixed
	 */
	public function saveCallback($arrConfig, $varNew);

	/**
	 * Call ondelete_callback.
	 *
	 * @return void
	 */
	public function ondeleteCallback();

	/**
	 * Call the onsubmit_callback.
	 *
	 * @return void
	 */
	public function onsubmitCallback();

	/**
	 * Call the oncreate_callback.
	 *
	 * @param mixed $insertID  The id from the new record.
	 *
	 * @param array $arrRecord The new record.
	 *
	 * @return void
	 */
	public function oncreateCallback($insertID, $arrRecord);


	/**
	 * Get the current palette.
	 *
	 * @param array $arrPalette The current palette.
	 *
	 * @return array The modified palette.
	 */
	public function parseRootPaletteCallback($arrPalette);

	/**
	 * Call the onmodel_beforeupdate.
	 *
	 * NOTE: the fact that this method has been called does not mean the values of the model have been changed
	 * it merely just tells "we will load a model (from memory or database) and update it's properties with
	 * those from the POST data".
	 *
	 * After the model has been updated, the onModelUpdateCallback will get triggered.
	 *
	 * @param ModelInterface $objModel The model that will get updated.
	 *
	 * @return void
	 */
	public function onModelBeforeUpdateCallback($objModel);

	/**
	 * Call the onmodel_update.
	 * NOTE: the fact that this method has been called does not mean the values of the model have been changed
	 * it merely just tells "we have loaded a model (from memory or database) and updated it's properties with
	 * those from the POST data".
	 *
	 * @param ModelInterface $objModel The model that has been updated.
	 *
	 * @return void
	 */
	public function onModelUpdateCallback($objModel);

	/**
	 * Generate the backend breadcrumb.
	 *
	 * @return array|null
	 */
	public function generateBreadcrumb();

	/**
	 * Call the custom filter callback for a field.
	 *
	 * Return true if the processing has been successfully applied, false otherwise.
	 * NOTE: GeneralController will stop further processing of the given field if true has been returned and will
	 * continue to process filtering if false has been returned.
	 *
	 * @param string $strField   The field to filter.
	 *
	 * @param array  $arrSession The currently saved values of the filter session.
	 *
	 * @return bool
	 */
	public function customFilterCallback($strField, $arrSession);
}
