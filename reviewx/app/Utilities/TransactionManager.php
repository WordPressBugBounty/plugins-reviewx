<?php

namespace ReviewX\Utilities;

\defined("ABSPATH") || exit;
use Exception;
use Throwable;
class TransactionManager
{
    /**
     * Run a task with potential rollback.
     * 
     * @param callable $wpCallback Should return the data needed for SaaS or true on success.
     * @param callable $saasCallback Receives the result of $wpCallback.
     * @return mixed The SaaS response or false on failure.
     */
    public static function run(callable $wpCallback, callable $saasCallback)
    {
        global $wpdb;
        try {
            // Start WP Transaction
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Raw transaction start
            $wpdb->query('START TRANSACTION');
            // 1. Perform WP updates
            $wpResult = $wpCallback();
            if ($wpResult === \false || \is_wp_error($wpResult)) {
                // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Raw transaction rollback
                $wpdb->query('ROLLBACK');
                return $wpResult;
            }
            // 2. Perform SaaS updates
            $saasResponse = $saasCallback($wpResult);
            // Handle SaaS Response success check
            $isSuccess = \false;
            if (\method_exists($saasResponse, 'getStatusCode')) {
                $status = $saasResponse->getStatusCode();
                $isSuccess = $status >= 200 && $status < 300;
            }
            if (!$isSuccess) {
                // SaaS failed, rollback WP
                // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Raw transaction rollback
                $wpdb->query('ROLLBACK');
                return $saasResponse;
            }
            // Both succeeded
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Raw transaction commit
            $wpdb->query('COMMIT');
            return $saasResponse;
        } catch (Throwable $e) {
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Raw transaction rollback
            $wpdb->query('ROLLBACK');
            throw $e;
        }
    }
}
