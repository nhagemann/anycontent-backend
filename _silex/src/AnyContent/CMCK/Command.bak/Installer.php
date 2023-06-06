<?php

namespace AnyContent\CMCK\Command;

use Symfony\Component\Filesystem\Filesystem;

class Installer
{

    public static function postInstallUpdate()
    {

        $filesystem = new Filesystem();
        $baseDir    = realpath(__DIR__ . '/../../../../../../../');
        $packageDir = realpath(__DIR__ . '/../../../../');

        echo "...\n";

        if (!$filesystem->exists($baseDir . '/web'))
        {
            echo "Creating web folder\n";
            $filesystem->mkdir($baseDir . '/web');
            $filesystem->mirror($packageDir . '/web', $baseDir . '/web');
        }

        echo "Creating config folder with example config.\n";

        $filesystem->mkdir($baseDir . '/config');

        $filesystem->copy($packageDir . '/config/config.example.yml', $baseDir . '/config/config.example.yml');
        $filesystem->copy($packageDir . '/config/modules.example.php', $baseDir . '/config/modules.example.php');
        $filesystem->copy($packageDir . '/config/repositories.example.php', $baseDir . '/config/repositories.example.php');

        echo "Creating cache folder, deleting eventually current cache files.\n";

        $filesystem->remove($baseDir . '/twig-cache');
        $filesystem->mkdir($baseDir . '/twig-cache');
        $filesystem->remove($baseDir . '/doctrine-cache');
        $filesystem->mkdir($baseDir . '/doctrine-cache');

        echo "Installing console.\n";

        $filesystem->mkdir($baseDir . '/console');
        $filesystem->copy($packageDir . '/console/console', $baseDir . '/console/console');

        echo "...\n";
        echo "Done.\n";
    }

}
