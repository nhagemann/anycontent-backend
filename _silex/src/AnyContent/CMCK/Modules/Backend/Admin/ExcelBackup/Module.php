<?php

namespace AnyContent\CMCK\Modules\Backend\Admin\ExcelBackup;

use AnyContent\CMCK\Modules\Backend\Core\Application\Application;

class Module extends \AnyContent\CMCK\Modules\Backend\Core\Core\Module
{

    public function init(Application $app, $options = array())
    {

        parent::init($app, $options);

        $app['console']->add(new BackupCommand());
        $app['console']->add(new BackupImportCommand());



        // Backup Routes

        $app
            ->get('/admin/backup/content_type/{contentTypeAccessHash}', 'AnyContent\CMCK\Modules\Backend\Admin\ExcelBackup\Controller::adminBackupContentType')
            ->bind('adminBackupContentType');
        $app
            ->get('/admin/backup/repository/{repositoryAccessHash}', 'AnyContent\CMCK\Modules\Backend\Admin\ExcelBackup\Controller::adminBackupRepository')
            ->bind('adminBackupRepository');

        $app
            ->post('/admin/backup/repository/import/{repositoryAccessHash}', 'AnyContent\CMCK\Modules\Backend\Admin\ExcelBackup\Controller::adminPostBackupRepository')
            ->bind('adminPostBackupRepository');
    }

}