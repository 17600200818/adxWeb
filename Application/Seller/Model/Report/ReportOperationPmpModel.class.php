<?php

namespace Seller\Model\Report;

use Think\Model;

class ReportOperationPmpModel extends ReportModel {
    protected $connection = 'DB_ADX_REPORT';
    protected  $tableName = 'pmp_day_2017_06';

    public $field = array(
        'buyerId' => 'buyerId as buyerId',
        'sellerId' => 'sellerId as sellerId',
        'dealId' => 'dealId as dealId',
        'reportDate' => 'reportDate as reportDate',
        'view' => "sum(view) as view",
        'request' => "sum(request) as request",
        'requestOk' => "sum(requestOk) as requestOk",
        'response' => "sum(response) as response",
        'bid' => "sum(bid) as bid",
        'winBid' => "sum(winBid) as winBid",
        'play' => "sum(play) as play",
        'click' => "sum(click) as click",
        'spend' => "sum(spend)/1000000 as spend",
        'sellerPlay' => "sum(sellerPlay) as sellerPlay",
        'sellerClick' => "sum(sellerClick) as sellerClick",
        'sellerCost' => "sum(sellerCost) as sellerCost",
        'buyerSpend' => "sum(buyerSpend)/1000000 as buyerSpend",
    );
    public $orderBy="reportDate desc";
    public $groupBy = 'id';
    public $dbTablePrefix = "pmp_day";
    public $whereAry=array();
}