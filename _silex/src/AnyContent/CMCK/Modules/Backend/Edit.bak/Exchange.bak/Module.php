<?php

namespace AnyContent\CMCK\Modules\Backend\Edit\Exchange;

use AnyContent\CMCK\Modules\Backend\Core\Application\Application;

class Module extends \AnyContent\CMCK\Modules\Backend\Core\Core\Module
{

    /**
     * \PHPExcel_Style_NumberFormat
     *
     * @var array
     */
    protected $defaultOptions = array(

        'FormatCode.DateTime' => 'YYYY/M/D hh:mm'

    );


    public function init(Application $app, $options = array())
    {

        parent::init($app, $options);

        $app->addTemplatesFolders(__DIR__ . '/views/');
        $app
            ->get('/content/export/{contentTypeAccessHash}/modal', 'AnyContent\CMCK\Modules\Backend\Edit\Exchange\Controller::exportRecords')
            ->bind('exportRecords')->value('module', $this);

        $app
            ->get('/content/import/{contentTypeAccessHash}/modal', 'AnyContent\CMCK\Modules\Backend\Edit\Exchange\Controller::importRecords')
            ->bind('importRecords')->value('module', $this);

        $app
            ->post('/content/export/{contentTypeAccessHash}/execute/{token}', 'AnyContent\CMCK\Modules\Backend\Edit\Exchange\Controller::executeExportRecords')
            ->bind('executeExportRecords')->value('module', $this);

        $app
            ->post('/content/import/{contentTypeAccessHash}/execute', 'AnyContent\CMCK\Modules\Backend\Edit\Exchange\Controller::executeImportRecords')
            ->bind('executeImportRecords')->value('module', $this);

        $app['console']->add(new ExportCommand());
        $app['console']->add(new ImportCommand());
        $app['console']->add(new ArchiveCommand());
    }


    public function run(Application $app)
    {

    }

}