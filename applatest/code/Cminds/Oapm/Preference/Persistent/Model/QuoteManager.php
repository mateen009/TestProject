<?php
namespace Cminds\Oapm\Preference\Persistent\Model;

class QuoteManager extends \Magento\Persistent\Model\QuoteManager
{
    /**
     * @param bool $checkQuote Check quote to be persistent (not stolen)
     * @return void
     */
    public function setGuest($checkQuote = false)
    {
        $oapmOrderQuoteId = $this->checkoutSession->getOapmOrderQuoteId();
        $quote = $this->checkoutSession->getQuote();

        if ($oapmOrderQuoteId === $quote->getId()) {
            $quote->getAddressesCollection();
            $quote->getItemsCollection();
            $quote->getPaymentsCollection();
        } else {
            parent::{__FUNCTION__}($checkQuote);
        }
    }
}
