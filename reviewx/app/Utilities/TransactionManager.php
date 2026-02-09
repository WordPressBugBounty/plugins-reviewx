<?php

namespace Rvx\Utilities;

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
            $wpdb->query('START TRANSACTION');
            // 1. Perform WP updates
            $wpResult = $wpCallback();
            if ($wpResult === \false || \is_wp_error($wpResult)) {
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
                $wpdb->query('ROLLBACK');
                return $saasResponse;
            }
            // Both succeeded
            $wpdb->query('COMMIT');
            return $saasResponse;
        } catch (Throwable $e) {
            $wpdb->query('ROLLBACK');
            \error_log("Transaction failed: " . $e->getMessage());
            throw $e;
        }
    }
}
