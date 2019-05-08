<?php

namespace app\admin\component\system_config;

use magein\php_tools\think\Model;

class SystemConfigModel extends Model
{
    protected $table = 'system_config';
    
    protected $readonly = [
        'id',
        'create_time',
    ];
}