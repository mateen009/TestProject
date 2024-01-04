<?php
namespace Mobility\QuoteRequest\Api;

use Mobility\QuoteRequest\Api\Data\QuoteRequestInterface;

interface QuoteRequestRepositoryInterface
{
    /**
     * Save
     *
     * @param QuoteRequestInterface $quoteRequest
     *
     * @return mixed
     */
    public function save(QuoteRequestInterface $quoteRequest);

    /**
     * @return mixed
     */
    public function getNew();

    /**
     * @return mixed
     */
    public function getCustomerQuoteRequestList($customerId, $requestStatus = [], $quoteId = null,$dateFrom=null,$dateTo=null,$search=null);

    /**
     * Get by id
     *
     * @param $id
     *
     * @return mixed
     */
    public function getById($id);

    /**
     * Delete
     *
     * @param QuoteRequestInterface $quoteRequest
     *
     * @return mixed
     */
    public function delete(QuoteRequestInterface $quoteRequest);

    /**
     * Delete by id
     *
     * @param $id
     *
     * @return mixed
     */
    public function deleteById($id);
}
