<?php

use think\migration\Migrator;
use think\migration\db\Column;

class CreateTableSystemMenu extends Migrator
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-abstractmigration-class
     *
     * The following commands can be used in this method and Phinx will
     * automatically reverse them when rolling back:
     *
     *    createTable
     *    renameTable
     *    addColumn
     *    renameColumn
     *    addIndex
     *    addForeignKey
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    private $table = 'system_menu';

    public function up()
    {
        $time = time();

        $data = array(
            0 => array(
                'id' => 1,
                'pid' => 0,
                'node' => '',
                'title' => '系统管理',
                'icon' => 'fa fa-cog',
                'url' => 'admin/config/index',
                'param' => '',
                'target' => '_self',
                'sort' => 1,
                'status' => 1,
                'create_time' => $time,
                'update_time' => $time,
            ),
            1 => array(
                'id' => 2,
                'pid' => 1,
                'node' => '1',
                'title' => '系统配置',
                'icon' => 'layui-icon layui-icon-set-fill',
                'url' => '',
                'param' => '',
                'target' => '_self',
                'sort' => 2,
                'status' => 1,
                'create_time' => $time,
                'update_time' => $time,
            ),
            2 => array(
                'id' => 3,
                'pid' => 2,
                'node' => '1-2',
                'title' => '网站参数',
                'icon' => 'fa fa-apple',
                'url' => 'admin/config/index',
                'param' => '',
                'target' => '_self',
                'sort' => 1,
                'status' => 1,
                'create_time' => $time,
                'update_time' => $time
            ),
            3 => array(
                'id' => 4,
                'pid' => 6,
                'node' => '1-6',
                'title' => '菜单管理',
                'icon' => 'fa fa-save',
                'url' => 'admin/menu/index',
                'param' => '',
                'target' => '_self',
                'sort' => 10,
                'status' => 1,
                'create_time' => $time,
                'update_time' => $time
            ),
            4 => array(
                'id' => 5,
                'pid' => 2,
                'node' => '1-2',
                'title' => '缓存管理',
                'icon' => 'fa fa-ban',
                'url' => 'admin/cache/index',
                'param' => '',
                'target' => '_self',
                'sort' => 20,
                'status' => 1,
                'create_time' => $time,
                'update_time' => $time
            ),
            5 => array(
                'id' => 6,
                'pid' => 1,
                'node' => '1',
                'title' => '权限管理',
                'icon' => 'fa fa-user-secret',
                'url' => 'admin/auth/index',
                'param' => '',
                'target' => '_self',
                'sort' => 20,
                'status' => 1,
                'create_time' => $time,
                'update_time' => $time
            ),
            6 => array(
                'id' => 7,
                'pid' => 6,
                'node' => '1-6',
                'title' => '角色管理',
                'icon' => 'layui-icon layui-icon-user',
                'url' => 'admin/role/index',
                'param' => '',
                'target' => '_self',
                'sort' => 20,
                'status' => 1,
                'create_time' => $time,
                'update_time' => $time
            ),
            7 => array(
                'id' => 9,
                'pid' => 6,
                'node' => '1-6',
                'title' => '用户管理',
                'icon' => 'layui-icon layui-icon-username',
                'url' => 'admin/user/index',
                'param' => '',
                'target' => '_self',
                'sort' => 30,
                'status' => 1,
                'create_time' => $time,
                'update_time' => $time
            ),
            8 => array(
                'id' => 10,
                'pid' => 2,
                'node' => '1-2',
                'title' => '操作日志',
                'icon' => 'layui-icon layui-icon-note',
                'url' => 'admin/log/index',
                'param' => '',
                'target' => '_self',
                'sort' => 40,
                'status' => 1,
                'create_time' => $time,
                'update_time' => $time
            ),
            9 => array(
                'id' => 11,
                'pid' => 6,
                'node' => '1-6',
                'title' => '登录限制',
                'icon' => 'layui-icon layui-icon-location',
                'url' => 'admin/ip/index',
                'param' => '',
                'target' => '_self',
                'sort' => 58,
                'status' => 1,
                'create_time' => $time,
                'update_time' => $time
            ),
        );

        $this->table($this->table)
            ->addColumn('pid', 'integer', ['comment' => '父类ID'])
            ->addColumn('node', 'string', ['limit' => 60, 'comment' => '节点信息'])
            ->addColumn('title', 'string', ['limit' => 60, 'comment' => '菜单名称'])
            ->addColumn('icon', 'string', ['limit' => 60, 'comment' => '图标', 'default' => ''])
            ->addColumn('url', 'string', ['limit' => 100, 'comment' => '链接', 'default' => ''])
            ->addColumn('param', 'string', ['limit' => 255, 'comment' => '参数', 'default' => ''])
            ->addColumn('target', 'string', ['limit' => 30, 'comment' => '打开方式', 'default' => '_self'])
            ->addColumn('sort', 'integer', ['limit' => \Phinx\Db\Adapter\MysqlAdapter::INT_TINY, 'default' => 99, 'comment' => '排序'])
            ->addColumn('status', 'integer', ['limit' => \Phinx\Db\Adapter\MysqlAdapter::INT_TINY, 'default' => 1, 'comment' => '状态 0 禁用 forbid 1 启用 open'])
            ->addColumn('create_time', 'integer', ['comment' => '创建时间'])
            ->addColumn('update_time', 'integer', ['comment' => '更新时间'])
            ->addColumn('delete_time', 'integer', ['comment' => '删除时间', 'default' => null, 'null' => true])
            ->insert($data)
            ->create();
    }

    public function down()
    {
        $this->table($this->table)->drop();
    }
}
