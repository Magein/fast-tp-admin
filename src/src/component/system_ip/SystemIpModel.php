<?php

namespace app\admin\component\system_ip;

use magein\php_tools\think\Model;

class SystemIpModel extends Model
{
    protected $table = 'system_ip';
    
    protected $readonly = [
        'id',
        'create_time',
    ];
    
       
}