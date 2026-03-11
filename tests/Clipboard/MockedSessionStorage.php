<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2019 Contao Community Alliance.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    contao-community-alliance/dc-general
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2013-2019 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Test\Clipboard;

use ContaoCommunityAlliance\DcGeneral\SessionStorageInterface;

/**
 * This class simply mocks a session storage.
 */
final class MockedSessionStorage implements SessionStorageInterface
{
    /**
     * The values.
     *
     * @var array
     */
    private array $values = [];

    public function has($name): bool
    {
        return isset($this->values[$name]);
    }

    public function get($name): mixed
    {
        return $this->values[$name] ?? null;
    }

    public function set($name, $value): self
    {
        $this->values[$name] = $value;

        return $this;
    }

    public function all(): array
    {
        return $this->values;
    }

    public function replace(array $attributes): self
    {
        foreach ($attributes as $name => $value) {
            $this->values[$name] = $value;
        }

        return $this;
    }

    public function remove($name): self
    {
        unset($this->values[$name]);

        return $this;
    }

    public function clear(): self
    {
        $this->values = [];

        return $this;
    }
}
