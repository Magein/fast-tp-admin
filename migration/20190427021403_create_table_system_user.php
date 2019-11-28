<?php

use think\migration\Migrator;
use think\migration\db\Column;

class CreateTableSystemUser extends Migrator
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

    private $table = 'system_user';

    public function up()
    {
        $data = [
            'username' => 'admin',
            'nickname' => '管理员',
            'password' => sha1(md5('123456')),
            'status' => 1,
            'create_time' => time(),
            'update_time' => time()
        ];

        $table = \magein\php_tools\extra\Migrate::get(
            $this->table($this->table),
            [
                ['username', 'string', ['limit' => 30, 'comment' => '登录账号']],
                ['password', 'string', ['limit' => 50, 'comment' => '登录密码']],
                ['nickname', 'string', ['limit' => 30, 'comment' => '昵称', 'default' => '']],
                ['phone', 'char', ['limit' => 11, 'comment' => '手机号码', 'default' => '']],
                ['email', 'string', ['limit' => 30, 'comment' => '邮箱地址', 'default' => '']],
                ['role', 'string', ['comment' => '角色 多个用逗号隔开', 'default' => '']],
                ['status', 'integer', ['limit' => \Phinx\Db\Adapter\MysqlAdapter::INT_TINY, 'comment' => '状态 0 禁用 forbid 1 启用 open']],
                ['login_at', 'integer', ['limit' => 11, 'comment' => '登录时间', 'default' => 0]],
                ['login_ip', 'string', ['limit' => 30, 'comment' => '登录IP', 'default' => '']],
                ['login_time', 'string', ['limit' => 30, 'comment' => '登录次数', 'default' => 0]],
                ['remark', 'string', ['limit' => 255, 'comment' => '备注', 'default' => '']],
            ],
            [
                'username' => ['unique' => true]
            ]
        );

        $table->insert($data);

        $table->create();

    }

    public function down()
    {
        $this->table($this->table)->drop();
    }
}
