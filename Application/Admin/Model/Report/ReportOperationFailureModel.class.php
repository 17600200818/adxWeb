<?php

namespace Admin\Model\Report;

use Think\Model;

class ReportOperationfailureModel extends ReportModel {
    protected $connection = 'DB_ADX_REPORT';
    protected  $tableName = 'failure_day_2017_06';

    public $field = array(
        'buyerId' => 'buyerId as buyerId',
        'creativeId' => 'creativeId as creativeId',
        'sellerId' => 'sellerId as sellerId',
        'mediaId' => 'mediaId as mediaId',
        'placeId' => 'placeId as placeId',
        'reportDate' => 'reportDate as reportDate',
        'errorId' => 'errorId as errorId',
        'errorTotal' => 'sum(errorTotal) as errorTotal',
    );
    public $orderBy="reportDate asc,buyerId asc,sellerId asc,mediaId asc,placeId asc";
    public $groupBy = 'id';
    public $dbTablePrefix = "failure_day";
    public $whereAry=array();
}