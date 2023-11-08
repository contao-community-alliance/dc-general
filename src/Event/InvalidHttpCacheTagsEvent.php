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
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2013-2020 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

declare(strict_types=1);

namespace ContaoCommunityAlliance\DcGeneral\Event;

/**
 * This event is for collected invalid http cache tags.
 */
class InvalidHttpCacheTagsEvent extends AbstractEnvironmentAwareEvent
{
    /**
     * The http cache namespace.
     *
     * @var string|null
     */
    private ?string $namespace = null;

    /**
     * The cache tags.
     *
     * @var array
     */
    private array $tags = [];

    /**
     * Get the http cache namespace.
     *
     * @return string
     */
    public function getNamespace(): string
    {
        if (null === $this->namespace) {
            throw new \RuntimeException('The namespace is not set.');
        }

        return $this->namespace;
    }

    /**
     * Set the http cache namespace.
     *
     * @param string $namespace The http cache namespace.
     *
     * @return InvalidHttpCacheTagsEvent
     *
     * @throws \RuntimeException Throws a exception, if will override the namespace.
     */
    public function setNamespace(string $namespace): InvalidHttpCacheTagsEvent
    {
        if (null !== $this->namespace) {
            throw new \RuntimeException('Overriding the namespace is forbidden.');
        }

        $this->namespace = $namespace;
        return $this;
    }

    /**
     * Get the tags.
     *
     * @return array
     */
    public function getTags(): array
    {
        return $this->tags;
    }

    /**
     * Set the tags.
     *
     * @param array $tags The cache tags.
     *
     * @return InvalidHttpCacheTagsEvent
     */
    public function setTags(array $tags): InvalidHttpCacheTagsEvent
    {
        $this->tags = $tags;
        return $this;
    }
}
