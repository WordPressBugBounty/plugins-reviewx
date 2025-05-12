<?php

namespace Rvx\Rest\Controllers;

use Rvx\WPDrill\Contracts\InvokableContract;
use Rvx\WPDrill\DB\QueryBuilder\QueryBuilderHandler;
use Rvx\WPDrill\Response;
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
        //        return rvx_rest($site)
        //            ->setHeader('Content-Type', 'application/json')
        //            ->details('User fetched successfully')
        //            ->success("User found");
    }
}
