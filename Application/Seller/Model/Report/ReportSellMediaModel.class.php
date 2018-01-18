<?php

namespace Seller\Model\Report;

use Think\Model;

class ReportSellMediaModel extends ReportModel {
    protected $connection = 'DB_ADX_REPORT_SELL';
    protected  $tableName = 'media_day_2017_06';

    public $field = array(
        'sellerId' => 'sellerId as sellerId',
        'mediaId' => 'mediaId as mediaId',
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
    public $groupBy = 'mediaId';
    public $dbTablePrefix = "media_day";
    public $whereAry=array();
}