<?php

use think\migration\Migrator;
use think\migration\db\Column;

class CreateTableSystemIp extends Migrator
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
    public function change()
    {
        $data = [
            [
                'title' => '本地网络',
                'ip' => '0.0.0.0',
                'remark' => '本地开发环境',
                'create_time' => time(),
                'update_time' => time()
            ],
            [
                'title' => '本地网络',
                'ip' => '127.0.0.1',
                'remark' => '本地开发环境',
                'create_time' => time(),
                'update_time' => time()
            ],
        ];

        $table = \magein\php_tools\extra\Migrate::get(
            $this->table('system_ip'),
            [
                ['title', 'string', ['limit' => 30, 'comment' => '名称']],
                ['ip', 'string', ['limit' => 18, 'comment' => 'IP地址 可以使用通配符']],
                ['remark', 'string', ['limit' => 30, 'comment' => '备注']]
            ]
        );

        $table->insert($data);
        $table->create();
    }
}
