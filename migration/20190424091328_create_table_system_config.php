<?php

use think\migration\Migrator;

class CreateTableSystemConfig extends Migrator
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

        $time = time();

        $data = [
            [
                'name' => 'app_name',
                'value' => '后台管理系统',
                'create_time' => $time,
                'update_time' => $time
            ],
            [
                'name' => 'app_version',
                'value' => '1.0.0',
                'create_time' => $time,
                'update_time' => $time
            ],
            [
                'name' => 'site_name',
                'value' => 'fast-admin',
                'create_time' => $time,
                'update_time' => $time
            ],
            [
                'name' => 'site_copy',
                'value' => 'Copyright [2019] by [magein]',
                'create_time' => $time,
                'update_time' => $time
            ],
            [
                'name' => 'miitbeian',
                'value' => '皖ICP备19006391号',
                'create_time' => $time,
                'update_time' => $time
            ],
            [
                'name' => 'site_icon',
                'value' => '',
                'create_time' => $time,
                'update_time' => $time
            ]
        ];

        $table = \magein\php_tools\extra\Migrate::get(
            $this->table('system_config'),
            [
                ['name', 'string', ['comment' => '参数名称']],
                ['value', 'string', ['comment' => '参数值']]
            ]
        );

        $table->insert($data);
        $table->create();
    }
}
