<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2024 Contao Community Alliance.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    contao-community-alliance/dc-general
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Tristan Lins <tristan.lins@bit3.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2013-2024 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Contao;

use ContaoCommunityAlliance\DcGeneral\SessionStorageInterface;
use Symfony\Component\HttpFoundation\Session\Attribute\AttributeBagInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * {@inheritdoc}
 */
class SessionStorage implements SessionStorageInterface
{
    /**
     * The session scope.
     *
     * @var null|string
     */
    private ?string $scope = null;

    /**
     * The symfony session.
     *
     * @var SessionInterface
     */
    private SessionInterface $session;

    /**
     * The database keys for store session data in the database.
     *
     * @var array
     */
    private array $databaseKeys = [];

    /**
     * The attribute storage.
     *
     * @var array
     */
    private array $attributes = [];

    /**
     * Create a new instance.
     *
     * @param SessionInterface $session      The symfony session.
     * @param array            $databaseKeys The database keys for store session data in the database.
     */
    public function __construct(
        SessionInterface $session,
        array $databaseKeys = []
    ) {
        $this->session = $session;

        if (!\count($databaseKeys)) {
            return;
        }

        foreach ($databaseKeys as $index => $databaseKeyItems) {
            foreach ((array) $databaseKeyItems as $databaseKey) {
                if (('common' === $index) || (\str_starts_with($index, 'DC_GENERAL_'))) {
                    $this->databaseKeys[$index][] = $databaseKey;

                    continue;
                }

                $this->databaseKeys['DC_GENERAL_' . \strtoupper($index)][] = $databaseKey;
            }
        }
    }

    /**
     * Set the scope name of this session storage.
     *
     * @param string $scope The session scope name.
     *
     * @return void
     */
    public function setScope($scope)
    {
        if (null !== $this->scope) {
            // @codingStandardsIgnoreStart
            @\trigger_error('The scope can not be change! Use a new session storage.', E_USER_ERROR);
            // @codingStandardsIgnoreEnd
        }

        $this->scope = $scope;
    }

    /**
     * {@inheritdoc}
     */
    public function has($name)
    {
        $this->load();

        return isset($this->attributes[$name]);
    }

    /**
     * {@inheritdoc}
     */
    public function get($name)
    {
        $this->load();

        return ($this->attributes[$name] ?? null);
    }

    /**
     * {@inheritdoc}
     */
    public function set($name, $value)
    {
        $this->load();
        $this->attributes[$name] = $value;
        $this->persist();

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function all()
    {
        $this->load();

        return $this->attributes;
    }

    /**
     * {@inheritdoc}
     */
    public function replace(array $attributes)
    {
        $this->load();
        $this->attributes = \array_merge($this->attributes, $attributes);
        $this->persist();

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function remove($name)
    {
        $this->load();
        unset($this->attributes[$name]);
        $this->persist();

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function clear()
    {
        $this->load();
        $this->attributes = [];
        $this->persist();

        return $this;
    }

    /**
     * Load the data from the session if it has not been loaded yet.
     *
     * @return void
     */
    private function load(): void
    {
        if (\count($this->attributes)) {
            return;
        }

        $sessionBag         = $this->session->getBag($this->getSessionBagKey());
        $databaseSessionBag = $this->session->getBag($this->getDatabaseSessionBagKey());
        assert($sessionBag instanceof AttributeBagInterface);
        assert($databaseSessionBag instanceof AttributeBagInterface);

        if (null === $scope = $this->getScope()) {
            return;
        }
        $this->attributes = \array_merge((array) $sessionBag->get($scope), (array) $databaseSessionBag->get($scope));
    }

    /**
     * Save the data to the session.
     *
     * @return void
     */
    private function persist(): void
    {
        if (null === $scope = $this->getScope()) {
            return;
        }

        $sessionBag = $this->session->getBag($this->getSessionBagKey());
        assert($sessionBag instanceof AttributeBagInterface);
        $sessionBag->set($scope, $this->filterAttributes());

        $databaseSessionBag = $this->session->getBag($this->getDatabaseSessionBagKey());
        assert($databaseSessionBag instanceof AttributeBagInterface);
        $databaseSessionBag->set($scope, $this->filterAttributes(true));
    }

    /**
     * Filter the attributes.
     *
     * @param bool $determineDatabase Determine for filter database session attributes.
     *                                If is false, this filter non database attributes.
     *
     * @return array
     */
    private function filterAttributes(bool $determineDatabase = false): array
    {
        $databaseAttributes = $this->databaseKeys['common'] ?? [];
        if (null !== ($scope = $this->getScope())) {
            $databaseAttributes = \array_merge($databaseAttributes, $this->databaseKeys[$scope] ?? []);
        }

        if ($determineDatabase) {
            return \array_intersect_key($this->attributes, \array_flip($databaseAttributes));
        }

        return \array_diff_key($this->attributes, \array_flip($databaseAttributes));
    }

    /**
     * Get the scope for this session storage.
     *
     * @return string|null
     */
    private function getScope(): ?string
    {
        if (null === $this->scope) {
            // @codingStandardsIgnoreStart
            @\trigger_error('The scope for this session storage is not defined!', E_USER_ERROR);
            // @codingStandardsIgnoreEnd

            return null;
        }

        return $this->scope;
    }

    /**
     * Gets the correct session bag key depending on the environment.
     *
     * @return string
     */
    private function getSessionBagKey(): string
    {
        return 'cca_dc_general';
    }

    /**
     * Get the session bag key for database session.
     *
     * @return string
     */
    private function getDatabaseSessionBagKey(): string
    {
        return 'contao_backend';
    }
}
