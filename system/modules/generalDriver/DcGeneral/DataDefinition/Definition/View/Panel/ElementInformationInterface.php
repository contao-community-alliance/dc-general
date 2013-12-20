<?php

namespace DcGeneral\DataDefinition\Definition\View\Panel;

/**
 * Interface ElementInformationInterface.
 *
 * This interface describes a generic panel element information.
 *
 * @package DcGeneral\DataDefinition\Definition\View\Panel
 */
interface ElementInformationInterface
{
	/**
	 * The name of the element.
	 *
	 * @return string
	 */
	public function getName();
}
