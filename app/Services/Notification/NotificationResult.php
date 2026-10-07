<?php

namespace App\Services\Notification;

/**
 * The outcome of trying to send one delivery.
 * Create it with NotificationResult::success() or NotificationResult::failure().
 */
class NotificationResult
{
    /**
     * Provider status meaning "this device address will never work again".
     */
    public const STATUS_INVALID_TOKEN = 'invalid_token';

    /**
     * Use the static helpers below instead of calling this directly.
     *
     * @param  array<string, mixed>  $providerResponse  Already cleaned of secrets.
     */
    public function __construct(
        public readonly bool $successful,
        public readonly ?string $providerMessageId = null,
        public readonly ?string $providerStatus = null,
        public readonly array $providerResponse = [],
        public readonly ?string $errorMessage = null,
        public readonly bool $retryable = true,
    ) {}

    /**
     * Builds a "message was handed to the provider" result.
     *
     * @param  array<string, mixed>  $providerResponse
     */
    public static function success(?string $providerMessageId = null, ?string $providerStatus = 'sent', array $providerResponse = []): self
    {
        return new self(true, $providerMessageId, $providerStatus, $providerResponse);
    }

    /**
     * Builds a "could not send" result.
     * Set $retryable to false for errors that will never work (for example a bad token).
     *
     * @param  array<string, mixed>  $providerResponse
     */
    public static function failure(string $errorMessage, bool $retryable = true, array $providerResponse = [], string $providerStatus = 'failed'): self
    {
        return new self(false, null, $providerStatus, $providerResponse, $errorMessage, $retryable);
    }
}
