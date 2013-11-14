<?php
/**
 * PHP version 5
 * @package    generalDriver
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Tristan Lins <tristan.lins@bit3.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

namespace DcGeneral\Contao\Callbacks;

use DcGeneral\DC_General;
use DcGeneral\Exception\DcGeneralRuntimeException;

class ContainerOnLoadCallbackListener extends AbstractStaticCallbackListener
{
	function __construct($callback, DC_General $dcGeneral)
	{
		parent::__construct($callback, $dcGeneral);
	}
}
