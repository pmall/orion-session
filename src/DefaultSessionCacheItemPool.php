<?php declare(strict_types=1);

namespace Orion\Session;

use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;

use Cache\Adapter\Filesystem\FilesystemCachePool;

class DefaultSessionCacheItemPool extends FilesystemCachePool
{
    public function __construct()
    {
        $storage_path = session_save_path();

        if (! $storage_path || ! is_readable($storage_path) || ! is_writable($storage_path)) {

            $storage_path = sys_get_temp_dir();

        }

        $adapter = new Local($storage_path);
        $filesystem = new Filesystem($adapter);

        parent::__construct($filesystem, $folder = '.');
    }
}
