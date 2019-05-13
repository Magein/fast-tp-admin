<?php

namespace app\admin\component\system_cache;

class SystemCacheConstant
{

    /**
     * 缓存驱动类型：文件类型
     */
    const STORE_TYPE_FILE = 1;

    /**
     * 缓存驱动类型：memcache
     */
    const STORE_TYPE_MEMCACHE = 2;

    /**
     * 缓存驱动类型：redis
     */
    const STORE_TYPE_REDIS = 3;
}