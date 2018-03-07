<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2018 Contao Community Alliance.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    contao-community-alliance/dc-general
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Tristan Lins <tristan.lins@bit3.de>
 * @author     Andreas Isaak <andy.jared@googlemail.com>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2013-2018 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Data;

use ContaoCommunityAlliance\DcGeneral\Exception\DcGeneralException;

/**
 * Class TableRowsAsRecordsDataProvider.
 *
 * This data provider allows to map multiple rows of a SQL table into a single model for usage in a MultiColumnWizard.
 */
class TableRowsAsRecordsDataProvider extends DefaultDataProvider
{
    /**
     * Grouping column to use to tie rows together.
     *
     * @var string
     */
    protected $strGroupCol = 'pid';

    /**
     * Sorting column to sort the entries by.
     *
     * @var string
     */
    protected $strSortCol = '';

    /**
     * Set base config with source and other necessary parameter.
     *
     * @param array $arrConfig The configuration to use.
     *
     * @return void
     *
     * @throws DcGeneralException When no source has been defined.
     */
    public function setBaseConfig(array $arrConfig)
    {
        parent::setBaseConfig($arrConfig);

        if (!$arrConfig['group_column']) {
            throw new DcGeneralException(__CLASS__ . ' needs a grouping column.', 1);
        }
        $this->strGroupCol = $arrConfig['group_column'];

        if ($arrConfig['sort_column']) {
            $this->strSortCol = $arrConfig['sort_column'];
        }
    }

    /**
     * Exception throwing convenience method.
     *
     * Convenience method in this data provider that simply throws an Exception stating that the passed method name
     * should not be called on this data provider, as it is only intended to display an edit mask.
     *
     * @param string $strMethod The name of the method being called.
     *
     * @throws DcGeneralException Throws always an exception telling that the method (see param $strMethod) must not be
     *                            called.
     *
     * @return void
     */
    protected function youShouldNotCallMe($strMethod)
    {
        throw new DcGeneralException(
            sprintf(
                'Error, %s not available, as the data provider is intended for edit mode only.',
                $strMethod
            ),
            1
        );
    }

    /**
     * Unsupported in this data provider, throws an Exception.
     *
     * @param mixed $item Unused.
     *
     * @return void
     *
     * @throws DcGeneralException Always throws exception.
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function delete($item)
    {
        $this->youShouldNotCallMe(__METHOD__);
    }

    /**
     * Fetch a single record by id.
     *
     * This data provider only supports retrieving by id so use $objConfig->setId() to populate the config with an Id.
     *
     * @param ConfigInterface $objConfig The configuration to use.
     *
     * @return ModelInterface
     *
     * @throws DcGeneralException If config object does not contain an Id.
     */
    public function fetch(ConfigInterface $objConfig)
    {
        if (!$objConfig->getId()) {
            throw new DcGeneralException(
                'Error, no id passed, TableRowsAsRecordsDriver is only intended for edit mode.',
                1
            );
        }

        $strQuery = sprintf(
            'SELECT %s FROM %s WHERE %s=?',
            DefaultDataProviderSqlUtils::buildFieldQuery($objConfig, $this->idProperty),
            $this->strSource,
            $this->strGroupCol
        );

        if ($this->strSortCol) {
            $strQuery .= ' ORDER BY ' . $this->strSortCol;
        }

        $objResult = $this->objDatabase
            ->prepare($strQuery)
            ->execute($objConfig->getId());

        $objModel = $this->getEmptyModel();
        if ($objResult->numRows) {
            $objModel->setPropertyRaw('rows', $objResult->fetchAllAssoc());
        }

        $objModel->setID($objConfig->getId());

        return $objModel;
    }

    /**
     * Unsupported in this data provider, throws an Exception.
     *
     * @param ConfigInterface $objConfig Unused.
     *
     * @return void
     *
     * @throws DcGeneralException Always throws exception.
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function fetchAll(ConfigInterface $objConfig)
    {
        $this->youShouldNotCallMe(__METHOD__);
    }

    /**
     * Unsupported in this data provider, throws an Exception.
     *
     * @param ConfigInterface $objConfig Unused.
     *
     * @return void
     *
     * @throws DcGeneralException Always throws exception.
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getCount(ConfigInterface $objConfig)
    {
        $this->youShouldNotCallMe(__METHOD__);
    }

    /**
     * Unsupported in this data provider, throws an Exception.
     *
     * @param string $strField Unused.
     * @param mixed  $varNew   Unused.
     * @param int    $intId    Unused.
     *
     * @return void
     *
     * @throws DcGeneralException Always throws exception.
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function isUniqueValue($strField, $varNew, $intId = null)
    {
        $this->youShouldNotCallMe(__METHOD__);
    }

    /**
     * Unsupported in this data provider, throws an Exception.
     *
     * @param string $strField Unused.
     *
     * @return void
     *
     * @throws DcGeneralException Always throws exception.
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function resetFallback($strField)
    {
        $this->youShouldNotCallMe(__METHOD__);
    }

    /**
     * Save a model to the database.
     *
     * In general, this method fetches the solely property "rows" from the model and updates the local table against
     * these contents.
     *
     * The parent id (id of the model) will get checked and reflected also for new items.
     *
     * When rows with duplicate ids are encountered (like from MCW for example), the dupes are inserted as new rows.
     *
     * @param ModelInterface $objItem   The model to save.
     * @param int            $timestamp Optional the timestamp.
     * @param bool           $recursive Ignored as not relevant in this data provider.
     *
     * @return ModelInterface The passed Model.
     *
     * @throws DcGeneralException When the passed model does not contain a property named "rows", an Exception is
     *                            thrown.
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function save(ModelInterface $objItem, $timestamp = 0, $recursive = false)
    {
        if (!is_int($timestamp)) {
            throw new DcGeneralException('The parameter for this method has been change!');
        }
        $arrData = $objItem->getProperty('rows');
        if (!($arrData && $objItem->getID())) {
            throw new DcGeneralException('invalid input data in model.', 1);
        }

        $arrKeep = [];
        foreach ($arrData as $arrRow) {
            $arrSQL = $arrRow;

            // Update all.
            $intId = (int) $arrRow['id'];

            // Work around the fact that multicolumnwizard does not clear any hidden fields when copying a dataset.
            // therefore we do consider any dupe as new dataset and save it accordingly.
            if (in_array($intId, $arrKeep)) {
                $intId = 0;
                unset($arrSQL['id']);
            }

            if ($intId > 0) {
                $this->objDatabase
                    ->prepare(sprintf('UPDATE %s %%s WHERE id=? AND %s=?', $this->strSource, $this->strGroupCol))
                    ->set($arrSQL)
                    ->execute($intId, $objItem->getId());
                $arrKeep[] = $intId;
            } else {
                // Force group col value.
                $arrSQL[$this->strGroupCol] = $objItem->getId();
                $arrKeep[]                  = $this->objDatabase
                    ->prepare(sprintf('INSERT INTO %s %%s', $this->strSource))
                    ->set($arrSQL)
                    ->execute()
                    ->insertId;
            }
        }
        // House keeping, kill the rest.
        $this->objDatabase
            ->prepare(
                sprintf(
                    'DELETE FROM  %s WHERE %s=? AND id NOT IN (%s)',
                    $this->strSource,
                    $this->strGroupCol,
                    implode(',', $arrKeep)
                )
            )
            ->execute($objItem->getId());
        return $objItem;
    }

    /**
     * Unsupported in this data provider, throws an Exception.
     *
     * @param CollectionInterface $objItems  Unused.
     * @param int                 $timestamp Optional the timestamp.
     *
     * @return void
     *
     * @throws DcGeneralException Always throws exception.
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function saveEach(CollectionInterface $objItems, $timestamp = 0)
    {
        $this->youShouldNotCallMe(__METHOD__);
    }

    /**
     * Check if the property exists in the table.
     *
     * This data provider only returns true for the tstamp property.
     *
     * @param string $strField The name of the property to check.
     *
     * @return boolean
     */
    public function fieldExists($strField)
    {
        return 'tstamp' === $strField;
    }

    /**
     * Unsupported in this data provider, throws an Exception.
     *
     * @param mixed $mixID      Unused.
     * @param mixed $mixVersion Unused.
     *
     * @return void
     *
     * @throws DcGeneralException Always throws exception.
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getVersion($mixID, $mixVersion)
    {
        $this->youShouldNotCallMe(__METHOD__);
    }

    /**
     * Return null as versioning is not supported in this data provider.
     *
     * @param mixed   $mixID         Unused.
     * @param boolean $blnOnlyActive Unused.
     *
     * @return null
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getVersions($mixID, $blnOnlyActive = false)
    {
        // Sorry, versioning not supported.
        return null;
    }

    /**
     * Unsupported in this data provider, throws an Exception.
     *
     * @param ModelInterface $objModel    Unused.
     * @param string         $strUsername Unused.
     *
     * @return void
     *
     * @throws DcGeneralException Always throws exception.
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function saveVersion(ModelInterface $objModel, $strUsername)
    {
        $this->youShouldNotCallMe(__METHOD__);
    }

    /**
     * Unsupported in this data provider, throws an Exception.
     *
     * @param mixed $mixID      Unused.
     * @param mixed $mixVersion Unused.
     *
     * @return void
     *
     * @throws DcGeneralException Always throws exception.
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function setVersionActive($mixID, $mixVersion)
    {
        $this->youShouldNotCallMe(__METHOD__);
    }

    /**
     * Unsupported in this data provider, throws an Exception.
     *
     * @param mixed $mixID Unused.
     *
     * @return void
     *
     * @throws DcGeneralException Always throws exception.
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getActiveVersion($mixID)
    {
        $this->youShouldNotCallMe(__METHOD__);
    }

    /**
     * Unsupported in this data provider, throws an Exception.
     *
     * @param ModelInterface $objModel1 Unused.
     * @param ModelInterface $objModel2 Unused.
     *
     * @return void
     *
     * @throws DcGeneralException Always throws exception.
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function sameModels($objModel1, $objModel2)
    {
        $this->youShouldNotCallMe(__METHOD__);
    }

    /**
     * Unsupported in this data provider, throws an Exception.
     *
     * @param string $strSourceSQL Unused.
     * @param string $strSaveSQL   Unused.
     * @param string $strTable     Unused.
     *
     * @return void
     *
     * @throws DcGeneralException Always throws exception.
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function insertUndo($strSourceSQL, $strSaveSQL, $strTable)
    {
        $this->youShouldNotCallMe(__METHOD__);
    }
}
