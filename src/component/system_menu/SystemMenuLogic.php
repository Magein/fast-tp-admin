<?php

namespace app\admin\component\system_menu;

use app\common\extra\TreeStructure;
use magein\php_tools\think\Logic;
use think\Cache;

class SystemMenuLogic extends Logic
{
    protected $fields = [
        'id',
        'pid',
        'node',
        'title',
        'icon',
        'url',
        'param',
        'target',
        'sort',
        'status',
        'create_time',
    ];

    /**
     * @var array
     */
    private $menuUrl = [];

    /**
     * @return SystemMenuModel|\think\Model
     */
    protected function model()
    {
        return new SystemMenuModel();
    }


    /**
     * @param bool $withTrashed
     * @return array
     */
    public function getTitle($withTrashed = false)
    {
        return $this->setWithTrashed($withTrashed)->column('id,title');
    }

    /**
     * @param mixed $status
     * @return array|mixed|string
     */
    public function transStatus($status = null)
    {
        $data = [
            SystemMenuConstant::STATUS_FORBID => '禁用',
            SystemMenuConstant::STATUS_OPEN => '启用',
        ];

        if (null !== $status) {
            return isset($data[$status]) ? $data[$status] : '';
        }

        return $data;
    }

    /**
     * @return array|mixed
     */
    public function getList()
    {
        return $this->setReturnArrayKey('id')->select();
    }

    /**
     * @return array
     */
    public function geUrlKeyList()
    {
        return $this->setReturnArrayKey('url')->select();
    }

    /**
     * 获取层级关系
     * @param $ids
     * @return array|mixed
     */
    public function floor($ids = null)
    {
        $records = Cache::store('file')->get(SystemMenuConstant::SYSTEM_MENU_TREE_FLOOR_NAME);

        if (empty($records)) {

            $records = $this->select();

            $records = TreeStructure::instance()->floor($records, function ($val, $result) {
                if (isset($result[$val['pid']]['node'])) {
                    $val['node'] = $result[$val['pid']]['node'] . '-' . $val['id'];
                } else {
                    $val['node'] = $val['id'];
                }

                return $val;
            });

            Cache::store('file')->set(SystemMenuConstant::SYSTEM_MENU_TREE_FLOOR_NAME, $records);
        }

        if ($ids) {
            foreach ($records as &$item) {
                if (in_array($item['id'], $ids)) {
                    unset($item);
                }
            }
        }

        return $records;
    }

    /**
     * @return array
     */
    public function getMenuUrl()
    {
        return $this->menuUrl;
    }

    public function getAuthList($auth)
    {
        $records = $this->setReturnArrayKey('id')->select();

        $result = array();
        foreach ($records as $key => &$item) {

            $item = [
                'id' => $item['id'],
                'pid' => $item['pid'],
                'name' => $item['title'],
                'open' => true,
                'checked' => $auth && in_array($item['id'], $auth) ? true : false,
                'children' => isset($item['children']) ? $item['children'] : [],
            ];

            if (isset($records[$item['pid']])) {
                $records[$item['pid']]['children'][] = &$records[$key];
            } else {
                $result[] = &$records[$key];
            }
        }

        return $result;
    }

    /**
     * @param $pk
     * @return bool|int
     */
    public function delete($pk)
    {
        return $this->model()->where('id', 'in', $pk)->delete();
    }
}