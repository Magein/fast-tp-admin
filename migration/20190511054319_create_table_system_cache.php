<?php

use think\migration\Migrator;
use think\migration\db\Column;

class CreateTableSystemCache extends Migrator
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
            $this->table('system_cache'),
            [
                ['title', 'string', ['comment' => '名称']],
                ['key', 'string', ['comment' => '键']],
                ['store', 'string', ['comment' => '驱动方式']],
                ['description', 'string', ['default' => '', 'comment' => '描述']]
            ]
        );
    }
}
