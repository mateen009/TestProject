<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Custom\AdvanceExchange\Controller\Adminhtml\Advancedexchange;

use Magento\Framework\Exception\LocalizedException;

class Save extends \Magento\Backend\App\Action
{

    protected $dataPersistor;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\App\Request\DataPersistorInterface $dataPersistor
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\App\Request\DataPersistorInterface $dataPersistor
    ) {
        $this->dataPersistor = $dataPersistor;
        parent::__construct($context);
    }

    /**
     * Save action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $data = $this->getRequest()->getPostValue();
        
        if ($data) {
            $id = $this->getRequest()->getParam('advanced_exchange_id');
        
            $model = $this->_objectManager->create(\Custom\AdvanceExchange\Model\AdvancedExchange::class)->load($id);
            if (!$model->getId() && $id) {
                $this->messageManager->addErrorMessage(__('This Record no longer exists.'));
                return $resultRedirect->setPath('*/*/');
            }
        
            //echo "<pre>";print_r($data);die();
            $data = $this->_filterFileData($data);
           // echo "<pre>";print_r($data);die();
            $model->setData($data);
        
            try {
                $model->save();
                $this->messageManager->addSuccessMessage(__('You saved the Data.'));
                $this->dataPersistor->clear('custom_advanceexchange_advanced_exchange');
        
                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('*/*/edit', ['advanced_exchange_id' => $model->getId()]);
                }
                return $resultRedirect->setPath('*/*/');
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage($e, __('Something went wrong while saving.'));
            }
        
            $this->dataPersistor->set('custom_advanceexchange_advanced_exchange', $data);
            return $resultRedirect->setPath('*/*/edit', ['advanced_exchange_id' => $this->getRequest()->getParam('advanced_exchange_id')]);
        }
        return $resultRedirect->setPath('*/*/');
    }

    public function _filterFileData(array $rawData)
    {
        //Replace icon with fileuploader field name
        $data = $rawData;
        if (isset($data['attachedfile'][0]['name'])) {
            //$data['attachedfile'] = 'advanceExchange/t/e/'.$data['attachedfile'][0]['name'];
            $data['attachedfile'] = 'advanceExchange/'.$data['attachedfile'][0]['name'];
        } else {
            $data['attachedfile'] = null;
        }
        return $data;
    }
}

