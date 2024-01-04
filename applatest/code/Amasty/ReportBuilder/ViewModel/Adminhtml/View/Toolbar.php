<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\ViewModel\Adminhtml\View;

use Amasty\ReportBuilder\Api\ColumnInterface;
use Amasty\ReportBuilder\Block\Adminhtml\Widget\Form\Renderer\DefaultElement;
use Amasty\ReportBuilder\Model\EntityScheme\Provider;
use Amasty\ReportBuilder\Model\ReportResolver;
use Amasty\ReportBuilder\Model\Source\IntervalType;
use Amasty\ReportBuilder\Model\Source\RestrictedStores;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Data\Form\AbstractForm;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Store\Model\System\Store;

class Toolbar implements ArgumentInterface
{
    const DEFAULT_FROM = '-7 day';

    /**
     * @var FormFactory
     */
    private $formFactory;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var TimezoneInterface
     */
    private $timezone;

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @var ReportResolver
     */
    private $resolver;

    /**
     * @var IntervalType
     */
    private $intervalType;

    /**
     * @var RestrictedStores
     */
    private $restrictedStores;

    /**
     * @var Provider
     */
    private $provider;

    /**
     * @var DefaultElement
     */
    private $defaultElement;

    public function __construct(
        FormFactory $formFactory,
        RequestInterface $request,
        TimezoneInterface $timezone,
        UrlInterface $urlBuilder,
        ReportResolver $resolver,
        IntervalType $intervalType,
        RestrictedStores $restrictedStores,
        Provider $provider,
        DefaultElement $defaultElement
    ) {
        $this->formFactory = $formFactory;
        $this->request = $request;
        $this->timezone = $timezone;
        $this->urlBuilder = $urlBuilder;
        $this->resolver = $resolver;
        $this->intervalType = $intervalType;
        $this->restrictedStores = $restrictedStores;
        $this->provider = $provider;
        $this->defaultElement = $defaultElement;
    }

    public function getFormHtml(): string
    {
        $form = $this->formFactory->create([
            'data' => [
                'id' => 'amrepbuilder_charts_toolbar',
                'class' => 'amrepbuilder-charts-toolbar',
                'action' => '',
            ]
        ]);
        $form->setUseContainer(true);
        $form->setElementRenderer($this->defaultElement);
        $this->addFromToFilter($form);
        $this->addPeriodFilter($form);
        $this->addStoreFilter($form);

        return $form->toHtml();
    }

    private function addFromToFilter(AbstractForm $form): void
    {
        if ($this->hasReportDateFilterColumn()) {
            $dateFormat = 'y-MM-dd';
            $params = $this->request->getParam('report');

            $timeFrom = $this->timezone->date(strtotime(self::DEFAULT_FROM));
            $form->addField('from', 'date', [
                'label' => __('From'),
                'name' => 'from',
                'wrapper_class' => 'amrepbuilder-filter-from',
                'date_format' => $dateFormat,
                'format' => $dateFormat,
                'value' => isset($params['from']) ? $params['from'] : $timeFrom
            ]);

            $form->addField('to', 'date', [
                'label' => __('To'),
                'name' => 'to',
                'wrapper_class' => 'amrepbuilder-filter-to',
                'format' => $dateFormat,
                'date_format' => $dateFormat,
                'value' =>  isset($params['to']) ? $params['to'] : $this->timezone->date(time())
            ]);
        }
    }

    private function hasReportDateFilterColumn()
    {
        $report = $this->resolver->resolve();
        foreach ($report->getAllColumns() as $column) {
            if (isset($column[ColumnInterface::IS_DATE_FILTER]) && $column[ColumnInterface::IS_DATE_FILTER]) {
                return true;
            }
        }

        return false;
    }

    private function addPeriodFilter(AbstractForm $form): void
    {
        if ($this->resolver->resolve()->getUsePeriod()) {
            $form->addField('interval', 'radios', [
                'name' => 'interval',
                'wrapper_class' => 'amrepbuilder-filter-interval',
                'values' => $this->intervalType->toOptionArray(),
                'value' => 'day',
                'default' => 1
            ]);
        }
    }

    private function addStoreFilter(AbstractForm $form): void
    {
        $report = $this->resolver->resolve();
        $mainEntityName = $report->getMainEntity();

        $entityScheme = $this->provider->getEntityScheme()->getEntityByName($mainEntityName);
        if ($entityScheme->isStoreColumnExist()) {
            $form->addField('store', 'select', [
                'name' => 'store',
                'values' => $this->restrictedStores->getFormStoreValues($report->getStoreIds()),
                'class' => 'amrepbuilder-select',
                'wrapper_class' => 'amrepbuilder-select-store',
                'no_span' => true,
                'value' => 0
            ]);
        }
    }

    public function getReloadUrl(): string
    {
        return $this->urlBuilder->getUrl('amreportbuilder/report/viewReload');
    }

    public function getReportId(): int
    {
        return $this->resolver->resolve()->getReportId();
    }
}
