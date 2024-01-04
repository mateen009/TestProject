<?php
namespace AscentDigital\NetsuiteConnector\Cron\Customer;
use AscentDigital\NetsuiteConnector\Helper\CSMPhone as MCGManager;


class CSMPhone
{
    /**
     * @var MCGManager;
     */
    protected $mcgManager;

    /**
     * @param MCGManager $mcgManager
     */
    public function __construct(
        MCGManager $mcgManager
    )
    {
        $this->mcgManager = $mcgManager;
    }

    
    public function execute()
    {
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/netsuite_cron.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);
        $logger->info('CSMPhone cron is running');
        // die('cron');
        $this->mcgManager->getCSMPhone();
        $logger->info('CSMPhone cron is finished');
    }

    
}
