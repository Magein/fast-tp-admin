<?php

namespace app\admin\component\system_ip;

use magein\php_tools\think\Logic;
use think\Cache;
use think\Request;


class SystemIpLogic extends Logic
{
    protected $fields = [
        'id',
        'title',
        'ip',
        'remark',
        'create_time',
    ];

    /**
     * @return SystemIpModel|\think\Model
     */
    protected function model()
    {
        return new SystemIpModel();
    }


    /**
     * @param bool $withTrashed
     * @return array
     */
    public function getTitle($withTrashed = false)
    {
        return $this->setWithTrashed($withTrashed)->getFileStorageList('id,title');
    }

    /**
     * 获取ip地址列表
     * @param bool $withTrashed
     * @return array|mixed
     */
    public function getIp($withTrashed)
    {
        return $this->setWithTrashed($withTrashed)->getFileStorageList('id,ip', __FUNCTION__);
    }

    /**
     * @param null $ip
     * @return bool
     */
    public function checkIp($ip = null)
    {
        if (empty($ip)) {
            $ip = Request::instance()->ip();
        }

        $allowIp = $this->getIp();

        if ($allowIp) {

            $ip = explode('.', $ip);

            foreach ($allowIp as $item) {

                $item = trim($item);

                $item = explode('.', $item);

                $diff = array_diff($item, $ip);

                /**
                 * 全匹配
                 */
                if (empty($diff)) {
                    return true;
                } elseif (empty(array_diff($diff, ['*']))) {
                    /**
                     * 通配符匹配
                     */
                    return true;
                }
            }
        }

        return false;
    }

}