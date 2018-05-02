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
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @copyright  2013-2018 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Contao;

use ContaoCommunityAlliance\DcGeneral\SessionStorageInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * {@inheritdoc}
 */
class SessionStorage implements SessionStorageInterface
{
    use RequestScopeDeterminatorAwareTrait;

    /**
     * The session key.
     *
     * @var string
     */
    private $key;

    /**
     * The symfony session.
     *
     * @var SessionInterface
     */
    private $session;

    /**
     * The database keys for store session data in the database.
     *
     * @var array
     */
    private $databaseKeys = [];

    /**
     * The attribute storage.
     *
     * @var array
     */
    private $attributes = [];

    /**
     * Create a new instance.
     *
     * @param RequestScopeDeterminator $scopeDeterminator The request scope determinator.
     * @param string                   $key               The key to use for storage.
     * @param SessionInterface         $session           The symfony session.
     * @param array                    $databaseKeys      The database keys for store session data in the database.
     */
    public function __construct(
        RequestScopeDeterminator $scopeDeterminator = null,
        $key = '',
        SessionInterface $session,
        array $databaseKeys = []
    ) {
        $this->key = (string) $key;

        $this->session           = $session;
        $this->scopeDeterminator = $scopeDeterminator;

        if (!\count($databaseKeys)) {
            return;
        }

        foreach ($databaseKeys as $index => $databaseKeyItems) {
            foreach ((array) $databaseKeyItems as $databaseKey) {
                if (('common' === $index)
                    || (0 === \strpos($index, 'DC_GENERAL_'))
                ) {
                    $this->databaseKeys[$index][] = $databaseKey;

                    continue;
                }

                $this->databaseKeys['DC_GENERAL_' . \strtoupper($index)][] = $databaseKey;
            }
        }
    }

    /**
     * Create new instance of his self.
     *
     * @param string $key The session key.
     *
     * @return SessionStorage
     */
    public function createInstance($key)
    {
        return new self($this->scopeDeterminator, $key, $this->session, $this->databaseKeys);
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

        return isset($this->attributes[$name]) ? $this->attributes[$name] : null;
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
    }

    /**
     * Load the data from the session if it has not been loaded yet.
     *
     * @return void
     */
    private function load()
    {
        if (\count($this->attributes)) {
            return;
        }

        $sessionBag         = $this->session->getBag($this->getSessionBagKey());
        $databaseSessionBag = $this->session->getBag($this->getDatabaseSessionBagKey());

        $this->attributes = \array_merge(
            (array) $sessionBag->get($this->key),
            (array) $databaseSessionBag->get($this->key)
        );
    }

    /**
     * Save the data to the session.
     *
     * @return void
     */
    private function persist()
    {
        $sessionBag = $this->session->getBag($this->getSessionBagKey());
        $sessionBag->set($this->key, $this->filterAttributes());

        $databaseSessionBag = $this->session->getBag($this->getDatabaseSessionBagKey());
        $databaseSessionBag->set($this->key, $this->filterAttributes(true));
    }

    /**
     * Filter the attributes.
     *
     * @param bool $determineDatabase Determine for filter database session attributes.
     *                                If is false, this filter non database attributes.
     *
     * @return array
     */
    private function filterAttributes($determineDatabase = false)
    {
        $databaseAttributes = \array_merge(
            (array) $this->databaseKeys['common'],
            (array) $this->databaseKeys[$this->key]
        );

        if ($determineDatabase) {
            return \array_intersect_key($this->attributes, \array_flip($databaseAttributes));
        }

        return \array_diff_key($this->attributes, \array_flip($databaseAttributes));
    }

    /**
     * Gets the correct session bag key depending on the environment.
     *
     * @return string
     */
    private function getSessionBagKey()
    {
        return 'cca_dc_general';
    }

    /**
     * Get the session bag key for database session.
     *
     * @return string
     */
    private function getDatabaseSessionBagKey()
    {
        return 'contao_backend';
    }
}
