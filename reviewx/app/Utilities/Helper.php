<?php

namespace Rvx\Utilities;

use Rvx\WPDrill\Plugin;
use Rvx\WPDrill\Response;
use Rvx\Utilities\Auth\Client;
use Rvx\Firebase\JWT\JWT;
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
        $payload = ["iss" => self::getWpDomain(), "iat" => \time(), "exp" => \time() + 300, "nbf" => \time(), "jti" => \uniqid("", \true)];
        $additionalPayload = ["uid" => Client::getUid()];
        // Encode the payload and return the JWT token
        return JWT::encode(\array_merge($payload, $additionalPayload), Client::getSecret(), "HS256");
    }
    private static function getWpDomain() : string
    {
        return $_SERVER["HTTP_HOST"];
    }
    public static function getWpVersion() : string
    {
        return get_bloginfo("version");
    }
    public static function getActiveTheme() : string
    {
        $active_theme = wp_get_theme();
        return $active_theme->get("Name");
    }
    public static function getWpDomainNameOnly() : string
    {
        return \parse_url(home_url(), \PHP_URL_HOST);
    }
    public static function getStrUnique(int $length = 16) : string
    {
        $side = \rand(0, 1);
        // 0 = left, 1 = right
        $salt = \rand(0, 9);
        $len = $length - 1;
        $string = self::generateRandomString($len <= 0 ? 7 : $len);
        $separatorPos = (int) \ceil($length / 4);
        $string = $side === 0 ? $salt . $string : $string . $salt;
        $string = \substr_replace($string, "-", $separatorPos, 0);
        return \substr_replace($string, "-", self::negativeValue($separatorPos), 0);
    }
    public static function generateRandomString(int $length) : string
    {
        $characters = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $charactersLength = \strlen($characters);
        $randomString = "";
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[\rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }
    public static function negativeValue(int $value) : int
    {
        return -$value;
    }
    public static function getApiResponse($response)
    {
        try {
            if ($response->getStatusCode() >= Response::HTTP_OK && $response->getStatusCode() < 300) {
                return self::rest($response->getApiData())->success($response()->message, $response->getStatusCode());
            }
            return self::rest($response->getApiData())->fails($response()->message, $response->getStatusCode());
        } catch (\Throwable $th) {
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
    public static function loggedInUserFullName()
    {
        $current_user = wp_get_current_user();
        if (!$current_user instanceof \WP_User) {
            return '';
        }
        return $current_user->display_name;
    }
    public static function loggedInUserEmail()
    {
        $current_user = wp_get_current_user();
        if (!$current_user instanceof \WP_User) {
            return '';
        }
        return $current_user->user_email;
    }
    public static function loggedInUserDisplayName()
    {
        $current_user = wp_get_current_user();
        if (!$current_user instanceof \WP_User) {
            return '';
        }
        return $current_user->display_name;
    }
    public static function userId()
    {
        $current_user = wp_get_current_user();
        if (!$current_user instanceof \WP_User) {
            return 0;
        }
        return $current_user->ID;
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
    public static function verifiedCustomer($customer_id)
    {
        if (!is_user_logged_in()) {
            return \false;
        }
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
    public static function reviewSettings()
    {
        return \json_decode(get_option("rvx_review_settings"), \true);
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
    public static function productId($product_id)
    {
        return $product_id;
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
        $parts = \explode('-', $orderStatus);
        return $parts[1] ?? null;
    }
    public static function appendToJsonl($file, $data, $jsonOptions = \JSON_UNESCAPED_UNICODE)
    {
        $json = wp_json_encode($data, $jsonOptions);
        if ($json === \false) {
            return \false;
        }
        return \fwrite($file, $json . \PHP_EOL);
    }
}
