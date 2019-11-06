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
        return $this->setWithTrashed($withTrashed)->column('id,title');
    }

    /**
     * 获取ip地址列表
     * @return array|mixed
     */
    public function getIpAddrList()
    {
        $records = Cache::store('file')->get(SystemIpConstant::SYSTEM_ALLOW_LOGIN_IP_LIST_NAME);

        if ($records) {
            return $records;
        }

        $records = $this->column('id,ip');

        Cache::store('file')->set(SystemIpConstant::SYSTEM_ALLOW_LOGIN_IP_LIST_NAME, $records);

        return $records;
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

        $allowIp = $this->getIpAddrList();

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