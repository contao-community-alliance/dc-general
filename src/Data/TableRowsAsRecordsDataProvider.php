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
 * @author     David Molineus <david.molineus@netzmacht.de>
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
     * @param array $config The configuration to use.
     *
     * @return void
     *
     * @throws DcGeneralException When no source has been defined.
     */
    public function setBaseConfig(array $config)
    {
        parent::setBaseConfig($config);

        if (!$config['group_column']) {
            throw new DcGeneralException(__CLASS__ . ' needs a grouping column.', 1);
        }
        $this->strGroupCol = $config['group_column'];

        if ($config['sort_column']) {
            $this->strSortCol = $config['sort_column'];
        }
    }

    /**
     * Get the property name that shall get updated with the current grouping when saving to the database.
     *
     * @return string
     */
    public function getGroupColumnProperty()
    {
        return $this->strGroupCol;
    }

    /**
     * Get the property name that shall get updated with the current sorting when saving to the database.
     *
     * @return string
     */
    public function getSortingColumnProperty()
    {
        return $this->strSortCol;
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
            \sprintf(
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
     * @param ConfigInterface $config The configuration to use.
     *
     * @return ModelInterface
     *
     * @throws DcGeneralException If config object does not contain an Id.
     */
    public function fetch(ConfigInterface $config)
    {
        if (!$config->getId()) {
            throw new DcGeneralException(
                'Error, no id passed, TableRowsAsRecordsDriver is only intended for edit mode.',
                1
            );
        }

        $queryBuilder = $this->connection->createQueryBuilder();
        DefaultDataProviderDBalUtils::addField($config, $this->idProperty, $queryBuilder);
        $queryBuilder->from($this->source);
        $queryBuilder->where($queryBuilder->expr()->eq($this->strGroupCol, ':' . $this->strGroupCol));
        $queryBuilder->setParameter($this->strGroupCol, $config->getId());

        if ($this->strSortCol) {
            $queryBuilder->orderBy($this->strSortCol, 'ASC');
        }

        $statement = $queryBuilder->execute();

        $model = $this->getEmptyModel();
        $model->setID($config->getId());
        if (0 < $statement->rowCount()) {
            $model->setPropertyRaw('rows', $statement->fetchAll(\PDO::FETCH_ASSOC));
        }

        return $model;
    }

    /**
     * Unsupported in this data provider, throws an Exception.
     *
     * @param ConfigInterface $config Unused.
     *
     * @return void
     *
     * @throws DcGeneralException Always throws exception.
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function fetchAll(ConfigInterface $config)
    {
        $this->youShouldNotCallMe(__METHOD__);
    }

    /**
     * Unsupported in this data provider, throws an Exception.
     *
     * @param ConfigInterface $config Unused.
     *
     * @return void
     *
     * @throws DcGeneralException Always throws exception.
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getCount(ConfigInterface $config)
    {
        $this->youShouldNotCallMe(__METHOD__);
    }

    /**
     * Unsupported in this data provider, throws an Exception.
     *
     * @param string $field     Unused.
     * @param mixed  $new       Unused.
     * @param int    $primaryId Unused.
     *
     * @return void
     *
     * @throws DcGeneralException Always throws exception.
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function isUniqueValue($field, $new, $primaryId = null)
    {
        $this->youShouldNotCallMe(__METHOD__);
    }

    /**
     * Unsupported in this data provider, throws an Exception.
     *
     * @param string $field Unused.
     *
     * @return void
     *
     * @throws DcGeneralException Always throws exception.
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function resetFallback($field)
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
     * @param ModelInterface $item      The model to save.
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
    public function save(ModelInterface $item, $timestamp = 0, $recursive = false)
    {
        if (!\is_int($timestamp)) {
            throw new DcGeneralException('The parameter for this method has been change!');
        }
        $data = $item->getProperty('rows');
        if (!($data && $item->getId())) {
            throw new DcGeneralException('invalid input data in model.', 1);
        }

        $keep = [];
        foreach ($data as $row) {
            $sqlData = $row;

            // Update all.
            $intId = (int) $row['id'];

            // Always unset id.
            unset($sqlData['id']);

            // Work around the fact that multicolumnwizard does not clear any hidden fields when copying a dataset.
            // therefore we do consider any dupe as new dataset and save it accordingly.
            if (\in_array($intId, $keep)) {
                $intId = 0;
            }

            if ($intId > 0) {
                $this->connection->update(
                    $this->source,
                    $sqlData,
                    ['id' => $intId, $this->strGroupCol => $item->getId()]
                );
                $keep[] = $intId;
            } else {
                // Force group col value.
                $sqlData[$this->strGroupCol] = $item->getId();

                $this->connection->insert($this->source, $sqlData);

                $keep[] = $this->connection->lastInsertId($this->source);
            }
        }

        // House keeping, kill the rest.
        $queryBuilder = $this->connection->createQueryBuilder();
        $queryBuilder->delete($this->source);
        $queryBuilder->andWhere($queryBuilder->expr()->eq($this->strGroupCol, ':' . $this->strGroupCol));
        $queryBuilder->setParameter(':' . $this->strGroupCol, $item->getId());
        $queryBuilder->andWhere($queryBuilder->expr()->notIn('id', $keep));

        $queryBuilder->execute();

        return $item;
    }

    /**
     * Unsupported in this data provider, throws an Exception.
     *
     * @param CollectionInterface $items Unused.
     * @param int                 $timestamp Optional the timestamp.
     *
     * @return void
     *
     * @throws DcGeneralException Always throws exception.
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function saveEach(CollectionInterface $items, $timestamp = 0)
    {
        $this->youShouldNotCallMe(__METHOD__);
    }

    /**
     * Check if the property exists in the table.
     *
     * This data provider only returns true for the tstamp property.
     *
     * @param string $columnName The name of the property to check.
     *
     * @return boolean
     */
    public function fieldExists($columnName)
    {
        return 'tstamp' === $columnName;
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
     * @param mixed   $mixID      Unused.
     * @param boolean $onlyActive Unused.
     *
     * @return null
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getVersions($mixID, $onlyActive = false)
    {
        // Sorry, versioning not supported.
        return null;
    }

    /**
     * Unsupported in this data provider, throws an Exception.
     *
     * @param ModelInterface $model    Unused.
     * @param string         $username Unused.
     *
     * @return void
     *
     * @throws DcGeneralException Always throws exception.
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function saveVersion(ModelInterface $model, $username)
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
     * @param ModelInterface $firstModel  Unused.
     * @param ModelInterface $secondModel Unused.
     *
     * @return void
     *
     * @throws DcGeneralException Always throws exception.
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function sameModels($firstModel, $secondModel)
    {
        $this->youShouldNotCallMe(__METHOD__);
    }

    /**
     * Unsupported in this data provider, throws an Exception.
     *
     * @param string $sourceSQL Unused.
     * @param string $saveSQL   Unused.
     * @param string $table     Unused.
     *
     * @return void
     *
     * @throws DcGeneralException Always throws exception.
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function insertUndo($sourceSQL, $saveSQL, $table)
    {
        $this->youShouldNotCallMe(__METHOD__);
    }
}
