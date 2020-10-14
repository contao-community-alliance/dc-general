<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2020 Contao Community Alliance.
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
 * @author     David Molineus <mail@netzmacht.de>
 * @author     Oliver Hoff <oliver@hofff.com>
 * @author     Patrick Kahl <kahl.patrick@googlemail.com>
 * @author     Simon Kusterer <simon@soped.com>
 * @author     Christopher Boelter <christopher@boelter.eu>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @author     Richard Henkenjohann <richardhenkenjohann@googlemail.com>
 * @author     Alex Wuttke <alex@das-l.de>
 * @copyright  2013-2020 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Data;

use Contao\BackendUser;
use Contao\Database;
use Contao\StringUtil;
use Contao\System;
use ContaoCommunityAlliance\DcGeneral\Exception\DcGeneralRuntimeException;
use Doctrine\DBAL\Connection;

/**
 * Class DefaultDataProvider.
 *
 * Default implementation for a data provider using the Contao default database as backend.
 *
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)     - We have to keep them as we implement the interfaces.
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity) - There is no elegant way to reduce this class more without
 *                                                     reducing the interface.
 */
class DefaultDataProvider implements DataProviderInterface
{
    /**
     * Name of current source.
     *
     * @var string
     */
    protected $source;

    /**
     * The Database instance.
     *
     * @var Connection
     */
    protected $connection;

    /**
     * The name of the id property.
     *
     * @var string
     */
    protected $idProperty = 'id';

    /**
     * The property that shall get populated with the current timestamp when saving data.
     *
     * @var string
     */
    protected $timeStampProperty = false;

    /**
     * The id generator to use (if any).
     *
     * @var IdGeneratorInterface
     */
    protected $idGenerator;

    /**
     * Retrieve the name of the id property.
     *
     * @return string
     */
    public function getIdProperty()
    {
        return $this->idProperty;
    }

    /**
     * Set the id property.
     *
     * @param string $idProperty The name of the id property.
     *
     * @return DefaultDataProvider
     */
    public function setIdProperty($idProperty)
    {
        $this->idProperty = $idProperty;

        return $this;
    }

    /**
     * Get the property name that shall get updated with the current time stamp when saving to the database.
     *
     * @return string|null
     */
    public function getTimeStampProperty()
    {
        return $this->timeStampProperty;
    }

    /**
     * Set the property name that shall get updated with the current time stamp when saving to the database.
     *
     * @param boolean $timeStampField The property name or empty to clear.
     *
     * @return DefaultDataProvider
     */
    public function setTimeStampProperty($timeStampField = null)
    {
        $this->timeStampProperty = $timeStampField;

        return $this;
    }

    /**
     * Set the id generator to use.
     *
     * @param IdGeneratorInterface $idGenerator The id generator.
     *
     * @return DefaultDataProvider
     */
    public function setIdGenerator($idGenerator)
    {
        $this->idGenerator = $idGenerator;

        return $this;
    }

    /**
     * Retrieve the id generator.
     *
     * @return IdGeneratorInterface
     */
    public function getIdGenerator()
    {
        return $this->idGenerator;
    }

    /**
     * Create an instance of the default database driven uuid generator.
     *
     * @return DefaultDataProvider
     *
     * @throws DcGeneralRuntimeException When already an id generator has been set on the instance.
     */
    public function enableDefaultUuidGenerator()
    {
        if ($this->idGenerator) {
            throw new DcGeneralRuntimeException('Error: already an id generator set on database provider.');
        }

        $this->setIdGenerator(new DatabaseUuidIdGenerator($this->connection));

        return $this;
    }

    /**
     * {@inheritDoc}
     *
     * @throws DcGeneralRuntimeException When no source has been defined.
     * @throws DcGeneralRuntimeException For invalid database connection.
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function setBaseConfig(array $config)
    {
        // Check configuration.
        if (!isset($config['source'])) {
            throw new DcGeneralRuntimeException('Missing table name.');
        }

        $this->fallbackFromDatabaseToConnection($config);

        if (isset($config['connection'])) {
            if (!($config['connection'] instanceof Connection)) {
                throw new DcGeneralRuntimeException('Invalid database connection.');
            }

            $this->connection = $config['connection'];
        } else {
            // @codingStandardsIgnoreStart
            @\trigger_error(
                'You should pass a doctrine database connection to "' . __METHOD__ . '".',
                E_USER_DEPRECATED
            );
            // @codingStandardsIgnoreEnd

            $this->connection = $this->getDefaultConnection();
        }

        if (isset($config['idGenerator'])) {
            if ($config['idGenerator'] instanceof IdGeneratorInterface) {
                $idGenerator = $config['idGenerator'];
                $this->setIdGenerator($idGenerator);
            }
        }

        $this->source = $config['source'];

        if (isset($config['timeStampProperty'])) {
            $this->setTimeStampProperty($config['timeStampProperty']);
        } elseif ($this->fieldExists('tstamp')) {
            $this->setTimeStampProperty('tstamp');
        }

        if (isset($config['idProperty'])) {
            $this->setIdProperty($config['idProperty']);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getEmptyConfig()
    {
        return DefaultConfig::init();
    }

    /**
     * {@inheritDoc}
     */
    public function getEmptyModel()
    {
        $model = new DefaultModel();
        $model->setProviderName($this->source);
        return $model;
    }

    /**
     * {@inheritDoc}
     */
    public function getEmptyCollection()
    {
        return new DefaultCollection();
    }

    /**
     * Fetch an empty single filter option collection (new model list).
     *
     * @return FilterOptionCollectionInterface
     *
     * @deprecated This method was never intended to be used externally.
     */
    public function getEmptyFilterOptionCollection()
    {
        // @codingStandardsIgnoreStart
        @\trigger_error(
            'Method ' . __METHOD__ . ' was never intended to be called via interface and will get removed',
            E_USER_DEPRECATED
        );
        // @codingStandardsIgnoreEnd
        return new DefaultFilterOptionCollection();
    }

    /**
     * {@inheritDoc}
     *
     * @throws DcGeneralRuntimeException When an unusable object has been passed.
     */
    public function delete($item)
    {
        $modelId = null;
        if (\is_numeric($item) || \is_string($item)) {
            $modelId = $item;
        } elseif (\is_object($item) && $item instanceof ModelInterface && null !== $item->getId()) {
            $modelId = $item->getId();
        } else {
            throw new DcGeneralRuntimeException("ID missing or given object not of type 'ModelInterface'.");
        }

        // Insert undo.
        $this->insertUndo(
            \sprintf(
                'DELETE FROM %1$s WHERE %1$s.id = %2$s',
                $this->source,
                $modelId
            ),
            \sprintf(
                'SELECT * FROM %1$s WHERE %1$s.id = %2$s',
                $this->source,
                $modelId
            ),
            $this->source
        );

        $this->connection->delete($this->source, [$this->source . '.id' => $modelId]);
    }

    /**
     * Create a model from a database result.
     *
     * @param array $result The database result to create a model from.
     *
     * @return ModelInterface
     */
    protected function createModelFromDatabaseResult(array $result)
    {
        $model = $this->getEmptyModel();

        foreach ($result as $key => $value) {
            if ($key === $this->idProperty) {
                $model->setIdRaw($value);
            }

            $model->setPropertyRaw($key, StringUtil::deserialize($value));
        }

        return $model;
    }

    /**
     * {@inheritDoc}
     */
    public function fetch(ConfigInterface $config)
    {
        $queryBuilder = $this->connection->createQueryBuilder();
        $queryBuilder->from($this->source);
        DefaultDataProviderDBalUtils::addField($config, $this->idProperty, $queryBuilder);

        if (null !== $config->getId()) {
            $queryBuilder->where($this->source . '.id=:id');
            $queryBuilder->setParameter(':id', $config->getId());
        }

        if (null === $config->getId()) {
            DefaultDataProviderDBalUtils::addWhere($config, $queryBuilder);
            DefaultDataProviderDBalUtils::addSorting($config, $queryBuilder);

            $queryBuilder->setMaxResults(1);
            $queryBuilder->setFirstResult(0);
        }

        $statement = $queryBuilder->execute();
        if (0 === $statement->rowCount()) {
            return null;
        }

        $result = $statement->fetch(\PDO::FETCH_ASSOC);

        return $this->createModelFromDatabaseResult($result);
    }

    /**
     * {@inheritDoc}
     */
    public function fetchAll(ConfigInterface $config)
    {
        $queryBuilder = $this->connection->createQueryBuilder();
        $queryBuilder->from($this->source);
        DefaultDataProviderDBalUtils::addField($config, $this->idProperty, $queryBuilder);
        DefaultDataProviderDBalUtils::addWhere($config, $queryBuilder);
        DefaultDataProviderDBalUtils::addSorting($config, $queryBuilder);

        if (0 !== $config->getAmount()) {
            $queryBuilder->setMaxResults($config->getAmount());
            $queryBuilder->setFirstResult($config->getStart());
        }

        $collection = $this->getEmptyCollection();

        $statement = $queryBuilder->execute();
        if (0 === $statement->rowCount()) {
            return $collection;
        }

        if ($config->getIdOnly()) {
            return $statement->fetchAll(\PDO::FETCH_COLUMN);
        }

        $result = $statement->fetchAll(\PDO::FETCH_ASSOC);

        foreach ($result as $item) {
            $collection->push($this->createModelFromDatabaseResult($item));
        }

        return $collection;
    }

    /**
     * {@inheritDoc}
     *
     * @throws DcGeneralRuntimeException If improper values have been passed (i.e. not exactly one field requested).
     */
    public function getFilterOptions(ConfigInterface $config)
    {
        $internalConfig = $this->prefixDataProviderProperties($config);
        $properties     = $internalConfig->getFields();
        if (1 !== \count($properties)) {
            throw new DcGeneralRuntimeException('objConfig must contain exactly one property to be retrieved.');
        }
        $property = $properties[0];

        $queryBuilder = $this->connection->createQueryBuilder();
        $queryBuilder->select('DISTINCT(' . $property . ')');
        $queryBuilder->from($this->source);
        DefaultDataProviderDBalUtils::addWhere($internalConfig, $queryBuilder);

        $statement = $queryBuilder->execute();

        $values = $statement->fetchAll(\PDO::FETCH_OBJ);

        $filterPropertyName = $property;
        // Remove the data provider name from the filter property name, if exist.
        if (0 === \strpos($filterPropertyName, $this->source . '.')) {
            $filterPropertyName = \substr($filterPropertyName, \strlen($this->source . '.'));
        }
        $collection = new DefaultFilterOptionCollection();
        foreach ($values as $value) {
            $collection->add($value->$filterPropertyName, $value->$filterPropertyName);
        }

        return $collection;
    }

    /**
     * {@inheritDoc}
     */
    public function getCount(ConfigInterface $config)
    {
        $internalConfig = $this->prefixDataProviderProperties($config);
        $queryBuilder   = $this->connection->createQueryBuilder();
        $queryBuilder->select('COUNT(*) AS count');
        $queryBuilder->from($this->source);
        DefaultDataProviderDBalUtils::addWhere($internalConfig, $queryBuilder);

        $statement = $queryBuilder->execute();

        return $statement->fetch(\PDO::FETCH_COLUMN);
    }

    /**
     * {@inheritDoc}
     */
    public function isUniqueValue($field, $new, $primaryId = null)
    {
        $queryBuilder = $this->connection->createQueryBuilder();
        $queryBuilder->select('*');
        $queryBuilder->from($this->source);
        $queryBuilder->where($queryBuilder->expr()->eq($this->source . '.' . $field, ':' . $field));
        $queryBuilder->setParameter(':' . $field, $new);

        $statement = $queryBuilder->execute();
        $unique    = $statement->fetch(\PDO::FETCH_OBJ);

        if (0 === $statement->rowCount()) {
            return true;
        }

        if (($primaryId === $unique->id) && (1 === $statement->rowCount())) {
            return true;
        }

        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function resetFallback($field)
    {
        // @codingStandardsIgnoreStart
        @\trigger_error(
            __CLASS__ . '::' . __METHOD__ . ' is deprecated - handle resetting manually',
            E_USER_DEPRECATED
        );
        // @codingStandardsIgnoreEnd

        $this->connection->query('UPDATE ' . $this->source . ' SET ' . $field . ' = \'\'')->execute();
    }

    /**
     * Prefix the data provider properties.
     *
     * @param ConfigInterface $config The config.
     *
     * @return ConfigInterface
     */
    private function prefixDataProviderProperties(ConfigInterface $config)
    {
        $internalConfig = clone $config;
        $this->sortingPrefixer($internalConfig);

        if (null !== ($filter = $internalConfig->getFilter())) {
            $this->filterPrefixer($filter);
            $internalConfig->setFilter($filter);
        }

        if (null !== ($fields = $internalConfig->getFields())) {
            $this->fieldPrefixer($fields);
            $internalConfig->setFields($fields);
        }

        return $internalConfig;
    }

    /**
     * The config sorting prefixer.
     *
     * @param ConfigInterface $config The config.
     *
     * @return void
     */
    private function sortingPrefixer(ConfigInterface $config)
    {
        $sorting = [];
        foreach ($config->getSorting() as $property => $value) {
            if (0 === \strpos($property, $this->source . '.')) {
                $sorting[$property] = $value;

                continue;
            }

            if (!$this->fieldExists($property)) {
                continue;
            }

            $sorting[$this->source . '.' . $property] = $value;
        }
        $config->setSorting($sorting);
    }

    /**
     * The filter prefixer.
     *
     * @param array $filter The filter setting.
     *
     * @return void
     */
    private function filterPrefixer(array &$filter)
    {
        foreach ($filter as &$child) {
            if (\array_key_exists('property', $child)
                && (false === \strpos($child['property'], $this->source . '.'))
                && $this->fieldExists($child['property'])
            ) {
                $child['property'] = $this->source . '.' . $child['property'];
            }

            if (\array_key_exists('children', $child)) {
                $this->filterPrefixer($child['children']);
            }
        }
    }

    /**
     * The field prefixer.
     *
     * @param array $fields The fields.
     *
     * @return void
     */
    private function fieldPrefixer(array &$fields)
    {
        foreach ($fields as $index => $property) {
            if (0 === \strpos($property, $this->source . '.')
                || !$this->fieldExists($property)
            ) {
                continue;
            }

            $fields[$index] = $this->source . '.' . $property;
        }
    }

    /**
     * Convert a model into a property array to be used in insert and update queries.
     *
     * @param ModelInterface $model     The model to convert into an property array.
     * @param int            $timestamp Optional the timestamp.
     *
     * @return array
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    private function convertModelToDataPropertyArray(ModelInterface $model, int $timestamp)
    {
        $data = [];
        foreach ($model as $key => $value) {
            if (($key === $this->idProperty) || !$this->fieldExists($key)) {
                continue;
            }

            if (\is_array($value)) {
                $data[$this->source . '.' . $key] = \serialize($value);
            } else {
                $data[$this->source . '.' . $key] = $value;
            }
        }

        if ($this->timeStampProperty) {
            $data[$this->source . '.' . $this->getTimeStampProperty()] = $timestamp ?: \time();
        }

        return $data;
    }

    /**
     * Insert the model into the database.
     *
     * @param ModelInterface $model     The model to insert into the database.
     * @param int            $timestamp Optional the timestamp.
     *
     * @return void
     */
    private function insertModelIntoDatabase(ModelInterface $model, $timestamp = 0)
    {
        $data = $this->convertModelToDataPropertyArray($model, $timestamp);
        if ($this->getIdGenerator()) {
            $model->setId($this->getIdGenerator()->generate());
            $data[$this->idProperty] = $model->getId();
        }

        $this->connection->insert($this->source, $data);

        $insertId = $this->connection->lastInsertId($this->source);

        if (('' !== $insertId) && !isset($data[$this->idProperty])) {
            $model->setId($insertId);
        }
    }

    /**
     * Update the model in the database.
     *
     * @param ModelInterface $model     The model to update the database.
     * @param int            $timestamp Optional the timestamp.
     *
     * @return void
     */
    private function updateModelInDatabase($model, $timestamp = 0)
    {
        $data = $this->convertModelToDataPropertyArray($model, $timestamp);

        $this->connection->update($this->source, $data, ['id' => $model->getId()]);
    }

    /**
     * {@inheritDoc}
     */
    public function save(ModelInterface $item, $timestamp = 0)
    {
        if (\in_array($item->getId(), [null, ''])) {
            $this->insertModelIntoDatabase($item);
        } else {
            $this->updateModelInDatabase($item);
        }

        return $item;
    }

    /**
     * {@inheritDoc}
     */
    public function saveEach(CollectionInterface $items, $timestamp = 0)
    {
        foreach ($items as $value) {
            $this->save($value, $timestamp);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function fieldExists($columnName)
    {
        $tableDetails = $this->connection->getSchemaManager()->listTableDetails($this->source);

        return $tableDetails->hasColumn($columnName);
    }

    /**
     * {@inheritDoc}
     */
    public function getVersion($mixID, $mixVersion)
    {
        $queryBuilder = $this->connection->createQueryBuilder();
        $queryBuilder->from('tl_version');
        $queryBuilder->andWhere($queryBuilder->expr()->eq('tl_version.pid', ':pid'));
        $queryBuilder->setParameter(':pid', $mixID);
        $queryBuilder->andWhere($queryBuilder->expr()->eq('tl_version.version', ':version'));
        $queryBuilder->setParameter(':version', $mixVersion);
        $queryBuilder->andWhere($queryBuilder->expr()->eq('tl_version.fromTable', ':fromTable'));
        $queryBuilder->setParameter(':fromTable', $this->source);

        $statement = $queryBuilder->execute();
        if (0 === $statement->rowCount()) {
            return null;
        }

        $version = $statement->fetch(\PDO::FETCH_OBJ);

        $data = StringUtil::deserialize($version->data);

        if (!\is_array($data) || (0 === \count($data))) {
            return null;
        }

        $model = $this->getEmptyModel();
        $model->setID($mixID);
        foreach ($data as $key => $value) {
            if ($key === $this->idProperty) {
                continue;
            }

            $model->setProperty($key, $value);
        }

        return $model;
    }

    /**
     * Return a list with all versions for the row with the given Id.
     *
     * @param mixed   $mixID      The ID of the row.
     * @param boolean $onlyActive If true, only active versions will get returned, if false all version will get
     *                            returned.
     *
     * @return CollectionInterface
     */
    public function getVersions($mixID, $onlyActive = false)
    {
        $queryBuilder = $this->connection->createQueryBuilder();
        $queryBuilder->select(['tstamp', 'version', 'username', 'active']);
        $queryBuilder->from('tl_version');
        $queryBuilder->andWhere($queryBuilder->expr()->eq('tl_version.fromTable', ':fromTable'));
        $queryBuilder->setParameter(':fromTable', $this->source);
        $queryBuilder->andWhere($queryBuilder->expr()->eq('tl_version.pid', ':pid'));
        $queryBuilder->setParameter(':pid', $mixID);

        if ($onlyActive) {
            $queryBuilder->andWhere($queryBuilder->expr()->eq('tl_version.active', ':active'));
            $queryBuilder->setParameter(':active', '1');
        } else {
            $queryBuilder->orderBy('tl_version.version', 'DESC');
        }

        $statement = $queryBuilder->execute();
        if (0 === $statement->rowCount()) {
            return null;
        }

        $versions = $statement->fetchAll(\PDO::FETCH_ASSOC);

        $collection = $this->getEmptyCollection();

        foreach ($versions as $versionValue) {
            $model = $this->getEmptyModel();
            $model->setId($mixID);

            foreach ($versionValue as $key => $value) {
                if ($key === $this->idProperty) {
                    continue;
                }

                $model->setProperty($key, $value);
            }

            $collection->push($model);
        }

        return $collection;
    }

    /**
     * Save a new version of a row.
     *
     * @param ModelInterface $model    The model for which a new version shall be created.
     * @param string         $username The username to attach to the version as creator.
     *
     * @return void
     */
    public function saveVersion(ModelInterface $model, $username)
    {
        $queryBuilder = $this->connection->createQueryBuilder();
        $queryBuilder->select('COUNT(*) AS count');
        $queryBuilder->from('tl_version');
        $queryBuilder->andWhere($queryBuilder->expr()->eq('tl_version.pid', ':pid'));
        $queryBuilder->setParameter(':pid', $model->getId());
        $queryBuilder->andWhere($queryBuilder->expr()->eq('tl_version.fromTable', ':fromTable'));
        $queryBuilder->setParameter(':fromTable', $this->source);

        $statement = $queryBuilder->execute();
        $count     = $statement->fetch(\PDO::FETCH_COLUMN);

        $mixNewVersion = ((int) $count + 1);
        $mixData       = $model->getPropertiesAsArray();

        $mixData[$this->idProperty] = $model->getId();

        $insert = [
            'tl_version.pid'       => $model->getId(),
            'tl_version.tstamp'    => \time(),
            'tl_version.version'   => $mixNewVersion,
            'tl_version.fromTable' => $this->source,
            'tl_version.username'  => $username,
            'tl_version.data'      => \serialize($mixData)
        ];

        $this->connection->insert('tl_version', $insert);

        $this->setVersionActive($model->getId(), $mixNewVersion);
    }

    /**
     * Set a version as active.
     *
     * @param mixed $mixID      The ID of the row.
     * @param mixed $mixVersion The version number to set active.
     *
     * @return void
     */
    public function setVersionActive($mixID, $mixVersion)
    {
        $updateValues = ['tl_version.pid' => $mixID, 'tl_version.fromTable' => $this->source];

        // Set version inactive.
        $this->connection->update('tl_version', ['tl_version.active' => ''], $updateValues);

        // Set version active.
        $updateValues['version'] = $mixVersion;
        $this->connection->update('tl_version', ['tl_version.active' => 1], $updateValues);
    }

    /**
     * Retrieve the current active version for a row.
     *
     * @param mixed $mixID The ID of the row.
     *
     * @return mixed The current version number of the requested row.
     */
    public function getActiveVersion($mixID)
    {
        $queryBuilder = $this->connection->createQueryBuilder();
        $queryBuilder->select('version');
        $queryBuilder->from('tl_version');
        $queryBuilder->andWhere($queryBuilder->expr()->eq('tl_version.pid', ':pid'));
        $queryBuilder->setParameter(':pid', $mixID);
        $queryBuilder->andWhere($queryBuilder->expr()->eq('tl_version.fromTable', ':fromTable'));
        $queryBuilder->setParameter(':fromTable', $this->source);
        $queryBuilder->andWhere($queryBuilder->expr()->eq('tl_version.active', ':active'));
        $queryBuilder->setParameter(':active', 1);

        $statement = $queryBuilder->execute();
        if (0 === $statement->rowCount()) {
            return null;
        }

        return $statement->fetch(\PDO::FETCH_OBJ)->version;
    }

    /**
     * Check if two models have the same values in all properties.
     *
     * @param ModelInterface $firstModel  The first model to compare.
     * @param ModelInterface $secondModel The second model to compare.
     *
     * @return boolean True - If both models are same, false if not.
     */
    public function sameModels($firstModel, $secondModel)
    {
        foreach ($firstModel as $key => $value) {
            if ($key === $this->idProperty) {
                continue;
            }

            if (\is_array($value)) {
                if (!\is_array($secondModel->getProperty($key))) {
                    return false;
                }

                if (\serialize($value) !== \serialize($secondModel->getProperty($key))) {
                    return false;
                }
            } elseif ($value !== $secondModel->getProperty($key)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Store an undo entry in the table tl_undo.
     *
     * Currently this only supports delete queries.
     *
     * @param string $sourceSQL The SQL used to perform the action to be undone.
     * @param string $saveSQL   The SQL query to retrieve the current entries.
     * @param string $table     The table to be affected by the action.
     *
     * @return void
     */
    protected function insertUndo($sourceSQL, $saveSQL, $table)
    {
        // Load row.
        $statement = $this->connection->query($saveSQL);

        // Check if we have a result.
        if (0 === $statement->rowCount()) {
            return;
        }

        $result = $statement->fetch(\PDO::FETCH_ASSOC);

        // Save information in array.
        $parameters = [];
        foreach ($result as $value) {
            $parameters[$table][] = $value;
        }

        $prefix = '<span style="color:#b3b3b3; padding-right:3px;">(DC General)</span>';
        $user   = BackendUser::getInstance();

        // Write into undo.
        $this->connection->insert(
            'tl_undo',
            [
                'tl_undo.pid'          => $user->id,
                'tl_undo.tstamp'       => \time(),
                'tl_undo.fromTable'    => $table,
                'tl_undo.query'        => $prefix . $sourceSQL,
                'tl_undo.affectedRows' => \count($parameters[$table]),
                'tl_undo.data'         => \serialize($parameters)
            ]
        );
    }

    /**
     * Get the default connection for the database.
     *
     * @return Connection
     */
    protected function getDefaultConnection()
    {
        return System::getContainer()->get('database_connection');
    }

    /**
     * This is a fallback for get the connection instead of the old database.
     *
     * @param array $config The configuration.
     *
     * @return void
     *
     * @deprecated This method is deprecated sinde 2.1 and where removed in 3.0. The old database never used in future.
     */
    private function fallbackFromDatabaseToConnection(array &$config)
    {
        if (isset($config['database'])) {
            // @codingStandardsIgnoreStart
            @\trigger_error(
                'Config key database is deprecated use instead connection. Fallback will be dropped.',
                E_USER_DEPRECATED
            );
            // @codingStandardsIgnoreEnd

            if (!isset($config['connection'])) {
                $config['connection'] = $config['database'];
            }

            unset($config['database']);
        }

        if (isset($config['connection']) && $config['connection'] instanceof Database) {
            // @codingStandardsIgnoreStart
            @\trigger_error(
                '"' . __METHOD__ . '" now accepts doctrine instances - ' .
                'passing Contao database instances is deprecated.',
                E_USER_DEPRECATED
            );
            // @codingStandardsIgnoreEnd
            $reflection = new \ReflectionProperty(Database::class, 'resConnection');
            $reflection->setAccessible(true);

            $config['connection'] = $reflection->getValue($config['connection']);
        }
    }
}
