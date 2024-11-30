<?php

/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2023 Contao Community Alliance.
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
 * @copyright  2013-2023 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Widget;

use Contao\Config;
use Contao\CoreBundle\Exception\ResponseException;
use Contao\CoreBundle\Image\ImageFactoryInterface;
use Contao\CoreBundle\Picker\PickerBuilderInterface;
use Contao\DataContainer;
use Contao\Environment;
use Contao\File;
use Contao\FileSelector;
use Contao\FilesModel;
use Contao\Image;
use Contao\Image\ResizeConfiguration;
use Contao\Model\Collection;
use Contao\StringUtil;
use Contao\System;
use ContaoCommunityAlliance\DcGeneral\Contao\Compatibility\DcCompat;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\ContaoBackendViewTemplate;
use ContaoCommunityAlliance\DcGeneral\Data\ModelInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\ContainerInterface;
use ContaoCommunityAlliance\DcGeneral\InputProviderInterface;
use ContaoCommunityAlliance\Translator\TranslatorInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * File tree widget being compatible with the dc general.
 *
 * @property string      $strField    The field name.
 * @property bool        $mandatory   If true the field is required.
 * @property bool        $multiple    If true multiple values are allowed.
 * @property bool        $isGallery   If true the image gallery is rendered.
 * @property bool        $isDownloads If true only allowed download files are listed.
 * @property string      $fieldType   Either 'radio' or 'checkbox'.
 * @property bool|null   $files       Flag if files shall be shown.
 * @property bool|null   $filesOnly   Flag if only files shall be selectable.
 * @property string|null $path        The root path.
 * @property string|null $extensions  The list of valid file extensions.
 *
 * @see https://github.com/contao/core/blob/master/system/modules/core/widgets/FileTree.php
 *
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @psalm-suppress PropertyNotSetInConstructor
 *
 * @deprecated Deprecated since Contao 4.13 as the used FilePicker has been deprecated. Use the picker instead.
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
     * @var string
     */
    protected $orderField = '';

    /**
     * The order field value.
     *
     * @var null|list<string>
     */
    protected $orderFieldValue = null;

    /**
     * The default width of the thumbnail.
     *
     * @var int
     */
    protected $thumbnailHeight = 50;

    /**
     * The default height of the thumbnail.
     *
     * @var int
     */
    protected $thumbnailWidth = 75;

    /**
     * The default placeholder image.
     *
     * @var string
     */
    protected $placeholderImage = 'placeholder.png';

    /**
     * The extensions where is allowed for download.
     *
     * @var array
     */
    private $allowedDownload = [];

    /**
     * The root directory.
     *
     * @var string
     */
    private $projectDir;

    /**
     * Create a new instance.
     *
     * @param array|null    $attributes    The custom attributes.
     * @param DcCompat|null $dataContainer The data container.
     */
    public function __construct($attributes = null, DcCompat $dataContainer = null)
    {
        parent::__construct($attributes, $dataContainer);

        $this->allowedDownload =
            ($attributes['allowedDownload'] ?? StringUtil::trimsplit(',', \strtolower(Config::get('allowedDownload'))));

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
     * @return bool|int|null|object|string The property value
     *
     * @psalm-suppress ImplementedReturnTypeMismatch - Widget incorrectly annotates that it only returns string.
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
     * @return bool True if the property exists
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
                return (bool) parent::__get($strKey);
        }
    }

    /**
     * Set up the file tree widget.
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

        $projectDir = System::getContainer()->getParameter('kernel.project_dir');
        assert(\is_string($projectDir));
        $this->projectDir = $projectDir;

        if (!$this->orderField) {
            return;
        }

        assert($this->dataContainer instanceof DcCompat);
        $model = $this->dataContainer->getModel();
        assert($model instanceof ModelInterface);

        $value = $model->getProperty($this->orderField);

        // support serialized values.
        if (!\is_array($value)) {
            $value = StringUtil::deserialize($value, true);
            assert(\is_array($value));
        }
        $value = \array_values($value);
        /** @var list<string> $value */

        $this->orderId         = $this->orderField . \str_replace($this->strField, '', (string) $this->strId);
        $this->orderFieldValue = \array_values(\array_filter($value));
    }

    /**
     * Process the validation.
     *
     * @param mixed $varInput The input value.
     *
     * @return array|string
     */
    protected function validator($varInput)
    {
        if ('' === $varInput) {
            if ($this->mandatory) {
                $translator = $this->getEnvironment()->getTranslator();
                assert($translator instanceof TranslatorInterface);

                $this->addError(
                    $translator->translate('mandatory', 'ERR', [$this->strLabel])
                );
            }

            return '';
        }

        $varInput = \array_map('\Contao\StringUtil::uuidToBin', \array_filter(\explode(',', $varInput)));

        return $this->multiple ? $varInput : $varInput[0];
    }

    /**
     * Render the file list.
     *
     * @param array           $icons         The generated icons.
     * @param Collection|null $collection    The file's collection.
     *
     * @param bool            $followSubDirs If true sub-folders get rendered.
     *
     * @return void
     */
    private function renderList(array &$icons, Collection $collection = null, bool $followSubDirs = false)
    {
        if (!$collection) {
            return;
        }

        foreach ($collection->getModels() as $model) {
            assert($model instanceof FilesModel);
            $uuid = $model->uuid;
            assert(is_string($uuid));
            // File system and database seem not in sync
            if (!\file_exists($this->projectDir . '/' . $model->path)) {
                continue;
            }

            if (('folder' === $model->type) && $followSubDirs) {
                $files = FilesModel::findByPid($uuid);
                if (null === $files) {
                    continue;
                }
                assert($files instanceof Collection);
                $this->renderList($icons, $files);
                continue;
            }

            if (false !== ($icon = $this->renderIcon($model, $this->isGallery, $this->isDownloads))) {
                $icons[\md5($uuid)] = ['uuid' => $uuid, 'image' => $icon];
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
        return \in_array($extension, $this->allowedDownload);
    }

    /**
     * Render the file info.
     *
     * @param File $file The file.
     *
     * @return string
     */
    protected function renderFileInfo(File $file)
    {
        return \sprintf(
            '%s <span class="tl_gray">(%s%s)</span>',
            $file->path,
            static::getReadableSize($file->size),
            (($file->isGdImage || $file->isSvgImage) ? ', ' . $file->width . 'x' . $file->height . ' px' : '')
        );
    }

    /**
     * Render the image of a file.
     *
     * @param FilesModel $model      The file model.
     * @param bool       $imagesOnly If true only images are rendered.
     * @param bool       $downloads  If true file extension has to be in the allowed downloads list.
     *
     * @return false|string
     */
    protected function renderIcon($model, $imagesOnly = false, $downloads = false)
    {
        if ('folder' === $model->type) {
            if ($imagesOnly || $downloads) {
                return false;
            }

            return Image::getHtml('folderC.svg') . ' ' . $model->path;
        }
        $file = new File($model->path);
        $info = $this->renderFileInfo($file);

        if ($imagesOnly && !$file->isImage) {
            return false;
        }

        if ($downloads) {
            if ($this->isAllowedDownload($file->extension)) {
                return Image::getHtml($file->icon) . ' ' . $info;
            }

            return false;
        }

        if (!$file->isImage) {
            return Image::getHtml($file->icon) . ' ' . $info;
        }

        return $this->generateGalleryImage($file, $info);
    }

    /**
     * Generate an image for use as gallery listing.
     *
     * @param File       $file  The image file being rendered.
     * @param string     $info  The image information.
     *
     * @return string
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    private function generateGalleryImage(File $file, $info)
    {
        if (
            $file->viewWidth && $file->viewHeight
            && ($file->isSvgImage
                || (($file->height <= Config::get('gdMaxImgHeight'))
                    && ($file->width <= Config::get('gdMaxImgWidth'))
                )
            )
        ) {
            // Inline the image if no preview image will be generated (see #636).
            if ($file->height <= $this->thumbnailHeight && $file->width <= $this->thumbnailWidth) {
                $image = $file->dataUri;
            } else {
                $projectDir = System::getContainer()->getParameter('kernel.project_dir');
                assert(\is_string($projectDir));

                $imageFactory = System::getContainer()->get('contao.image.image_factory');
                assert($imageFactory instanceof ImageFactoryInterface);

                $image = $imageFactory->create(
                    $projectDir . '/' . $file->path,
                    [$this->thumbnailWidth, $this->thumbnailHeight, ResizeConfiguration::MODE_BOX]
                )->getUrl($projectDir);
            }
        } else {
            $image = $this->placeholderImage;
        }

        if (strncmp($image, 'data:', 5) === 0) {
            return '<img src="' . $file->dataUri . '" width="' . $file->width . '" height="' . $file->height
                   . '" alt="" class="gimage removable" title="' . StringUtil::specialchars($info) . '">';
        }

        return Image::getHtml($image, '', 'class="gimage removable" title="' . StringUtil::specialchars($info) . '"');
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
        if (('' !== $this->orderField) && \is_array($this->orderFieldValue)) {
            $ordered = [];

            foreach ($this->orderFieldValue as $uuid) {
                $iconKey = \md5($uuid);
                if (isset($icons[$iconKey])) {
                    $ordered[$iconKey] = $icons[$iconKey];
                    unset($icons[$iconKey]);
                }
            }

            foreach ($icons as $uuid => $icon) {
                $ordered[\md5($uuid)] = $icon;
            }

            $icons = $ordered;
        }

        return $icons;
    }

    /**
     * Generate the adjust selection link.
     *
     * @return string
     */
    private function generateLink()
    {
        $extras = ['fieldType' => $this->fieldType];

        if ($this->files) {
            $extras['files'] = (bool) $this->files;
        }

        if ($this->filesOnly) {
            $extras['filesOnly'] = (bool) $this->filesOnly;
        }

        if (\is_string($this->path)) {
            $extras['path'] = $this->path;
        }

        if (\is_string($this->extensions)) {
            $extras['extensions'] = $this->extensions;
        }

        $pickerBuilder = System::getContainer()->get('contao.picker.builder');
        assert($pickerBuilder instanceof PickerBuilderInterface);

        return $pickerBuilder->getUrl('file', $extras);
    }

    /**
     * {@inheritdoc}
     */
    public function generate()
    {
        $values = [];
        $icons  = [];

        if (!empty($this->varValue)) {
            $files = FilesModel::findMultipleByUuids((array) $this->varValue);

            if (null !== $files) {
                assert($files instanceof Collection);
                $this->renderList($icons, $files, $this->isGallery || $this->isDownloads);
            }

            $icons = $this->applySorting($icons);

            // Files can be null.
            if (null !== $files) {
                foreach ($files as $model) {
                    $values[] = StringUtil::binToUuid($model->uuid);
                }
            }
        }

        $translator = $this->getEnvironment()->getTranslator();
        assert($translator instanceof TranslatorInterface);

        $content = (new ContaoBackendViewTemplate($this->subTemplate))
            ->setTranslator($translator)
            ->set('name', $this->strName)
            ->set('id', $this->strId)
            ->set('value', \implode(',', $values))
            ->set('hasOrder', $this->orderField !== '' && \is_array($this->orderFieldValue))
            ->set('icons', $icons)
            ->set('isGallery', $this->isGallery)
            ->set('orderId', $this->orderId)
            ->set('link', $this->generateLink())
            ->set('label', $this->label)
            ->set('readonly', $this->readonly)
            ->parse();

        return !Environment::get('isAjaxRequest') ? '<div>' . $content . '</div>' : $content;
    }

    /**
     * Update the value via ajax and redraw the widget.
     *
     * @param string        $ajaxAction    Not used in here.
     * @param DataContainer $dataContainer The data container to use.
     *
     * @return string
     *
     * @throws ResponseException Throws a response exception.
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     * @SuppressWarnings(PHPMD.ExitExpression)
     */
    public function updateAjax($ajaxAction, DataContainer $dataContainer)
    {
        if ('loadFiletree' !== $ajaxAction) {
            return '';
        }

        assert($dataContainer instanceof DcCompat);
        $this->dataContainer = $dataContainer;
        $this->setUp();

        $environment = $this->dataContainer->getEnvironment();

        $dataDefinition = $environment->getDataDefinition();
        assert($dataDefinition instanceof ContainerInterface);

        $inputProvider = $environment->getInputProvider();
        assert($inputProvider instanceof InputProviderInterface);

        $propertyName = $inputProvider->getValue('name');
        $information  = (array) $GLOBALS['TL_DCA'][$dataContainer->getName()]['fields'][$propertyName];

        // Merge with the information from the data container.
        $information['eval'] = \array_merge(
            $dataDefinition->getPropertiesDefinition()->getProperty($propertyName)->getExtra(),
            (array) $information['eval']
        );

        $combat = new DcCompat($environment, null, $propertyName);

        /** @var class-string<FileSelector> $widgetClass */
        /** @psalm-suppress DeprecatedClass - we know we are deprecated ourselves. :D */
        $widgetClass = $GLOBALS['BE_FFL']['fileSelector'];

        /** @psalm-suppress UnsafeInstantiation - no better way to instantiate :( */
        $widget = new $widgetClass(
            $widgetClass::getAttributesFromDca(
                $information,
                $combat->field,
                null,
                $propertyName,
                $dataDefinition->getName(),
                $combat
            )
        );

        // Load a particular node
        if ('' !== $inputProvider->getValue('folder', true)) {
            $content = $widget->generateAjax(
                $inputProvider->getValue('folder', true),
                $inputProvider->getValue('field'),
                (int) $inputProvider->getValue('level')
            );
        } else {
            $content = $widget->generate();
        }

        throw new ResponseException(new Response($content));
    }
}
