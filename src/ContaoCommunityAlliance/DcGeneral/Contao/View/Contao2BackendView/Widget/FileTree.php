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
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2013-2018 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Widget;

use Contao\DataContainer;
use Contao\RequestToken;
use Contao\StringUtil;
use ContaoCommunityAlliance\DcGeneral\Contao\Compatibility\DcCompat;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\ContaoBackendViewTemplate;
use Model\Collection;

/**
 * File tree widget being compatible with the dc general.
 *
 * @property string strField    The field name.
 * @property bool   mandatory   If true the field is required.
 * @property bool   multiple    If true multiple values are allowed.
 * @property bool   isGallery   If true the a image gallery is rendered.
 * @property bool   isDownloads If true only allowed download files are listed.
 *
 * @see https://github.com/contao/core/blob/master/system/modules/core/widgets/FileTree.php
 */
class FileTree extends AbstractWidget
{
    /**
     * The widget sub template.
     *
     * @var string
     */
    private $subTemplate = 'widget_filetree';

    /**
     *  Css ID of the order field.
     *
     * @var string
     */
    protected $orderId;

    /**
     * The order field attribute name.
     *
     * @var string.
     */
    protected $orderField;

    /**
     * The order field value.
     *
     * @var array.
     */
    protected $orderFieldValue;

    /**
     * The default width of the thumbnail.
     *
     * @var int
     */
    protected $thumbnailHeight = 80;

    /**
     * The default height of the thumbnail.
     *
     * @var int
     */
    protected $thumbnailWidth = 60;

    /**
     * The default placeholder image.
     *
     * @var string
     */
    protected $placeholderImage = 'placeholder.png';

    /**
     * Create a new instance.
     *
     * @param array|null    $attributes    The custom attributes.
     * @param DcCompat|null $dataContainer The data container.
     */
    public function __construct($attributes = null, DcCompat $dataContainer = null)
    {
        parent::__construct($attributes, $dataContainer);

        $this->setUp();
    }

    /**
     * Set an object property
     *
     * @param string $strKey   The property name.
     * @param mixed  $varValue The property value.
     *
     * @return void
     */
    public function __set($strKey, $varValue)
    {
        switch ($strKey) {
            case 'subTemplate':
                $this->subTemplate = $varValue;
                break;

            case 'thumbnailHeight':
                $this->thumbnailHeight = $varValue;
                break;

            case 'thumbnailWidth':
                $this->thumbnailWidth = $varValue;
                break;

            case 'placeholderImage':
                $this->placeholderImage = $varValue;
                break;

            default:
                parent::__set($strKey, $varValue);
                break;
        }
    }

    /**
     * Return an object property
     *
     * @param string $strKey The property name.
     *
     * @return string The property value
     */
    public function __get($strKey)
    {
        switch ($strKey) {
            case 'subTemplate':
                return $this->subTemplate;

            case 'thumbnailHeight':
                return $this->thumbnailHeight;

            case 'thumbnailWidth':
                return $this->thumbnailWidth;

            case 'placeholderImage':
                return $this->placeholderImage;

            default:
        }

        return parent::__get($strKey);
    }

    /**
     * Check whether an object property exists
     *
     * @param string $strKey The property name.
     *
     * @return boolean True if the property exists
     */
    public function __isset($strKey)
    {
        switch ($strKey) {
            case 'subTemplate':
                return isset($this->subTemplate);

            case 'thumbnailHeight':
                return isset($this->thumbnailHeight);

            case 'thumbnailWidth':
                return isset($this->thumbnailWidth);

            case 'placeholderImage':
                return isset($this->placeholderImage);

            default:
                return parent::__get($strKey);
        }
    }

    /**
     * Setup the file tree widget.
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    private function setUp()
    {
        // Load the fonts for the drag hint (see #4838)
        $GLOBALS['TL_CONFIG']['loadGoogleFonts'] = true;

        if (!$this->dataContainer || !$this->orderField) {
            return;
        }

        $value = $this->dataContainer->getModel()->getProperty($this->orderField);

        // support serialized values.
        if (!is_array($value)) {
            $value = deserialize($value, true);
        }

        $this->orderId         = $this->orderField . str_replace($this->strField, '', $this->strId);
        $this->orderFieldValue = (!empty($value) && is_array($value)) ? array_filter($value) : array();
    }

    /**
     * Process the validation.
     *
     * @param mixed $inputValue The input value.
     *
     * @return array|string
     */
    protected function validator($inputValue)
    {
        $translator = $this->getEnvironment()->getTranslator();

        if ($inputValue == '') {
            if ($this->mandatory) {
                $this->addError($translator->translate('mandatory', 'ERR', array($this->strLabel)));
            }

            return '';
        }

        $inputValue = array_map('\Contao\StringUtil::uuidToBin', array_filter(explode(',', $inputValue)));

        return $this->multiple ? $inputValue : $inputValue[0];
    }

    /**
     * Render the file list.
     *
     * @param array           $icons         The generated icons.
     * @param Collection|null $collection    The files collection.
     * @param bool            $followSubDirs If true subfolders get rendered.
     *
     * @return void
     */
    private function renderList(array &$icons, Collection $collection = null, $followSubDirs = false)
    {
        if (!$collection) {
            return;
        }

        foreach ($collection->getModels() as $model) {
            // File system and database seem not in sync
            if (!file_exists(TL_ROOT . '/' . $model->path)) {
                continue;
            }

            if (('folder' === $model->type) && $followSubDirs) {
                $this->renderList($icons, \FilesModel::findByPid($model->uuid));
                continue;
            }
            if (false !== ($icon = $this->renderIcon($model, $this->isGallery, $this->isDownloads))) {
                $icons[] = ['uuid' => $model->uuid, 'image' => $icon];
            }
        }
    }

    /**
     * Check if an extension is in the allowed downloads.
     *
     * @param string $extension The file extension.
     *
     * @return bool
     */
    protected function isAllowedDownload($extension)
    {
        static $allowedDownload;

        if ($allowedDownload === null) {
            $allowedDownload = trimsplit(',', strtolower(\Config::get('allowedDownload')));
        }

        return in_array($extension, $allowedDownload);
    }

    /**
     * Render the file info.
     *
     * @param \File $file The file.
     *
     * @return string
     */
    protected function renderFileInfo($file)
    {
        return sprintf(
            '%s <span class="tl_gray">(%s%s)</span>',
            $file->path,
            static::getReadableSize($file->size),
            (($file->isGdImage || $file->isSvgImage) ? ', ' . $file->width . 'x' . $file->height . ' px' : '')
        );
    }

    /**
     * Render the image of a file.
     *
     * @param \FilesModel $model      The file model.
     * @param bool        $imagesOnly If true only images are rendered.
     * @param bool        $downloads  If true file extension has to be in the allowed downloads list.
     *
     * @return false|string
     */
    protected function renderIcon($model, $imagesOnly = false, $downloads = false)
    {
        if ($model->type === 'folder') {
            if ($imagesOnly || $downloads) {
                return false;
            }

            return \Image::getHtml('folderC.gif') . ' ' . $model->path;
        }
        $file = new \File($model->path, true);
        $info = $this->renderFileInfo($file);

        if ($imagesOnly && !$file->isImage) {
            return false;
        }

        if ($downloads) {
            if ($this->isAllowedDownload($file->extension)) {
                return \Image::getHtml($file->icon) . ' ' . $info;
            }

            return false;
        }

        if (!$file->isImage) {
            return \Image::getHtml($file->icon) . ' ' . $info;
        }

        return $this->generateGalleryImage($model, $file, $info);
    }

    /**
     * Generate a image for use as gallery listing.
     *
     * @param \FilesModel $model The file model in use.
     * @param \File       $file  The image file being rendered.
     * @param string      $info  The image information.
     *
     * @return string
     */
    private function generateGalleryImage($model, $file, $info)
    {
        $image = $this->placeholderImage;

        if ($file->isSvgImage
            || $file->height <= \Config::get('gdMaxImgHeight') && $file->width <= \Config::get('gdMaxImgWidth')
        ) {
            $width  = min($file->width, $this->thumbnailWidth);
            $height = min($file->height, $this->thumbnailHeight);
            $image  = \Image::get($model->path, $width, $height, 'center_center');
        }

        return \Image::getHtml($image, '', 'class="gimage" title="' . specialchars($info) . '"');
    }

    /**
     * Apply the sorting to the icons.
     *
     * @param array $icons The file icons.
     *
     * @return array
     */
    private function applySorting($icons)
    {
        if ($this->orderField != '' && is_array($this->orderFieldValue)) {
            $ordered = array();

            foreach ($this->orderFieldValue as $uuid) {
                if (isset($icons[$uuid])) {
                    $ordered[$uuid] = $icons[$uuid];
                    unset($icons[$uuid]);
                }
            }

            foreach ($icons as $uuid => $icon) {
                $ordered[$uuid] = $icon;
            }

            $icons = $ordered;
        }

        return $icons;
    }

    /**
     * Generate the adjust selection link.
     *
     * @param array $values The selected files (string uuids).
     *
     * @return string
     */
    private function generateLink($values)
    {
        $inputProvider = $this->getEnvironment()->getInputProvider();

        $modelId = $inputProvider->getParameter('id');
        if (!$modelId) {
            // Use the highest id for mysql.
            $modelId = $this->dataContainer->getModel()->getProviderName(). '::4294967295';
        }

        return sprintf(
            '%s?do=%s&amp;field=%s&amp;act=show&amp;id=%s&amp;value=%s&amp;rt=%s',
            'system/modules/dc-general/backend/generalfile.php',
            $inputProvider->getParameter('do'),
            $this->strField,
            $modelId,
            implode(',', $values),
            RequestToken::get()
        );
    }

    /**
     * {@inheritdoc}
     */
    public function generate()
    {
        $values = array();
        $icons  = array();

        if (!empty($this->varValue)) {
            $files = \FilesModel::findMultipleByUuids((array) $this->varValue);
            $this->renderList($icons, $files, $this->isGallery || $this->isDownloads);
            $icons = $this->applySorting($icons);

            // Files can be null.
            if (null !== $files) {
                foreach ($files as $model) {
                    $values[] = StringUtil::binToUuid($model->uuid);
                }
            }
        }

        $template = new ContaoBackendViewTemplate($this->subTemplate);
        $buffer   = $template
            ->setTranslator($this->getEnvironment()->getTranslator())
            ->set('name', $this->strName)
            ->set('id', $this->strId)
            ->set('value', implode(',', $values))
            ->set('hasOrder', $this->orderField != '' && is_array($this->orderFieldValue))
            ->set('icons', $icons)
            ->set('isGallery', $this->isGallery)
            ->set('orderId', $this->orderId)
            ->set('link', $this->generateLink($values))
            ->parse();

        if (!\Environment::get('isAjaxRequest')) {
            $buffer = '<div>' . $buffer . '</div>';
        }

        return $buffer;
    }

    /**
     * Update the value via ajax and redraw the widget.
     *
     * @param string        $ajaxAction    Not used in here.
     * @param DataContainer $dataContainer The data container to use.
     *
     * @return string
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     * @SuppressWarnings(PHPMD.ExitExpression)
     */
    public function updateAjax($ajaxAction, DataContainer $dataContainer)
    {
        if ($ajaxAction !== 'loadFiletree') {
            return '';
        }

        $this->dataContainer = $dataContainer;
        $this->setUp();

        $environment   = $this->dataContainer->getEnvironment();
        $inputProvider = $environment->getInputProvider();
        $propertyName  = $inputProvider->getValue('name');

        $combat = new DcCompat($environment, null, $propertyName);

        /** @var \FileSelector $widgetClass */
        $widgetClass = $GLOBALS['BE_FFL']['fileSelector'];

        /** @var \FileSelector $widget */
        $widget = new $widgetClass(
            $widgetClass::getAttributesFromDca(
                $GLOBALS['TL_DCA'][$environment->getDataDefinition()->getName()]['fields'][$propertyName],
                $combat->field,
                null,
                $propertyName,
                $environment->getDataDefinition()->getName(),
                $combat
            )
        );

        // Load a particular node
        if ('' !== $inputProvider->getValue('folder', true)) {
            $buffer = $widget->generateAjax(
                $inputProvider->getValue('folder', true),
                $inputProvider->getValue('field'),
                (int) $inputProvider->getValue('level')
            );
        } else {
            $buffer = $widget->generate();
        }

        echo $buffer;
        exit;
    }
}
