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
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2013-2024 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Contao\Callback;

use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\Properties\PropertyInterface;
use ContaoCommunityAlliance\DcGeneral\Event\AbstractEnvironmentAwareEvent;
use InvalidArgumentException;
use Symfony\Contracts\EventDispatcher\Event;

use function method_exists;

/**
 * Class AbstractReturningPropertyCallbackListener.
 *
 * Abstract base class for a callback listener.
 *
 * @template TEvent of AbstractEnvironmentAwareEvent
 * @extends AbstractReturningCallbackListener<TEvent>
 *
 * @SuppressWarnings(PHPMD.LongClassName)
 */
abstract class AbstractReturningPropertyCallbackListener extends AbstractReturningCallbackListener
{
    /**
     * The name of the property to limit execution on.
     *
     * @var null|string
     */
    protected $propertyName;

    /**
     * Set the restrictions for this callback.
     *
     * @param null|string $dataContainerName The name of the data container to limit execution on.
     * @param null|string $propertyName      The name of the property to limit execution on.
     *
     * @return void
     */
    public function setRestrictions(?string $dataContainerName = null, ?string $propertyName = null)
    {
        parent::setRestrictions($dataContainerName);
        $this->propertyName = $propertyName;
    }

    /**
     * {@inheritDoc}
     */
    public function wantToExecute($event)
    {
        return parent::wantToExecute($event)
            && (null === $this->propertyName || ($this->propertyName === $this->getProperty($event)));
    }

    private function getProperty(Event $event): string
    {
        if (method_exists($event, 'getPropertyName')) {
            return $event->getPropertyName();
        }
        if (method_exists($event, 'getProperty')) {
            if ($event->getProperty() instanceof PropertyInterface) {
                return $event->getProperty()->getName();
            } else {
                return (string) $event->getProperty();
            }
        }

        throw new InvalidArgumentException('Neither Method getPropertyName() nor method getProperty() found');
    }
}
