<?php
/**
 * 前台配置文件
 * 所有除开系统级别的前台配置
 */
return array(
    'DEFAULT_MODULE' => 'Admin',
    'MODULE_ALLOW_LIST' => array('Admin','V1'),

    'DB_MAIN' => array(
        'DB_TYPE'   => 'mysqli', // 数据库类型
        'DB_HOST'   => 'localhost', // 服务器地址
        'DB_NAME'   => 'adexchange', // 数据库名
        'DB_USER'   => 'adx',
        'DB_PWD'    => 'rtbs789',
        'DB_PORT'   => '3306', // 端口
        'DB_PREFIX' => '', // 数据库表前缀
    ),

    'DB_ADX_REPORT_SELL' => array(
        'db_type' => 'mysqli',
        'DB_USER' => 'adx',
        'DB_PWD' => 'rtbs789',
        'DB_HOST' => 'localhost',
        'DB_PORT' => '3306',
        'db_name' => 'adx_report_sell',
        'db_charset' => 'utf8',
    ),

    'DB_ADX_REPORT_BUY' => array(
        'db_type' => 'mysqli',
        'DB_USER' => 'adx',
        'DB_PWD' => 'rtbs789',
        'DB_HOST' => 'localhost',
        'DB_PORT' => '3306',
        'db_name' => 'adx_report_buy',
        'db_charset' => 'utf8',
    ),

    'DB_ADX_REPORT' => array(
        'db_type' => 'mysqli',
        'DB_USER' => 'adx',
        'DB_PWD' => 'rtbs789',
        'DB_HOST' => 'localhost',
        'DB_PORT' => '3306',
        'db_name' => 'adx_report',
        'db_charset' => 'utf8',
    ),

    //展现形式
    'INSTL'=>array(1=>'banner',2=>'video',3=>'背投',4=>'视频暂停',5=>'弹窗',6=>'视频悬浮',7=>'开屏',8=>'插屏',9=>'应用墙',10=>'信息流'),

    //banner创意类型
    'BANNER_MIME_TYPE' => array(
        '1'   => '图片',
        '2'   => 'Flash',
        '3'   => 'HTML',
    ),

    //视频：允许的素材类型
    'VIDEO_MIME_TYPE' => array(
        '1'   => 'flv',
        '2'   => 'mp4',
    ),

    //允许的素材文件类型
    'FILE_EXT' => array(
        '1'   => 'jpg',
        '2'   => 'png',
        '3'   => 'gif',
        '4'   => 'swf',
        '5'   => 'flv',
        '6'   => 'mp4',
        '7'   => '动态素材',
    ),

    //贴片位置
    'POS' => array(
        '1'   => '未知',
        '2'   => '前贴片',
        '3'   => '中贴片',
        '4'   => '暂定',
        '5'   => '后贴片',
    ),

    //原生广告布局样式
    'LAYOUT' => array(
        '1'   => '文本内容列表',
        '2'   => 'app列表',
        '3'   => '新闻提要',
        '4'   => '聊天列表',
        '5'   => '走马灯',
        '6'   => '信息流',
        '7'   => '网格',
    ),

    //原生data类型
    'NATIVE_ASSETS_DATA_TYPE' => array(
        '1'   => '赞助信息',
        '2'   => '广告的描述',
        '3'   => '广告产品或者服务的评级',
        '4'   => '用户评级',
        '5'   => '下载/安装数量',
        '6'   => '产品价格',
        '7'   => '销售价格',
        '8'   => '电话号码',
        '9'   => '地址',
        '10'   => '产品、服务相关的描述',
        '11'   => '产品、服务相关的网址',
        '12'   => 'button上显示的文字',
    ),

    //广告位类型
    "PLACE_TYPE" => array(
        "1"=>"图片",
        "2"=>"视频",
        "3"=>"原生",
    ),

    //设备类型
    "DEVICE_TYPE" => array(         
        "1"=>"个人电脑",
        "2"=>"手机",
        "3"=>"平板电脑",
        "4"=>"电视",
    ),

    "OS_TYPE" => array(         
        "0"=>"未知",
        "1"=>"ios",
        "2"=>"android"
    ),

    "LINEARITY" => array(
        "1"=>"线性",
        "2"=>"非线性"
    ),

    "ALLOWADM" => array(
        "1"=>"允许",
        "2"=>"不允许"
    ),

    //失败明细错误id对应的错误描述
    "REPORT_FAILURE" => array(
        22013 => '买方接收超时',
        22014 => '买方回复无效',
        23010 => '买方回复格式错误',
        23011 => '买方回复错误数据',
        23020 => '买方回复中，没有bidid',
        23040 => '买方回复中没有素材id',
        23042 => '买方回复错误，idx错误',
        23043 => '买方回复错误，没有idx',
        23044 => '买方回复的广告类型错误',
        23045 => '买方出价低于底价',
        23052 => '素材高度不对',
        23053 => '素材宽度不对',
        23061 => '不允许投放动态素材',
        23063 => '广告位不支持动态素材',
        24100 => '买方回复中，没有素材',
        24200 => '回复的素材找不到',
        24400 => '素材审核不通过',
        25500 => '买方 HTTP请求500错误',
        25502 => '买方 HTTP请求502错误',
        25404 => '买方 HTTP请求404错误',
        25415 => '买方 HTTP请求415错误',
    ),
);
