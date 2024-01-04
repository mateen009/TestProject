<?php
namespace Mobility\DemoReminder\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Sales\Api\OrderRepositoryInterface;

class Reminder extends Command
{
    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /** 
     * @var \Magento\Framework\App\State 
     */
    private $state;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    private $date;

    /**
     * @var \Mobility\DemoReminder\Helper\Data
     */
    private $helper;

    /**
     * @param \Mobility\DemoReminder\Helper\Data $helper
     */
    public function __construct(
        OrderRepositoryInterface $orderRepository,
        \Magento\Framework\App\State $state,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $date,
        \Mobility\DemoReminder\Helper\Data $helper
    ) {
        $this->orderRepository = $orderRepository;
        $this->state = $state;
        $this->date = $date;
        $this->helper = $helper;
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('mobility:demoReminder');
        $this->setDescription('Demo Reminder Cron');
       
        parent::configure();
    }
   
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->state->setAreaCode(\Magento\Framework\App\Area::AREA_GLOBAL);

        if (!$this->helper->isEnabled()) {
            return $this;
        }

        $orderCollection = $this->helper->getDemoOrdersCollection();
        $currentDate = strtotime($this->date->date()->format('Y-m-d'));
        if ($orderCollection->count()) {
            foreach ($orderCollection as $order) {
                echo "Order: " . $order->getId() . "\n";
                foreach ($order->getAllVisibleItems() as $item) {
                    $options = $item->getProductOptionByCode('info_buyRequest');
                    if (isset($options['additional_options']) && !empty($options['additional_options'])) {
                        // print_r($options['additional_options']);
                        $updateDate = $options['additional_options']['rental_to_utc'];
                        $datediff = $updateDate - $currentDate;
                        $noOfDays = round($datediff / (60 * 60 * 24));
                        if ($this->helper->getReminderDaysBefore() == $noOfDays) {
                            $this->helper->sendCustomerEmail($order->getCustomerName(), $order->getCustomerEmail(), $order->getIncrementId(), $expireDate);
                            // $this->helper->sendSalesRepEmail($order->getCustomerName(), $order->getCustomerEmail(), $order->getCustomerName(), $order->getIncrementId(), $expireDate);
                            $order->setData('demo_reminder', 1)->save();
                        }
                    }
                }
                // $this->helper->getLogger($order->getId(), $order->getIncrementId());
            }
        }
        
        $output->writeln("DemoReminder Processed Successfully...");
    }
}