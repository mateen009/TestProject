<?php
namespace Cminds\Oapm\Block\Adminhtml\Sales\Order;

use Magento\Backend\Block\Template\Context as TemplateContext;
use Magento\Backend\Helper\Data as BackendHelper;
use Cminds\Oapm\Model\ResourceModel\Order\CollectionFactory as OrderCollectionFactory;
use Cminds\Oapm\Model\Order as OapmOrder;
use Magento\Sales\Model\ResourceModel\Order\Grid\CollectionFactory as OrderGridCollectionFactory;
use Magento\Sales\Model\Order\Config as OrderConfig;
use Magento\Framework\AuthorizationInterface;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    const ACL_SALES_ACTIONS_VIEW = 'Magento_Sales::actions_view';

    /**
     * @var OrderCollectionFactory
     */
    protected $orderCollectionFactory;

    /**
     * @var OapmOrder
     */
    protected $order;

    /**
     * @var OrderGridCollectionFactory
     */
    protected $salesOrderCollectionFactory;

    /**
     * @var OrderConfig
     */
    protected $orderConfig;

    /**
     * @var AuthorizationInterface
     */
    protected $authorization;

    /**
     * @param TemplateContext $context
     * @param BackendHelper $backendHelper
     * @param OrderCollectionFactory $orderCollectionFactory
     * @param OapmOrder $order,
     * @param OrderConfig $orderConfig,
     * @param array $data
     * @codeCoverageIgnore
     */
    public function __construct(
        TemplateContext $context,
        BackendHelper $backendHelper,
        OrderCollectionFactory $orderCollectionFactory,
        OapmOrder $order,
        OrderGridCollectionFactory $salesOrderCollectionFactory,
        OrderConfig $salesOrderConfig,
        AuthorizationInterface $authorization,
        array $data = []
    ) {
        $this->collectionFactory = $orderCollectionFactory;
        $this->order = $order;
        $this->salesOrderCollectionFactory = $salesOrderCollectionFactory;
        $this->salesOrderConfig = $salesOrderConfig;
        $this->authorization = $authorization;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('oapmGrid');
        $this->setDefaultSort('created_at');
        $this->setDefaultDir('desc');
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection()
    {
         /** @var \Magento\Sales\Model\ResourceModel\Order\Grid\Collection $collection */
        $collection = $this->salesOrderCollectionFactory->create();
        $collection->getSelect()->join(
            ['oapm' => $collection->getTable(\Cminds\Oapm\Setup\InstallSchema::TABLE_NAME)],
            'oapm.order_id = main_table.entity_id',
            [
                'oapm_status' => 'oapm.status',
                'order_status' => 'main_table.status'
            ]
        );
        $collection->addFilterToMap('oapm_status', 'oapm.status');
        $collection->addFilterToMap('order_status', 'main_table.status');

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * @return $this
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'real_order_id',
            [
                'header' => __('Order #'),
                'index' => 'increment_id',
                'type' => 'text',
                'width' => '80px',
            ]
        );

        if (! $this->_storeManager->isSingleStoreMode()) {
            $this->addColumn(
                'store_id',
                [
                    'header' => __('Purchased From (Store)'),
                    'index' => 'store_id',
                    'type' => 'store',
                    'store_view' => true,
                    'display_deleted' => true,
                ]
            );
        }

        $this->addColumn(
            'created_at',
            [
                'header' => __('Purchased On'),
                'index' => 'created_at',
                'type' => 'datetime',
                'width' => '100px',
            ]
        );

        $this->addColumn(
            'billing_name',
            [
                'header' => __('Creator Name'),
                'index' => 'billing_name',
                'sortable' => false,
            ]
        );

        $this->addColumn(
            'payer_name',
            [
                'header' => __('Payer Name'),
                'index' => 'payer_name',
                'renderer' => \Cminds\Oapm\Block\Adminhtml\Sales\Order\Grid\Column\Renderer\PayerName::class,
                'sortable' => false,
            ]
        );

        $this->addColumn(
            'base_grand_total',
            [
                'header' => __('G.T. (Base)'),
                'index' => 'base_grand_total',
                'type' => 'currency',
                'currency' => 'base_currency_code',
            ]
        );

        $this->addColumn(
            'grand_total',
            [
                'header' => __('G.T. (Purchased)'),
                'index' => 'grand_total',
                'type' => 'currency',
                'currency' => 'order_currency_code',
            ]
        );

        $this->addColumn(
            'order_status',
            [
                'header' => __('Status'),
                'index' => 'order_status',
                'type' => 'options',
                'options' => $this->salesOrderConfig->getStatuses(),
                'width' => '70px',
            ]
        );

        $this->addColumn(
            'oapm_status',
            [
                'header' => __('OAPM Status'),
                'index' => 'oapm_status',
                'type' => 'options',
                'options' => $this->order->getStatuses(),
                'width' => '70px',
            ]
        );

        if ($this->authorization->isAllowed(self::ACL_SALES_ACTIONS_VIEW)) {
            $this->addColumn(
                'action',
                [
                    'header' => __('Action'),
                    'width' => '50px',
                    'type' => 'action',
                    'getter' => 'getId',
                    'actions' => [
                        [
                            'caption' => __('View'),
                            'url' => ['base' => '*/order/view'],
                            'field' => 'order_id'
                        ]
                    ],
                    'filter' => false,
                    'sortable' => false,
                    'index' => 'stores',
                    'is_system' => true
                ]
            );
        }

        return parent::_prepareColumns();
    }

    /**
     * @param \Magento\Framework\DataObject $row
     * @return string
     * @codeCoverageIgnore
     */
    public function getRowUrl($row)
    {
        if ($this->authorization->isAllowed(self::ACL_SALES_ACTIONS_VIEW)) {
            return $this->getUrl('*/order/view', ['order_id' => $row->getId()]);
        }

        return false;
    }
}
