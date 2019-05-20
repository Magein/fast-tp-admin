<?php

namespace app\admin\component\system_role;

use magein\php_tools\think\Logic;


class SystemRoleLogic extends Logic
{
    protected $fields = [
        'id',
        'title',
        'description',
        'auth',
        'status',
        'create_time',
    ];

    /**
     * @return SystemRoleModel|\think\Model
     */
    protected function model()
    {
        return new SystemRoleModel();
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
            SystemRoleConstant::STATUS_FORBID => '禁用',
            SystemRoleConstant::STATUS_OPEN => '启用',
        ];

        if (null !== $status) {
            return isset($data[$status]) ? $data[$status] : '';
        }

        return $data;
    }

    /**
     * @param $roleId
     * @param $menuId
     * @return bool|false|int
     */
    public function setMenu($roleId, $menuId)
    {
        if (empty($roleId) || empty($menuId)) {
            return false;
        }

        return $this->save(
            [
                'id' => $roleId,
                'auth' => $menuId
            ]
        );
    }

    /**
     * @param $ids
     * @return array
     */
    public function getMenuIdByRoleIds($ids)
    {
        $records = $this->setCondition(['id' => ['in', $ids]])->column('auth');

        $menu_ids = [];
        if ($records) {
            foreach ($records as $item) {
                if ($item) {
                    $menu_ids = array_merge($menu_ids, explode(',', $item));
                }
            }
        }

        return $menu_ids;
    }
}