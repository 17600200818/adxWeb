<?php
namespace V1\Model;
use Think\Model;
class PowerItemModel extends Model {

    protected $connection = 'DB_MAIN';
    public $powerArr = array();
    public $html = '';

    public function findByRoleId($where){
        $rsItem = $this->table('v_role_power_items')->where($where)->order('parentId asc, itemorder asc')->select();
        if(empty($rsItem))
            return null;

        $arrPowerItem = array();
        foreach($rsItem as $item){
            $id = intval($item["idpoweritem"]);
            $arrPowerItem[$id] = $item;
        }

        return $arrPowerItem;
    }

    //获取平台类型
    public function getPlatforms() {
        $platforms = $this->table('platform')->select();
        $platArr = array();
        foreach ($platforms as $k => $v) {
            $platArr[$v['id']] = $v;
        }
        return $platArr;
    }

    //获取权限列表
    public function powerItemList($parentId = 0, $str = '') {
        $powerItems = D('user')->table('power_item')->where('parentId = '.$parentId)->order('itemOrder asc')->select();
        if (empty($powerItems)) {
            return;
        }
        foreach ($powerItems as $v) {
            $v['name'] = $str.$v['name'];
            $this->powerArr[] = $v;
            $this->powerItemList($v['id'], $str."一");
        }
        return $this->powerArr;
    }

    //获取权限列表html
    public function getPowerItemHtml($parentId = 0, $checkedPowerItems = [], $platFormId = 0) {
        $paltformWhere = '';
        if ($platFormId) {
            $paltformWhere = ' and idPlatform = '.$platFormId;
        }
        $powerItems = D('user')->table('power_item')->where('parentId = '.$parentId.' and status = 1'.$paltformWhere)->order('itemOrder asc')->select();
        if (empty($powerItems)) {
            return;
        }
        $this->html .= '<ol class="dd-list">';
        foreach ($powerItems as $v) {
            if (in_array($v['id'], $checkedPowerItems)) {
                $checked = 'checked';
            }else {
                $checked = '';
            }

            $this->html .= '<li class="dd-item">
                      <div class="dd-handle">
                        '.$v['name'].'
                        <input type="checkbox" '.$checked.' style="float: right" name="powerItem[]" value="'.$v['id'].'">
                      </div>';
            $this->getPowerItemHtml($v['id'], $checkedPowerItems);
            $this->html .= '</li>';
        }
        $this->html .= '</ol>';
        return $this->html;
    }

    //修改状态
    public function editStatus($id) {
        $where = ['id' => $id];
        $powerItem = $this->where($where)->find();
        $status = $powerItem['status'] == 1 ? 2 : 1;
        $data = [
            'status' => $status,
            'mid' => $_SESSION['userInfo']['id'],
            'mtime' => date('Y-m-d h:i:s'),
        ];
        $result = $this->where($where)->save($data);
        return $result;
    }

    //获取报表显示的字段
    public function getShowField($roleId, $parentPowerItemId) {
        $powerItemArr = $this->table('v_role_power_items')->field('idPowerItem')->where("roleStatus = 1 and itemStatus = 1 and idRole = {$roleId} and parentId = {$parentPowerItemId}")->select();
        $powerItemsIdArr = [];
        foreach ($powerItemArr as $v) {
            $powerItemsIdArr[] = $v['idpoweritem'];
        }
        $powerItemsIdStr = implode(',', $powerItemsIdArr);
        $powerItems = $this->field('id,name,remark,controller')->where("id in ({$powerItemsIdStr})")->order('itemOrder asc,id asc')->select();
        $fields = [];
        foreach ($powerItems as $v) {
            if ($v['controller'] == '') {
                $fields[$v['id']]['name'] = $v['name'];
                $fields[$v['id']]['remark'] = $v['remark'];
            }
        }
        return $fields;
    }
}
?>