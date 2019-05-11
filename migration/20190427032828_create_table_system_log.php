<?php

use think\migration\Migrator;
use think\migration\db\Column;

class CreateTableSystemLog extends Migrator
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
            $this->table('system_log'),
            [
                ['uid', 'integer', ['limit' => 11, 'comment' => '管理员']],
                ['action', 'string', ['limit' => 50, 'comment' => '行为']],
                ['content', 'string', ['limit' => 255, 'comment' => '行为描述']],
                ['ip', 'string', ['limit' => 30, 'comment' => 'IP地址']]
            ]
        );
    }
}
