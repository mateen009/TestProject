<?php

namespace AscentDigital\SalesForce\Block;

use AscentDigital\SalesForce\Model\SalesForceFactory;
use Magento\Framework\App\ResourceConnection;


class Data extends \Magento\Framework\View\Element\Template
{

    protected $salesForceFactory;
    private $resourceConnection;
    protected $request;

    /**
     * Constructor
     *
     * @param \Magento\Framework\View\Element\Template\Context  $context
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        SalesForceFactory $salesForceFactory,
        ResourceConnection $resourceConnection,
        \Magento\Framework\App\Action\Context $request,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->salesForceFactory = $salesForceFactory;
        $this->resourceConnection = $resourceConnection;
        $this->request = $request;
    }

    public function getByTrackingNo($trackingNo)
    {
        // $quoteData = $this->salesForceFactory->create()->load($id);
        $collection = $this->salesForceFactory->create()->getCollection();
        $quoteData = $collection->addFieldToFilter('tracking_no', $trackingNo)->addFieldToFilter('status', ['neq' => 'converted'])->getFirstItem();
        return $quoteData;
    }

    public function getAllSalesForceQuotes()
    {
        $params = $this->request->getRequest()->getParams();
        $status = '';
        $collection = $this->salesForceFactory->create()->getCollection()->setOrder('id', 'DESC');
        if (isset($params['status'])) {
            if ($params['status'] == 'converted') {
                $collection->addFieldToFilter('status', 'converted');
                $status = 'converted';
            } elseif ($params['status'] == 'notconverted') {
                $collection->addFieldToFilter('status', ['neq' => 'converted']);
                $status = 'notconverted';
            }
        }
        return [
            'quoteCollection' => $collection,
            'quoteStatus' => $status
        ];
    }
}
