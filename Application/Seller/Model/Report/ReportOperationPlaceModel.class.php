<?php

namespace Seller\Model\Report;

use Think\Model;

class ReportOperationFailureModel extends ReportModel {
    protected $connection = 'DB_ADX_REPORT';
    protected  $tableName = 'place_day_2017_06';

    public $field = array(
        'buyerId' => 'buyerId as buyerId',
        'sellerId' => 'sellerId as sellerId',
        'mediaId' => 'mediaId as mediaId',
        'placeId' => 'placeId as placeId',
        'reportDate' => 'reportDate as reportDate',
        'view' => "sum(view) as view",
        'request' => "sum(request) as request",
        'requestOk' => "sum(requestOk) as requestOk",
        'response' => "sum(response) as response",
        'bid' => "sum(bid) as bid",
        'play' => "sum(play) as play",
        'click' => "sum(click) as click",
        'spend' => "sum(spend)/1000000 as spend",
        'sellerPlay' => "sum(sellerPlay) as sellerPlay",
        'sellerClick' => "sum(sellerClick) as sellerClick",
        'sellerSpend' => "sum(sellerSpend)/1000000 as sellerSpend",
        'buyerSpend' => "sum(buyerSpend)/1000000 as buyerSpend",
    );
    public $orderBy="reportDate desc";
    public $groupBy = 'id';
    public $dbTablePrefix = "place_day";
    public $whereAry=array();
}