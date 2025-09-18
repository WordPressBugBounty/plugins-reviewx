<?php

namespace Rvx\Utilities;

use Rvx\Firebase\JWT\JWT;
use Rvx\Utilities\Auth\Client;
use Throwable;
use Rvx\WPDrill\Plugin;
use Rvx\WPDrill\Response;
class Helper
{
    public static function plugin() : Plugin
    {
        return Plugin::getInstance();
    }
    public static function rest($data = []) : Response
    {
        return new Response($data);
    }
    public static function pluginPath(string $path = "") : string
    {
        return RVX_DIR_PATH . \ltrim($path, "/");
    }
    public static function resourcePath(string $path = "") : string
    {
        return self::pluginPath("resources/" . \ltrim($path, "/"));
    }
    public static function storagePath(string $path = "") : string
    {
        return self::pluginPath("storage/" . \ltrim($path, "/"));
    }
    public static function pluginFile() : string
    {
        return RVX_FILE;
    }
    public static function getAuthToken() : string
    {
        if (!Client::has()) {
            return "";
        }
        $payload = ["iss" => $_SERVER["HTTP_HOST"], "iat" => \time(), "exp" => \time() + 300, "nbf" => \time(), "jti" => \uniqid("", \true)];
        $additionalPayload = ["uid" => Client::getUid()];
        // Encode the payload and return the JWT token
        return JWT::encode(\array_merge($payload, $additionalPayload), Client::getSecret(), "HS256");
    }
    public static function getWpDomainNameOnly() : string
    {
        return \trim(\parse_url(home_url(), \PHP_URL_HOST), '/');
    }
    public static function getApiResponse($response)
    {
        try {
            if ($response->getStatusCode() >= Response::HTTP_OK && $response->getStatusCode() < 300) {
                return self::rest($response->getApiData())->success($response()->message, $response->getStatusCode());
            }
            return self::rest($response->getApiData())->fails($response()->message, $response->getStatusCode());
        } catch (Throwable $th) {
            return self::rest([])->fails($th->getMessage());
        }
    }
    public static function rvxApi($data = [])
    {
        return new Response($data);
    }
    public static function saasResponse($response) : Response
    {
        $content = $response()->get()->toArray();
        if ($response->getStatusCode() >= 200 && $response->getStatusCode() < 300) {
            return self::rest($content["data"])->success($content["message"], $response->getStatusCode());
        } else {
            return self::rest($content["data"])->fails($content["message"], $response->getStatusCode());
        }
    }
    public static function loggedIn()
    {
        return is_user_logged_in() ? 1 : 0;
    }
    public static function getWpCurrentUser()
    {
        $user = wp_get_current_user();
        return $user->ID > 0 ? $user : null;
    }
    public static function arrayGet($data, $accessor, $default = null)
    {
        $accessorArray = \is_array($accessor) ? $accessor : \explode(".", $accessor);
        $value = $data[\array_shift($accessorArray)] ?? $default;
        foreach ($accessorArray as $key) {
            if (!isset($value[$key])) {
                return $default;
            }
            $value = $value[$key];
        }
        return $value;
    }
    public static function verifiedCustomer($customer_id) : bool
    {
        $orders = wc_get_orders(["customer" => $customer_id, "status" => ["completed", "processing", "on-hold", "pending-payment"], "limit" => 1]);
        if (!empty($orders)) {
            return \true;
        }
        return \false;
    }
    public static function debugLog($message = "")
    {
        $logMessage = \is_array($message) ? "Output is: " . \print_r($message, \true) : "Output is: " . $message;
        \error_log($logMessage);
        return $logMessage;
    }
    public static function arrayValue($array, $key, $default = null)
    {
        if (!\is_array($array)) {
            return $default;
        }
        if (\is_null($key)) {
            return $array;
        }
        if (\array_key_exists($key, $array)) {
            return $array[$key];
        }
        foreach (\explode(".", $key) as $segment) {
            if (\array_key_exists($segment, $array)) {
                $array = $array[$segment];
            } else {
                return $default;
            }
        }
        return $array;
    }
    public static function retrieveReviewId($order_id, $prod_id, $user_id)
    {
        if (isset($order_id, $prod_id, $user_id)) {
            global $wpdb;
            $rx_comment = $wpdb->prefix . "comments";
            $rx_commentmeta = $wpdb->prefix . "commentmeta";
            $data = $wpdb->get_results($wpdb->prepare("SELECT DISTINCT {$rx_commentmeta}.comment_id FROM {$rx_commentmeta} \n                        INNER JOIN {$rx_comment} \n                        ON {$rx_commentmeta}.comment_id = {$rx_comment}.comment_ID \n                        WHERE {$rx_commentmeta}.meta_key = 'reviewx_order' \n                        AND {$rx_commentmeta}.meta_value = %d \n                        AND {$rx_comment}.comment_post_ID = %d \n                        AND {$rx_comment}.user_id = %d", $order_id, $prod_id, $user_id));
            if ($data && !empty($data[0]->comment_id)) {
                return $data[0]->comment_id;
            }
        }
        return 0;
    }
    public static function getIpAddress()
    {
        $ip_address = $_SERVER["HTTP_CLIENT_IP"] ?? $_SERVER["REMOTE_ADDR"];
        return \explode(":", $ip_address)[0] ?? null;
    }
    public static function loadTemplate($template_name, $data = [])
    {
        \extract($data);
        $template_path = RVX_DIR_PATH . "widget/components/" . $template_name . ".php";
        if (\file_exists($template_path)) {
            include $template_path;
        } else {
            echo "Template file not found: " . $template_name;
        }
    }
    public static function prepareLangArray() : array
    {
        $json_file_path = RVX_DIR_PATH . "/translation.json";
        $json_content = \file_get_contents($json_file_path);
        $translations = \json_decode($json_content, \true);
        $result = [];
        foreach ($translations as $key => $text) {
            $result[$key] = __($text, "reviewx");
        }
        return $result;
    }
    public static function rvxGetOrderStatus($orderStatus) : ?string
    {
        return \str_replace('wc-', '', $orderStatus);
    }
    public static function appendToJsonl($file, $data, $jsonOptions = \JSON_UNESCAPED_UNICODE)
    {
        $json = wp_json_encode($data, $jsonOptions);
        if ($json === \false) {
            return \false;
        }
        return \fwrite($file, $json . \PHP_EOL);
    }
    public static function rvxLog($message, $context = 'debug')
    {
        $log_folder = RVX_DIR_PATH . 'log/';
        if (!\is_dir($log_folder)) {
            return;
        }
        $log_file = $log_folder . 'log-' . \wp_date('Y-m-d') . '.log';
        $formatted_message = \sprintf("[%s] [%s]: %s\n", \wp_date('Y-m-d H:i:s'), \strtoupper($context), \print_r($message, \true));
        \file_put_contents($log_file, $formatted_message, \FILE_APPEND);
    }
    public static function domainSupport()
    {
        // Get the full site URL including the subdomain and subdirectory (if present)
        $site_url = get_site_url();
        // Parse the URL to extract the components
        $parsed_url = \parse_url($site_url);
        // Rebuild the base URL (scheme + host + path)
        $full_domain = $parsed_url['scheme'] . '://' . $parsed_url['host'];
        // Add the path (subdirectory) if it exists
        if (isset($parsed_url['path'])) {
            $full_domain .= \rtrim($parsed_url['path'], '/');
        }
        return $full_domain;
    }
    public static function getRestAPIurl() : string
    {
        // If init hasn't fired yet → don't fallback
        if (!did_action('init')) {
            // Return fallback REST API URL
            return site_url('/?rest_route=/reviewx');
        }
        // init has fired, return proper REST API URL
        return get_rest_url(null, 'reviewx');
    }
    public static function orderStatus($newStatus) : string
    {
        $statusMap = ['processing' => 'processing', 'pending' => 'pending', 'on-hold' => 'on_hold', 'completed' => 'completed', 'cancelled' => 'cancelled', 'refunded' => 'refunded', 'failed' => 'failed', 'checkout-draft' => 'draft', 'auto-draft' => 'auto_draft'];
        if (!$statusMap[$newStatus]) {
            return 'any';
        }
        return $statusMap[$newStatus];
    }
    public static function orderItemStatus($newStatus) : string
    {
        $statusMap = ['processing' => 'PROCESSING', 'pending' => 'PENDING_PAYMENT', 'on-hold' => 'ON_HOLD', 'completed' => 'COMPLETED', 'cancelled' => 'CANCELLED', 'refunded' => 'REFUNDED', 'failed' => 'FAILED', 'checkout-draft' => 'DRAFT', 'auto-draft' => 'AUTO-DRAFT'];
        if (!$statusMap[$newStatus]) {
            return 'any';
        }
        return $statusMap[$newStatus];
    }
    public static function validateReturnDate($dateString)
    {
        if (\is_numeric($dateString) && $dateString > 0) {
            $timestamp = (int) $dateString;
        } else {
            $timestamp = \strtotime($dateString);
        }
        if ($timestamp === \false || $timestamp < 0) {
            return null;
        }
        return \wp_date('Y-m-d H:i:s', $timestamp);
    }
    public static function formatToTwoDecimalPlaces($number)
    {
        if (empty($number)) {
            $number = 0;
        }
        return \number_format((float) $number, 2);
    }
    public static function getWpClientInfo() : array
    {
        $current_user = wp_get_current_user();
        $first_name = $current_user->first_name ?: $current_user->user_login;
        $last_name = $current_user->last_name ?: '';
        return ['domain' => \Rvx\Utilities\Helper::getWpDomainNameOnly(), 'url' => home_url(), 'site_locale' => get_locale(), 'first_name' => sanitize_text_field($first_name), 'last_name' => sanitize_text_field($last_name)];
    }
}
