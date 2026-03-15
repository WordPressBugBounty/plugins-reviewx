<?php

namespace ReviewX\Rest\Controllers;

\defined("ABSPATH") || exit;
use ReviewX\WPDrill\Contracts\InvokableContract;
use ReviewX\WPDrill\DB\QueryBuilder\QueryBuilderHandler;
use ReviewX\WPDrill\Response;
class WPDrillController implements InvokableContract
{
    protected QueryBuilderHandler $db;
    /**
     * @param QueryBuilderHandler $db
     */
    public function __construct(QueryBuilderHandler $db)
    {
        $this->db = $db;
    }
    /**
     * @return Response
     */
    public function __invoke()
    {
        //        $user = User::where('id', 1)->first();
        //        //$user = $this->db->table('users')->where('id', 1)->first();
        //        return reviewx_rest($site)
        //            ->setHeader('Content-Type', 'application/json')
        //            ->details('User fetched successfully')
        //            ->success("User found");
    }
}
