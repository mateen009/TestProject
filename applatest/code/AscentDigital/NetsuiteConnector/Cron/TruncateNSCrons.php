<?php

namespace AscentDigital\NetsuiteConnector\Cron;

use Magento\Framework\App\ResourceConnection;

class TruncateNSCrons
{
    protected $nsCron;
    protected $connection;

    public function __construct(ResourceConnection $resource)
    {
        $this->connection = $resource->getConnection();
    }

    public function execute()
    {
        // $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/crontest.log');
        // $logger = new \Zend_Log();
        // $logger->addWriter($writer);
        // $logger->info('TruncateNSCrons cron is running');
        // die('cron');
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/netsuite_cron.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);
        $logger->info("Truncate NS cron is executed.");
        $tableName = $this->connection->getTableName('ns_cron_iteration');
        
        $this->connection->delete($tableName);
        $logger->info("Truncate NS cron is FInished.");

    }
}