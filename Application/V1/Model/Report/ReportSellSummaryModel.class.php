<?php

namespace V1\Model\Report;

use Think\Model;

class ReportSellSummaryModel extends ReportModel {
    protected $connection = 'DB_ADX_REPORT_SELL';
    protected  $tableName = 'summary_day_2017_06';

    public $field = array(
        'sellerId' => 'sellerId as sellerId',
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
    public $groupBy = 'sellerId';
    public $dbTablePrefix = "summary_day";
    public $whereAry=array();
}