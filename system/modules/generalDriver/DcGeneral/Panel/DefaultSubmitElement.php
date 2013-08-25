<?php
/**
 * PHP version 5
 * @package    generalDriver
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

namespace DcGeneral\Panel;

use DcGeneral\Data\ConfigInterface;
use DcGeneral\Data\ModelInterface;
use DcGeneral\Panel\AbstractElement;
use DcGeneral\Panel\PanelElementInterface;
use DcGeneral\Panel\FilterElementInterface;
use DcGeneral\View\ViewTemplateInterface;

class DefaultSubmitElement extends AbstractElement implements SubmitElementInterface
{
	/**
	 * {@inheritdoc}
	 */
	public function initialize(ConfigInterface $objConfig, PanelElementInterface $objElement = null)
	{
	}

	/**
	 * {@inheritdoc}
	 */
	public function render(ViewTemplateInterface $objTemplate)
	{
	}
}
