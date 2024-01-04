<?php
// CHM-MA



/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace AscentDigital\PayStandCron\Cron;



/**
 * DeletedProduct Controller
 *
 * get deleted products from Netsuite
 * and disable deleted product in magento
 */
class PayStandCron
{

    public function __construct(
        \Magento\Framework\View\Result\PageFactory $pageFactory,
        \PayStand\PayStandMagento\Model\PayStandConfigProvider $paystandConfigProvider,
        \Magento\Quote\Model\QuoteIdMaskFactory $quoteIdMaskFactory,
        \Magento\Quote\Api\CartRepositoryInterface $cartRepository,
        \Magento\Framework\ObjectManagerInterface $objectManager
    ) {
        $this->paystandConfigProvider = $paystandConfigProvider;
        $this->_quoteIdMaskFactory = $quoteIdMaskFactory;
        $this->cartRepository = $cartRepository;
        $this->_objectManager = $objectManager;
        $this->_pageFactory = $pageFactory;

    }

    public function execute()
    {
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/paystand_cron.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);
        $logger->info("PayStand cron is executed.");
        //getting client id and client secret from paystand model
        $config = $this->paystandConfigProvider->getConfig();
        $clientId = $config['payment']['paystandmagento']['client_id'];
        $clientSecret = $config['payment']['paystandmagento']['client_secret'];
        $publishableKey = $config['payment']['paystandmagento']['publishable_key'];

        // Bearer token 
        $token = $this->getToken($clientId, $clientSecret);

        $orderCollection = $this->_objectManager->create(\Magento\Sales\Model\ResourceModel\Order\Collection::class);
        $orderCollection->addFieldToSelect('*');
        $orderCollection->addFieldToFilter('paystand_nsid_status', 'not_updated');
        foreach ($orderCollection as $order) {
            // echo $order->getId();die;
            $paymentId = $order->getPaystandPaymentId();
            $transactionId = $order->getNsInternalId();
            $transactionId = 234234234;
            $paystandRequest = $this->paystandRequest($token, $publishableKey, $paymentId, $transactionId);
            if ($paystandRequest == 1) {
                $order->setPaystandNsidStatus('updated');
                $order->save();
            }
        }





        // die('paystand controller');
        return $this->_pageFactory->create();
        $logger->info("PayStand cron is finished.");
    }

    public function getToken($clientId, $clientSecret)
    {
        $token_url = "https://api.paystand.co/v3/oauth/token/";
        $data = array(
            'grant_type' => 'client_credentials',
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'scope' => 'auth',
        );
        //Paystand api, to get access token
        $ch = curl_init();
        $data_string = json_encode($data);

        $ch = curl_init($token_url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt(
            $ch,
            CURLOPT_HTTPHEADER,
            array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen($data_string)
            )
        );
        $tokenResponse = json_decode(curl_exec($ch));
        $token = $tokenResponse->access_token;
        return $token;
    }

    public function paystandRequest($token, $key, $paymentId, $transactionId)
    {

        $url = 'https://api.paystand.co/v3/netsuites/apply-payment/public';
        $data = array(
            'paymentId' => $paymentId,
            'transactionId' => $transactionId,
            'transactionType' => 'salesOrder',
        );
        // header(
        //     'Content-Type: application/json'
        // ); // Specify the type of data
        $ch = curl_init($url); // Initialise cURL
        $post = json_encode($data); // Encode the data array into a JSON string
        $authorization = "Authorization: Bearer " . $token; // Prepare the authorisation token
        $publishableKey = 'x-publishable-key: ' . $key;
        curl_setopt(
            $ch,
            CURLOPT_HTTPHEADER,
            array(
                'Content-Type: application/json',
                'Accept: application/json',
                $authorization,
                $publishableKey,
                'Content-Length: ' . strlen($post)
            )
        ); // Inject the token into the header
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, 1); // Specify the request method as POST
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post); // Set the posted fields
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1); // This will follow any redirects
        $result = curl_exec($ch); // Execute the cURL statement
        curl_close($ch); // Close the cURL connection
        return json_decode($result); // Return the received data

    }

    public function getPaystandPayments()
    {
        ////////////////////////////////////////////////////////////////////////////////////////////////////
        $curl_handle = curl_init();
        $headers = [
            'Accept: application/json',
            'Authorization: Bearer 7lOfHiaCVDuvlaDEO9PBb0FCOcQb8RRZ',
            'Content-Type: application/json',
            'X-CUSTOMER-ID: irhx9ojrzs6mp1khsxpayzbs',
        ];
        curl_setopt($curl_handle, CURLOPT_URL, 'https://api.paystand.co/v3/payments/all?startDate=2023-11-21&endDate=2023-11-21&format=json&offset=0&limit=50&method=get');
        curl_setopt($curl_handle, CURLOPT_CONNECTTIMEOUT, 2);
        curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl_handle, CURLOPT_HTTPHEADER, $headers);
        $buffer = curl_exec($curl_handle);
        curl_close($curl_handle);
        if (empty($buffer)) {
            print "Nothing returned from url.<p>";
        } else {
            $responses = json_decode($buffer);
            if ($responses->results) {
                $responses = $responses->results;
                echo "<pre>";
                print_r($responses);
                die('$source');
                foreach ($responses as $response) {
                    $source = $response->meta->source;
                    if ($source == 'magento 2') {

                        $quoteId = $response->meta->quote;
                        $quoteIdMask = $this->_quoteIdMaskFactory->create()->load($quoteId, 'masked_id');
                        // If the quoteId is not masked, it comes from a logged in user and should be used as is.
                        $id = (empty($quoteIdMask->getQuoteId())) ? $response->meta->quote : $quoteIdMask->getQuoteId();

                        // Get Order Id from quote
                        // $quote = $this->_quoteFactory->create()->load($id);

                        $quote = $this->cartRepository->get($id);
                        $order = $this->_objectManager->create(
                            \Magento\Sales\Model\Order::class
                        )->loadByIncrementId($quote->getReservedOrderId());
                        if (empty($order)) {
                            $order = $this->_objectManager->create(
                                \Magento\Sales\Model\Order::class
                            )->load($quote->getReservedOrderId());
                        }
                        die($order->getIncrementId());
                        if (empty($order->getIncrementId())) {
                            continue;
                        }
                    }
                }
            }
        }
    }
}
