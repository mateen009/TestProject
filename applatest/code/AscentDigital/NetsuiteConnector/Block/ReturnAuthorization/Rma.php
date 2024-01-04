<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace AscentDigital\NetsuiteConnector\Block\ReturnAuthorization;

class Rma extends \Magento\Framework\View\Element\Template
{

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Request\Http $request,
        array $data = []
    ) {
        $this->storeManager = $storeManager;
        $this->request = $request;
        parent::__construct($context, $data);
    }
    public function _prepareLayout()
    {
        $id =$this->request->getParam('ae_id');
        $breadcrumbsBlock = $this->getLayout()->getBlock('breadcrumbs');
        $baseUrl = $this->storeManager->getStore()->getBaseUrl();

        if ($breadcrumbsBlock) {

            $breadcrumbsBlock->addCrumb(
                'Advance Exchange',
                [
                'label' => __('Advance Exchange'), //lable on breadCrumbes
                'title' => __('Advance Exchange'),
                'link' => $baseUrl.('advanceexchange/manage/detail').'?ae_id='.$id
                ]
            );
            $breadcrumbsBlock->addCrumb(
                'Return Authorization',
                [
                'label' => __('Return Authorization'),
                'title' => __('Return Authorization'),
                'link' => '' //set link path
                ]
            );
        }
        // $this->pageConfig->getTitle()->set(__('FAQ')); // set page name
        return parent::_prepareLayout();
    }
}