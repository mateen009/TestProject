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

namespace Magezon\EmailBuilder\Block\Adminhtml\Template;


class Preview extends \Magento\Email\Block\Adminhtml\Template\Preview
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
     * Preview constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Filter\Input\MaliciousCode $maliciousCode
     * @param \Magento\Email\Model\TemplateFactory $emailFactory
     * @param \Magezon\Builder\Helper\Data $builderHelper
     * @param \Magezon\EmailBuilder\Helper\Data $helperData
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Filter\Input\MaliciousCode $maliciousCode,
        \Magento\Email\Model\TemplateFactory $emailFactory,
        \Magezon\Builder\Helper\Data $builderHelper,
        \Magezon\EmailBuilder\Helper\Data $helperData,
        \Magento\Framework\View\Asset\Repository $assetRepository,
        \Magento\Framework\Filesystem\Driver\File $processFile,
        \Magento\Framework\Filesystem\DirectoryList $directoryList,
        array $data = []
    ) {
        $this->builderHelper = $builderHelper;
        $this->helperData = $helperData;
        $this->assetRepository = $assetRepository;
        $this->processFile = $processFile;
        $this->directoryList = $directoryList;
        parent::__construct($context, $maliciousCode, $emailFactory, $data);
    }

    protected function _toHtml()
    {
        if ($this->helperData->isModuleEnable()) {
            $request = $this->getRequest();

            $storeId = $this->getAnyStoreView()->getId();
            /** @var $template \Magento\Email\Model\Template */
            $template = $this->_emailFactory->create();

            if ($id = (int)$request->getParam('id')) {
                $template->load($id);
            } else {
                $content = $request->getParam('text');
                if ($this->isJson($content)) {
                    $content = $this->builderHelper->prepareProfileBlock(
                        \Magezon\SimpleBuilder\Block\Profile::class,
                        $content
                    )->setTemplate('Magezon_EmailBuilder::profile.phtml')->toHtml();

                    $css = $this->getStyles($content);
                    $this->addCssToFile('css/mgz-inline.css', $css);
                }
                $template->setTemplateType($request->getParam('type'));
                $template->setTemplateText($content);
                $template->setTemplateStyles($request->getParam('styles'));
                $template->setData('is_legacy', false);
            }

            \Magento\Framework\Profiler::start($this->profilerName);

            $template->emulateDesign($storeId);
            $templateProcessed = $this->_appState->emulateAreaCode(
                \Magento\Email\Model\AbstractTemplate::DEFAULT_DESIGN_AREA,
                [$template, 'getProcessedTemplate']
            );
            $template->revertDesign();
            $templateProcessed = $this->_maliciousCode->filter($templateProcessed);

            if ($template->isPlain()) {
                $templateProcessed = "<pre>" . $this->escapeHtml($templateProcessed) . "</pre>";
            }

            \Magento\Framework\Profiler::stop($this->profilerName);

            return $templateProcessed;
        } else {
            return parent::_toHtml();
        }
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
}
