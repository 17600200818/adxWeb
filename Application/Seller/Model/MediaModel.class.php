<?php
namespace Seller\Model;
use Think\Model;
class MediaModel extends Model {

	protected $connection = 'DB_MAIN';

    public function getIdKeyArr($field = '*') {
        $medias = $this->field($field)->select();

        $resultArr = [];
        foreach ($medias as $v) {
            $resultArr[$v['id']] = $v;
        }

        return $resultArr;
    }
}
?>