<?php
/**
 * Magezon
 *
 * This source file is subject to the Magezon Software License, which is available at https://magezon.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to https://www.magezon.com for more information.
 *
 * @category  Magezon
 * @package   Magezon_EmailBuilder
 * @copyright Copyright (C) 2020 Magezon (https://magezon.com)
 */

namespace Magezon\EmailBuilder\Model;

class Template extends \Magento\Email\Model\Template
{
    /**
     * @var \Magezon\Builder\Helper\Data
     */
    protected $builderHelper;

    /**
     * @var \Magezon\EmailBuilder\Helper\Data
     */
    protected $helperData;

    /**
     * @var \Magezon\Core\Plugin\View\Result\Layout
     */
    protected $layout;

    /**
     * @var \Magento\Framework\View\Asset\Repository
     */
    protected $assetRepository;

    /**
     * @var \Magento\Framework\Filesystem\Driver\File
     */
    private $processFile;

    /**
     * @var \Magento\Framework\Filesystem\DirectoryList
     */
    protected $directoryList;

    /**
     * @var \Magento\Framework\App\ProductMetadataInterface
     */
    protected $productMetadata;

    /**
     * Template constructor.
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\View\DesignInterface $design
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Store\Model\App\Emulation $appEmulation
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\View\Asset\Repository $assetRepo
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Email\Model\Template\Config $emailConfig
     * @param \Magento\Email\Model\TemplateFactory $templateFactory
     * @param \Magento\Framework\Filter\FilterManager $filterManager
     * @param \Magento\Framework\UrlInterface $urlModel
     * @param \Magento\Email\Model\Template\FilterFactory $filterFactory
     * @param \Magezon\Builder\Helper\Data $builderHelper
     * @param \Magezon\EmailBuilder\Helper\Data $helperData
     * @param \Magezon\Core\Plugin\View\Result\Layout $layout
     * @param \Magento\Framework\View\Asset\Repository $assetRepository
     * @param \Magento\Framework\Filesystem\Driver\File $processFile
     * @param \Magento\Framework\Filesystem\DirectoryList $directoryList
     * @param \Magento\Framework\App\ProductMetadataInterface $productMetadata
     * @param array $data
     * @param \Magento\Framework\Serialize\Serializer\Json|null $serializer
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\View\DesignInterface $design,
        \Magento\Framework\Registry $registry,
        \Magento\Store\Model\App\Emulation $appEmulation,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\View\Asset\Repository $assetRepo,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Email\Model\Template\Config $emailConfig,
        \Magento\Email\Model\TemplateFactory $templateFactory,
        \Magento\Framework\Filter\FilterManager $filterManager,
        \Magento\Framework\UrlInterface $urlModel,
        \Magento\Email\Model\Template\FilterFactory $filterFactory,
        \Magezon\Builder\Helper\Data $builderHelper,
        \Magezon\EmailBuilder\Helper\Data $helperData,
        \Magezon\Core\Plugin\View\Result\Layout $layout,
        \Magento\Framework\View\Asset\Repository $assetRepository,
        \Magento\Framework\Filesystem\Driver\File $processFile,
        \Magento\Framework\Filesystem\DirectoryList $directoryList,
        \Magento\Framework\App\ProductMetadataInterface $productMetadata,
        array $data = [],
        \Magento\Framework\Serialize\Serializer\Json $serializer = null
    ) {
        parent::__construct(
            $context,
            $design,
            $registry,
            $appEmulation,
            $storeManager,
            $assetRepo,
            $filesystem,
            $scopeConfig,
            $emailConfig,
            $templateFactory,
            $filterManager,
            $urlModel,
            $filterFactory,
            $data,
            $serializer
        );
        $this->builderHelper = $builderHelper;
        $this->helperData = $helperData;
        $this->layout = $layout;
        $this->assetRepository = $assetRepository;
        $this->processFile = $processFile;
        $this->directoryList = $directoryList;
        $this->productMetadata = $productMetadata;
    }

    /**
     * Set is_legacy for older versions
     *
     * @throws \Magento\Framework\Exception\MailException
     * @return \Magento\Email\Model\Template
     */
    public function beforeSave()
    {
        //Current magento version
        $version = $this->productMetadata->getVersion();
        if (version_compare($version, '2.3.5-p2') <= 0) {
            $this->setData('is_legacy', 1);
        }
        parent::beforeSave();
        return $this;
    }

    /**
     * Process email content with Builder
     *
     * @param array $variables
     * @return string
     * @throws \Magento\Framework\Exception\MailException
     */
    public function getProcessedTemplate(array $variables = [])
    {
        $isEnable = $this->helperData->isModuleEnable();
        if ($isEnable && is_numeric($this->getId())) {
            $processor = $this->getTemplateFilter()
                ->setPlainTemplateMode($this->isPlain())
                ->setIsChildTemplate($this->isChildTemplate())
                ->setTemplateProcessor([$this, 'getTemplateContent']);

            $variables['this'] = $this;

            $isDesignApplied = $this->applyDesignConfig();

            // Set design params so that CSS will be loaded from the proper theme
            $processor->setDesignParams($this->getDesignParams());

            if (isset($variables['subscriber'])) {
                $storeId = $variables['subscriber']->getStoreId();
            } else {
                $storeId = $this->getDesignConfig()->getStore();
            }
            $processor->setStoreId($storeId);

            // Populate the variables array with store, store info, logo, etc. variables
            $variables = $this->addEmailVariables($variables, $storeId);
            $processor->setVariables($variables);

            //Current magento version
            $version = $this->productMetadata->getVersion();

            if (version_compare($version, '2.3.4') >= 0) {
                $previousStrictMode = $processor->setStrictMode(
                    !$this->getData('is_legacy') && is_numeric($this->getTemplateId())
                );
            }

            try {
                $templateText = $this->getTemplateText();
                if ($this->isJson($templateText)) {
                    $replaces = [
                        '{{template config_path="vendor_design/email/header_template"}}',
                        '{{template config_path="vendor_design/email/footer_template"}}',
                        '<p>{{template config_path=\"vendor_design/email/header_template\"}}</p>',
                        '<p>{{template config_path=\"vendor_design/email/footer_template\"}}</p>',
                        '{{template config_path=\"vendor_design/email/header_template\"}}\n\n',
                        '\n\n{{template config_path=\"vendor_design/email/footer_template\"}}'
                    ];

                    $template = str_replace($replaces, '', $templateText);
                    $content = $this->builderHelper->prepareProfileBlock(
                        \Magezon\SimpleBuilder\Block\Profile::class,
                        $template
                    )->setTemplate('Magezon_EmailBuilder::profile.phtml')->toHtml();

                    $css = $this->getStyles($content);
                    $this->addCssToFile('css/mgz-inline.css', $css);

                    $result = $processor->filter($content);
                } else {
                    $result = $processor->filter($templateText);
                }
            } catch (\Exception $e) {
                $this->cancelDesignConfig();
                throw new \LogicException(__($e->getMessage()), $e->getCode(), $e);
            } finally {
                if (version_compare($version, '2.3.4') >= 0) {
                    $processor->setStrictMode($previousStrictMode);
                }
            }

            if ($isDesignApplied) {
                $this->cancelDesignConfig();
            }
            return $result;
        } else {
            return parent::getProcessedTemplate($variables);
        }
    }

    /**
     * Get Css from Element Settings
     *
     * @param $html
     * @return mixed
     */
    protected function getStyles($html)
    {
        $regex  = '@(?:<style class="mgz-style">)(.*)</style>@msU';
        preg_match_all($regex, $html, $matches);
        $stylesHtml = '';
        if ($matches[0]) {
            $styles = '';
            foreach ($matches[0] as $_style) {
                $styles .= str_replace(['<style class="mgz-style">', '</style>'], [], $_style);
            }
            $stylesHtml .= $this->minifyCss($styles);
        }

        return $stylesHtml;
    }

    /**
     * @param $fileName
     * @param string $area
     * @return string
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getAssetFile($fileName, $area = 'frontend')
    {
        $fileId = 'Magezon_EmailBuilder::'.$fileName;
        $params = [
            'area' => $area
        ];
        $asset = $this->assetRepository->createAsset($fileId, $params);
        $staticView = $this->directoryList->getPath(\Magento\Framework\App\Filesystem\DirectoryList::STATIC_VIEW);
        $path = $staticView. '/' .$asset->getPath();

        return $path;
    }

    /**
     * @param $file
     * @param $css
     * @return bool
     */
    public function addCssToFile($file, $css)
    {
        try {
            $this->processFile->filePutContents($this->getAssetFile($file), $css, 0777);
        } catch (\Exception $e) {
            return false;
        }

        return true;
    }

    /**
     * @param $file
     * @return bool
     */
    public function deleteFileCreated($file)
    {
        try {
            $this->processFile->deleteFile($this->getAssetFile($file));
        } catch (\Exception $e) {
            return false;
        }

        return true;
    }

    /**
     * https://gist.github.com/webgefrickel/3339063
     * This function takes a css-string and compresses it, removing
     * unneccessary whitespace, colons, removing unneccessary px/em
     * declarations etc.
     *
     * @param string $css
     * @return string compressed css content
     * @author Steffen Becker
     */
    private function minifyCss($css)
    {
        // some of the following functions to minimize the css-output are directly taken
        // from the awesome CSS JS Booster: https://github.com/Schepp/CSS-JS-Booster
        // all credits to Christian Schaefer: http://twitter.com/derSchepp
        // remove comments
        $css = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $css);
        // backup values within single or double quotes
        preg_match_all('/(\'[^\']*?\'|"[^"]*?")/ims', $css, $hit, PREG_PATTERN_ORDER);
        $countHit = count($hit[1]);
        for ($i=0; $i < $countHit; $i++) {
            $css = str_replace($hit[1][$i], '##########' . $i . '##########', $css);
        }
        // remove traling semicolon of selector's last property
        $css = preg_replace('/;[\s\r\n\t]*?}[\s\r\n\t]*/ims', "}\r\n", $css);
        // remove any whitespace between semicolon and property-name
        $css = preg_replace('/;[\s\r\n\t]*?([\r\n]?[^\s\r\n\t])/ims', ';$1', $css);
        // remove any whitespace surrounding property-colon
        $css = preg_replace('/[\s\r\n\t]*:[\s\r\n\t]*?([^\s\r\n\t])/ims', ':$1', $css);
        // remove any whitespace surrounding selector-comma
        $css = preg_replace('/[\s\r\n\t]*,[\s\r\n\t]*?([^\s\r\n\t])/ims', ',$1', $css);
        // remove any whitespace surrounding opening parenthesis
        $css = preg_replace('/[\s\r\n\t]*{[\s\r\n\t]*?([^\s\r\n\t])/ims', '{$1', $css);
        // remove any whitespace between numbers and units
        $css = preg_replace('/([\d\.]+)[\s\r\n\t]+(px|em|pt|%)/ims', '$1$2', $css);
        // shorten zero-values
        $css = preg_replace('/([^\d\.]0)(px|em|pt|%)/ims', '$1', $css);
        // constrain multiple whitespaces
        $css = preg_replace('/\p{Zs}+/ims', ' ', $css);
        // remove newlines
        $css = str_replace(["\r\n", "\r", "\n"], '', $css);
        // Restore backupped values within single or double quotes
        for ($i=0; $i < $countHit; $i++) {
            $css = str_replace('##########' . $i . '##########', $hit[1][$i], $css);
        }
        return $css;
    }

    /**
     * Check string is Json
     *
     * @return boolean
     */
    private function isJson($string)
    {
        json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE);
    }
}
