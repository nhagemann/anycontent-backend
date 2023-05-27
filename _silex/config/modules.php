<?php
$app['debug'] = true;

$app->registerModule('AnyContent\CMCK\Modules\Backend\Edit\TextFormElements');
$app->registerModule('AnyContent\CMCK\Modules\Backend\Edit\LinkFormElement');
$app->registerModule('AnyContent\CMCK\Modules\Backend\Edit\EmailFormElement');
$app->registerModule('AnyContent\CMCK\Modules\Backend\Edit\PartitionFormElements');
$app->registerModule('AnyContent\CMCK\Modules\Backend\Edit\RichtextTinyMCEFormElements',['cdn'=>false]);
$app->registerModule('AnyContent\CMCK\Modules\Backend\Edit\SourceCodeFormElements');
$app->registerModule('AnyContent\CMCK\Modules\Backend\Edit\SelectionFormElements');
$app->registerModule('AnyContent\CMCK\Modules\Backend\Edit\NumberFormElement');
$app->registerModule('AnyContent\CMCK\Modules\Backend\Edit\RangeFormElement');
$app->registerModule('AnyContent\CMCK\Modules\Backend\Edit\DateTimeFormElements');
$app->registerModule('AnyContent\CMCK\Modules\Backend\Edit\FileFormElements');
$app->registerModule('AnyContent\CMCK\Modules\Backend\Edit\SequenceFormElement');
$app->registerModule('AnyContent\CMCK\Modules\Backend\Edit\InsertFormElement');
$app->registerModule('AnyContent\CMCK\Modules\Backend\Edit\ReferenceFormElements');
$app->registerModule('AnyContent\CMCK\Modules\Backend\Edit\GeoLocationFormElement');
$app->registerModule('AnyContent\CMCK\Modules\Backend\Edit\TableFormElement');
$app->registerModule('AnyContent\CMCK\Modules\Backend\Edit\ColorFormElement');
$app->registerModule('AnyContent\CMCK\Modules\Backend\Edit\PasswordFormElement');
$app->registerModule('AnyContent\CMCK\Modules\Backend\Edit\EmailFormElement');
$app->registerModule('AnyContent\CMCK\Modules\Backend\Edit\LinkFormElement');
$app->registerModule('AnyContent\CMCK\Modules\Backend\Edit\Exchange');
$app->registerModule('AnyContent\CMCK\Modules\Backend\Libs\jQueryAutosize');
$app->registerModule('AnyContent\CMCK\Modules\Backend\Libs\jQueryMiniColors');
$app->registerModule('AnyContent\CMCK\Modules\Backend\Admin\CMDL');

$app->registerModule('AnyContent\CMCK\Modules\Backend\View\Glossary');
$app->registerModule('AnyContent\CMCK\Modules\Backend\View\CustomList');
$app->registerModule('AnyContent\CMCK\Modules\Backend\View\Map');

$app->registerModule('AnyContent\CMCK\Modules\Backend\Admin\ExcelBackup');


\KVMLogger\KVMLoggerFactory::createWithKLogger('../');

//\KVMLogger\KVMLogger::instance()->logRequest();
//\KVMLogger\KVMLogger::instance()->enablePHPExceptionLogging();
//\KVMLogger\KVMLogger::instance()->enablePHPErrorLogging();

