<?php
namespace Mobility\QuoteRequest\Model;

/**
 * Email from quote request form
 *
 * @api
 * @since 100.2.0
 */
interface MailInterface
{
    /**
     * Send email from quote request
     *
     * @param string $replyTo Reply-to email address
     * @param string $recipientEmail Recipient email address
     * @param array $variables Email template variables
     * @param string $emailTemplate Email Template
     * @return void
     */
    public function send($replyTo, $recipientEmail, array $variables, $emailTemplate);
}
