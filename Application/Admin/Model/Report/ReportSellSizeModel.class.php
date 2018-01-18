<?php

namespace Admin\Model\Report;

use Think\Model;

class ReportSellSizeModel extends ReportModel {
    protected $connection = 'DB_ADX_REPORT_SELL';
    protected  $tableName = 'size_day_2017_06';

    public $field = array(
        'sellerId' => 'sellerId as sellerId',
        'w' => 'w as w',
        'h' => 'h as h',
        'reportDate' => 'reportDate as reportDate',
        'view' => "sum(view) as view",
        'request' => "sum(request) as request",
        'requestOk' => "sum(requestOk) as requestOk",
        'response' => "sum(response) as response",
        'bid' => "sum(bid) as bid",
        'bidOk' => "sum(bidOk) as bidOk",
        'play' => "sum(play) as play",
        'click' => "sum(click) as click",
        'spend' => "sum(spend)/1000000 as spend",
        'sellerPlay' => "sum(sellerPlay) as sellerPlay",
        'sellerClick' => "sum(sellerClick) as sellerClick",
        'sellerSpend' => "sum(sellerSpend)/1000000 as sellerSpend",
        'buyerSpend' => "sum(buyerSpend)/1000000 as buyerSpend",
         'click_rate'=>"sum(click)/sum(play) as  click_rate",
    );
    public $orderBy="reportDate asc,sellerId asc";
    public $groupBy = 'id';
    public $dbTablePrefix = "size_day";
    public $whereAry=array();
}