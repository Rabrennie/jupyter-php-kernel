<?php

namespace JupyterPhpKernel\Installer;

class Installer
{
    public static function install()
    {
        $kernel_path = self::getInstallPath();

        if (!is_dir($kernel_path)) {
            mkdir($kernel_path, 0755, true);
        }

        file_put_contents($kernel_path . '/kernel.json', json_encode(self::getKernelJSON()));
    }

    protected static function getInstallPath()
    {
        $data_dir = shell_exec('jupyter --data-dir');
        $data_dir = trim($data_dir);

        if ($data_dir == '') {
            echo 'ERROR: Could not find jupyter data directory. Please ensure jupyter is installed';
        }

        return $data_dir . '/kernels/PHP';
    }

    protected static function getKernelJSON()
    {
        return [
            "argv" => ["jupyter-php-kernel" , "-r",  "-c", "{connection_file}"],
            "display_name" => "PHP",
            "language" => "php"
        ];
    }
}
