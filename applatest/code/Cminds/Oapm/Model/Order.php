<?php
namespace Cminds\Oapm\Model;

class Order extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Status available values.
     */
    const STATUS_ACTIVE = 1;
    const STATUS_NOT_ACTIVE = 0;
    const STATUS_FINALIZED = 2;
    const STATUS_CANCELED = 3;

    /**
     * Event names.
     */
    const EVENT_STATUS_UPDATE_CANCELED = 'cminds_oapm_order_update_status_canceled';

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $dateTime;

    /**
     * @var \Magento\Framework\Math\Random
     */
    protected $random;

    /**
     * @var \Cminds\Oapm\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * Prefix of model events names.
     *
     * @var string
     */
    protected $_eventPrefix = 'cminds_oapm_order';

    private $sendNotification = false;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $dateTime
     * @param \Magento\Framework\Math\Random $random
     * @param \Cminds\Oapm\Helper\Data $helper
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
        \Magento\Framework\Math\Random $random,
        \Cminds\Oapm\Helper\Data $helper,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->dateTime = $dateTime;
        $this->random = $random;
        $this->helper = $helper;
        $this->orderRepository = $orderRepository;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Cminds\Oapm\Model\ResourceModel\Order::class);
    }

    /**
     * Processing object before save data.
     *
     * @return \Cminds\Oapm\Model\Order
     */
    public function beforeSave()
    {
        parent::beforeSave();

        if ($this->isObjectNew()) {
            $this->setHash($this->getHash())
                ->setStatus(self::STATUS_ACTIVE)
                ->setCreatedAt($this->dateTime->gmtDate());
        } elseif ($this->hasDataChanges()) {
            $this->setUpdatedAt($this->dateTime->gmtDate());
        }

        return $this;
    }

    /**
     * Load order by order id.
     *
     * @param   int $orderId
     * @return  \Cminds\Oapm\Model\ResourceModel\Order
     */
    public function loadByOrderId($orderId)
    {
        $this->_getResource()->load($this, $orderId, 'order_id');

        return $this;
    }

    /**
     * Load order by hash.
     *
     * @param   string $hash
     * @return  \Cminds\Oapm\Model\Order
     */
    public function loadByHash($hash)
    {
        $this->_getResource()->load($this, $hash, 'hash');

        return $this;
    }

    /**
     * Get array of objects transferred to default events processing.
     *
     * @return array
     */
    public function getEventData()
    {
        return $this->_getEventData();
    }

    /**
     * Retrieve hash.
     *
     * @return string
     */
    protected function getHash()
    {
        return $this->random->getUniqueHash();
    }

    public function notifyCustomer($notify = true)
    {
        $this->sendNotification = $notify;

        return $this;
    }

    public function getStatuses()
    {
        return array(
            self::STATUS_ACTIVE => __('Active'),
            self::STATUS_NOT_ACTIVE => __('Not Active'),
            self::STATUS_FINALIZED => __('Finalized'),
            self::STATUS_CANCELED => __('Canceled'),
        );
    }
}
