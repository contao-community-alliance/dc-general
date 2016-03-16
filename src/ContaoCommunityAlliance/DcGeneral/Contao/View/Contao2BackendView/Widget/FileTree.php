<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2015 Contao Community Alliance.
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
 * @copyright  2013-2015 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Widget;

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
     * Create a new instance.
     *
     * @param array|null    $attributes    The custom attributes.
     *
     * @param DcCompat|null $dataContainer The data container.
     */
    public function __construct($attributes = null, DcCompat $dataContainer = null)
    {
        parent::__construct($attributes, $dataContainer);

        $this->setUp();
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

        // PHP 7 compatibility, see https://github.com/contao/core-bundle/issues/309
        if (version_compare(VERSION . '.' . BUILD, '3.5.5', '>=')) {
            $mapFunc = 'StringUtil::uuidToBin';
        } else {
            $mapFunc = 'StringUtil::uuidToBin';
        }

        $inputValue = array_map($mapFunc, array_filter(explode(',', $inputValue)));

        return $this->multiple ? $inputValue : $inputValue[0];
    }

    /**
     * Render the file list.
     *
     * @param array           $values        The selected values.
     * @param array           $icons         The generated icons.
     * @param Collection|null $collection    The files collection.
     * @param bool            $followSubDirs If true subfolders get rendered.
     *
     * @return void
     */
    private function renderList(array &$values, array &$icons, Collection $collection = null, $followSubDirs = false)
    {
        if (!$collection) {
            return;
        }

        foreach ($collection as $model) {
            // File system and database seem not in sync
            if (!file_exists(TL_ROOT . '/' . $model->path)) {
                continue;
            }

            $values[$model->id] = $model->uuid;

            if ($this->isGallery && !$this->isDownloads) {
                $icons[$model->uuid] = $this->renderIcon($model);
            } elseif ($model->type === 'folder' && $followSubDirs) {
                $subCollection = \FilesModel::findByPid($model->uuid);
                $this->renderList($values, $icons, $subCollection);
            } else {
                $icon = $this->renderIcon($model, $this->isGallery, $this->isDownloads);

                if ($icon !== false) {
                    $icons[$model->uuid] = $icon;
                }
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
            $this->getReadableSize($file->size),
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

        if ($imagesOnly && !($file->isGdImage || $file->isSvgImage)) {
            return false;
        }

        if ($downloads) {
            if ($this->isAllowedDownload($file->extension)) {
                return \Image::getHtml($file->icon) . ' ' . $info;
            }

            return false;
        }

        if (!$file->isGdImage && !$file->isSvgImage) {
            return \Image::getHtml($file->icon) . ' ' . $info;
        }

        $image = 'placeholder.png';

        if ($file->isSvgImage
            || $file->height <= \Config::get('gdMaxImgHeight') && $file->width <= \Config::get('gdMaxImgWidth')
        ) {
            $image = \Image::get($model->path, 80, 60, 'center_center');
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
     * @param array $values The selected files.
     *
     * @return string
     */
    private function generateLink($values)
    {
        $inputProvider = $this->getEnvironment()->getInputProvider();

        // Contao passed File ids sinc 3.3.4
        // @see https://github.com/contao/core/commit/c1472209fdfd6e2446013430753ed65530b5a1d1
        if (version_compare(VERSION . '.' . BUILD, '3.3.4', '>=')) {
            $values = array_keys($values);
        } else {
            $values = array_map('String::binToUuid', $values);
        }

        return sprintf(
            'contao/file.php?do=%s&amp;table=%s&amp;field=%s&amp;act=show&amp;id=%s&amp;value=%s&amp;rt=%s',
            $inputProvider->getParameter('do'),
            $this->getModel()->getProviderName(),
            $this->strField,
            $this->getModel()->getId(),
            implode(',', $values),
            \RequestToken::get()
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
            $this->renderList($values, $icons, $files, ($this->isGallery || $this->isDownloads));
            $icons = $this->applySorting($icons);
        }

        // PHP 7 compatibility, see https://github.com/contao/core-bundle/issues/309
        if (version_compare(VERSION . '.' . BUILD, '3.5.5', '>=')) {
            $mapFunc = 'StringUtil::binToUuid';
        } else {
            $mapFunc = 'String::binToUuid';
        }

        $template = new ContaoBackendViewTemplate($this->subTemplate);
        $buffer   = $template
            ->setTranslator($this->getEnvironment()->getTranslator())
            ->set('name', $this->strName)
            ->set('id', $this->strId)
            ->set('value', implode(',', array_map($mapFunc, $values)))
            ->set('hasOrder', ($this->orderField != '' && is_array($this->orderFieldValue)))
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
}
