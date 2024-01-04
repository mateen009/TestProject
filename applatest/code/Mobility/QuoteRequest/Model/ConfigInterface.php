<?php
namespace Mobility\QuoteRequest\Model;

/**
 * QuoteRequest module configuration
 *
 * @api
 * @since 100.2.0
 */
interface ConfigInterface
{
    const STATUS_REQUESTED = 'requested';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';
    
    /**
     * Recipient email config path
     */
    const XML_PATH_EMAIL_RECIPIENT = 'quoterequest/email/recipient_email';

    /**
     * Sender email config path
     */
    const XML_PATH_EMAIL_SENDER = 'quoterequest/email/sender_email_identity';

    /**
     * Email template config path
     */
    const XML_PATH_EMAIL_REQUESTED_TEMPLATE = 'quoterequest/email/requested_template';

    /**
     * Email template config path
     */
    const XML_PATH_EMAIL_APPROVED_TEMPLATE = 'quoterequest/email/approved_template';

    /**
     * Email template config path
     */
    const XML_PATH_EMAIL_REJECTED_TEMPLATE = 'quoterequest/email/rejected_template';

    /**
     * Enabled config path
     */
    const XML_PATH_ENABLED = 'quoterequest/general/enabled';

    /**
     * Check if module is enabled
     *
     * @return bool
     * @since 100.2.0
     */
    public function isEnabled();

    /**
     * Return email template identifier
     *
     * @return string
     * @since 100.2.0
     */
    public function requestedEmailTemplate();

    /**
     * Return email template identifier
     *
     * @return string
     * @since 100.2.0
     */
    public function approvedEmailTemplate();

    /**
     * Return email template identifier
     *
     * @return string
     * @since 100.2.0
     */
    public function rejectedEmailTemplate();

    /**
     * Return email sender address
     *
     * @return string
     * @since 100.2.0
     */
    public function emailSender();

    /**
     * Return email recipient address
     *
     * @return string
     * @since 100.2.0
     */
    public function emailRecipient();
}
