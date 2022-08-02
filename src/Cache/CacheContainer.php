<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2022 Contao Community Alliance.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    contao-community-alliance/dc-general
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2013-2022 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

declare(strict_types=1);

namespace ContaoCommunityAlliance\DcGeneral\Cache;

use Psr\Cache\CacheItemInterface;

class CacheContainer implements CacheItemInterface
{
    /**
     * The data to be cached.
     *
     * @var mixed|null
     */
    protected mixed $data = null;

    /**
     * The key of the cache.
     *
     * @var string
     */
    protected string $key = '';

    /**
     *
     * @param string $key
     * @param mixed  $data
     */
    public function __construct(string $key, mixed $data)
    {
        if (empty($key)) {
            throw new \RuntimeException('An empty key is not allowed for the cache.');
        }

        $this->key  = $key;
        $this->data = $data;
    }

    /**
     * @inheritDoc
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @inheritDoc
     */
    public function get()
    {
        return $this->data;
    }

    /**
     * @inheritDoc
     */
    public function isHit()
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function set(mixed $value)
    {
        $this->data = $value;
    }

    /**
     * @inheritDoc
     */
    public function expiresAt(?\DateTimeInterface $expiration)
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function expiresAfter(\DateInterval|int|null $time)
    {
        return null;
    }
}
