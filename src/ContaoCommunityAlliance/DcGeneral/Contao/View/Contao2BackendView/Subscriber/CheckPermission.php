<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2016 Contao Community Alliance.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    contao-community-alliance/dc-general
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2013-2016 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Subscriber;

use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Condition\Property\BooleanCondition;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Condition\Property\PropertyConditionChain;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\PropertyInterface;
use ContaoCommunityAlliance\DcGeneral\Factory\Event\BuildDataDefinitionEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * The check permission subscriber.
 */
class CheckPermission implements EventSubscriberInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            BuildDataDefinitionEvent::NAME => 'checkPermissionForProperties'
        );
    }

    /**
     * Check permission for properties by user alexf.
     *
     * @param BuildDataDefinitionEvent $event The event.
     *
     * @return void
     */
    public function checkPermissionForProperties(BuildDataDefinitionEvent $event)
    {
        $container          = $event->getContainer();
        $properties         = $container->getPropertiesDefinition();
        $palettesDefinition = $container->getPalettesDefinition();
        $palettes           = $palettesDefinition->getPalettes();

        foreach ($palettes as $palette) {
            foreach ($palette->getProperties() as $property) {
                if (!$properties->hasProperty($name = $property->getName())) {
                    trigger_error('Warning: unknown property in palette: ' . $name, E_USER_DEPRECATED);
                    continue;
                }

                $chain = $this->getVisibilityConditionChain($property);

                $chain->addCondition(new BooleanCondition(!$properties->getProperty($name)->isExcluded()));
            }
        }
    }

    /**
     * Retrieve the visibility condition chain or create an empty one if none exists.
     *
     * @param PropertyInterface $property The property.
     *
     * @return PropertyConditionChain
     */
    private function getVisibilityConditionChain($property)
    {
        $chain = $property->getVisibleCondition();
        if ($chain
            && ($chain instanceof PropertyConditionChain)
            && $chain->getConjunction() === PropertyConditionChain::AND_CONJUNCTION
        ) {
            return $chain;
        }

        $chain = new PropertyConditionChain($chain ? [$chain] : []);
        $property->setVisibleCondition($chain);

        return $chain;
    }
}
