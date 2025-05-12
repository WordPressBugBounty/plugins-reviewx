<?php

namespace Rvx\Rest\Controllers;

use Rvx\Rest\Api;
use Rvx\Rest\Api\BaseApi;
use Rvx\WPDrill\Contracts\InvokableContract;
use Rvx\WPDrill\DB\QueryBuilder\QueryBuilderHandler;
class SaveOptionsController implements InvokableContract
{
    /**
     * @param QueryBuilderHandler $db
     */
    public function __construct(QueryBuilderHandler $db)
    {
    }
    /**
     * @return void
     */
    public function __invoke()
    {
    }
    /**
     * @param $request
     * @return void
     */
    public function license_key($request)
    {
        $this->save_site_data($request);
    }
    /**
     * @param $site_data
     * @return mixed
     */
    public function save_site_data($site_data)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'rx_sites';
        $data_to_insert = array('name' => $site_data['name'], 'uid' => $site_data['uid'], 'domain' => $site_data['domain']);
        $wpdb->insert($table_name, $data_to_insert);
        return $wpdb->insert_id;
    }
}
