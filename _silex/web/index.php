<?php

use Silex\Provider\HttpCacheServiceProvider;

if (!defined('APPLICATION_PATH'))
{
    define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/..'));
}

require_once __DIR__ . '/../vendor/autoload.php';


$app          = new \AnyContent\CMCK\Modules\Backend\Core\Application\Application();

// Detect environment (default: prod) by checking for the existence of $app_env
if (isset($app_env) && in_array($app_env, array('prod','dev','test','console'))) { $app['env'] = $app_env; }else{$app['env'] = 'prod';}

$app->registerModule('AnyContent\CMCK\Modules\Backend\Core\Init');
$app->registerModule('AnyContent\CMCK\Modules\Backend\Core\Layout');
$app->registerModule('AnyContent\CMCK\Modules\Backend\Core\Repositories');
$app->registerModule('AnyContent\CMCK\Modules\Backend\Core\Context');
$app->registerModule('AnyContent\CMCK\Modules\Backend\Core\Menu');
$app->registerModule('AnyContent\CMCK\Modules\Backend\Core\Listing');
$app->registerModule('AnyContent\CMCK\Modules\Backend\Core\Pager');
$app->registerModule('AnyContent\CMCK\Modules\Backend\Core\Sort');
$app->registerModule('AnyContent\CMCK\Modules\Backend\Core\Edit');
$app->registerModule('AnyContent\CMCK\Modules\Backend\Core\Start');
$app->registerModule('AnyContent\CMCK\Modules\Backend\Core\Config');
$app->registerModule('AnyContent\CMCK\Modules\Backend\Core\Revisions');
$app->registerModule('AnyContent\CMCK\Modules\Backend\Core\Files');
$app->registerModule('AnyContent\CMCK\Modules\Backend\Core\TimeShift');
$app->registerModule('AnyContent\CMCK\Modules\Backend\Core\WorkspacesLanguages');
$app->registerModule('AnyContent\CMCK\Modules\Backend\Core\User');

$app->registerModule('AnyContent\CMCK\Modules\Backend\Libs\BlockUI');
$app->registerModule('AnyContent\CMCK\Modules\Backend\Libs\BootstrapFormHelpers');
$app->registerModule('AnyContent\CMCK\Modules\Backend\Libs\NestedSortable');

require_once (APPLICATION_PATH .'/config/modules.php');

$app->initModules();

if ($app['env']=='test' || $app['env']=='console')
{
    return $app;
}

if (file_exists(APPLICATION_PATH .'/config/repositories.php'))
{
    require_once(APPLICATION_PATH . '/config/repositories.php');
}

if ($app['config']->hasConfigurationSection('http_cache') && $app['config']->getConfigurationSection('http_cache') === true) {
    $app->register(new HttpCacheServiceProvider(), array(
        'http_cache.cache_dir' => APPLICATION_PATH . '/var/cache',
    ));
    $app['http_cache']->run();
}
else {
    $app->run();
}

\KVMLogger\KVMLogger::instance()->logResources();




