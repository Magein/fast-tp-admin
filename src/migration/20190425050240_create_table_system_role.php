<?php

use think\migration\Migrator;
use think\migration\db\Column;

class CreateTableSystemRole extends Migrator
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
        \magein\php_tools\extra\Migrate::create(
            $this->table('system_role'),
            [
                ['title', 'string', ['limit' => 50, 'comment' => '名称']],
                ['description', 'string', ['comment' => '描述']],
                ['auth', 'string', ['limit' => 800, 'comment' => '权限', 'default' => '']],
                ['status', 'integer', ['limit' => \Phinx\Db\Adapter\MysqlAdapter::INT_TINY, 'comment' => '状态 0 禁用 forbid 1 启用 open']]
            ]
        );
    }
}
