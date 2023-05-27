<?php

namespace AnyContent\CMCK\Modules\Backend\Core\Application;

use AnyContent\Client\Client;
use AnyContent\Client\MySQLCache;
use AnyContent\Client\Repository;
use AnyContent\Client\RepositoryFactory;
use AnyContent\CMCK\Modules\Backend\Core\Context\ContextManager;
use AnyContent\CMCK\Modules\Backend\Core\Layout\LayoutManager;
use AnyContent\CMCK\Modules\Backend\Core\Menu\MenuManager;
use AnyContent\CMCK\Modules\Backend\Core\Repositories\RepositoryManager;
use AnyContent\Service\Service;
use Doctrine\Common\Cache\ArrayCache;
use Silex\Application as SilexApplication;

use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Knp\Provider\ConsoleServiceProvider;
use Symfony\Component\Routing\Generator\UrlGenerator;

class Application extends SilexApplication
{

    /** @var  Client */
    protected $client;

    protected $modules = array();

    protected $templatesFolder = array();

    protected $repositories = array();

    protected $authenticationAdapter = array();


    public function __construct(array $values = array())
    {

        parent::__construct($values);

        $this['config'] = $this->share(function ()
        {
            return new ConfigService($this);
        });
    }


    /**
     * @return Session
     */
    protected function getSession()
    {
        return $this['session'];
    }


    /**
     * @return Client
     */
    public function getClient()
    {
        if (!$this->client)
        {
            $this->client = new Client();
        }

        return $this->client;
    }


    /**
     * @param Client $client
     */
    public function setClient($client)
    {
        $this->client = $client;
    }


    public function registerModule($class, $options = array())
    {
        $this->modules[$class] = array( 'class' => $class, 'options' => $options );

    }


    public function registerAuthenticationAdapter($type, $class, $options = array())
    {
        $this->authenticationAdapter[$type] = array( 'class' => $class, 'options' => $options );
    }


    public function getAuthenticationAdapter($config)
    {
        if (array_key_exists($config['type'], $this->authenticationAdapter))
        {

            $class   = $this->authenticationAdapter[$config['type']]['class'];
            $options = $this->authenticationAdapter[$config['type']]['options'];
            unset($config['type']);

            $adapter = new $class($config, $this['session'], $options);
        }
        else
        {
            throw new \Exception ('Unknown authentication adapter type ' . $config['type'] . '.');
        }

        return $adapter;
    }


    /**
     * Adds a path to a folder with templates. The later you add a folder, the more priority you give to the folder, in case a template file exists more than once.
     *
     * @param $path
     */
    public function addTemplatesFolders($path)
    {
        $this->templatesFolder[] = $path;
    }


    public function setCacheDriver($cache)
    {
        $this['cache'] = $cache;
    }


    public function initModules()
    {

        $this->register(new ConsoleServiceProvider(), array(
            'console.name'              => 'AnyContent CMCK Console',
            'console.version'           => '1.0.0',
            'console.project_directory' => APPLICATION_PATH
        ));

        foreach ($this->modules as $module)
        {
            $class = $module['class'] . '\Module';
            $o     = new $class;
            $o->init($this, $module['options']);
            $module['module']                = $o;
            $this->modules[$module['class']] = $module;
        }

        $this->register(new \Silex\Provider\TwigServiceProvider(), array(
            'twig.path' => array_reverse($this->templatesFolder)
        ));

        $this['twig']->setCache(APPLICATION_PATH . '/var/twig');

        // Init Cache

        $cacheConfiguration = $this['config']->getCacheConfiguration();

        switch ($cacheConfiguration['driver']['type'])
        {
            case 'none':
                $cacheDriver = new ArrayCache();
                break;
            case 'apc':
                if (PHP_MAJOR_VERSION === 5) {
                    $cacheDriver = new  \Doctrine\Common\Cache\ApcCache();
                }
                else{
                    $cacheDriver = new  \Doctrine\Common\Cache\ApcuCache();
                }
                $this->setCacheDriver($cacheDriver);
                break;
            case 'memcached':
                $memcached = new \Memcached();
                $memcached->addServer($cacheConfiguration['driver']['host'], $cacheConfiguration['driver']['port']);
                $memcached->setOption(\Memcached::OPT_BINARY_PROTOCOL, 1);
                if (array_key_exists('username', $cacheConfiguration['driver']))
                {
                    $memcached->setSaslAuthData($cacheConfiguration['driver']['username'], $cacheConfiguration['driver']['password']);
                }
                $cacheDriver = new \Doctrine\Common\Cache\MemcachedCache();
                $cacheDriver->setMemcached($memcached);
                $this->setCacheDriver($cacheDriver);
                break;
            case 'file':
                $cacheDriver = new PhPFileCache(APPLICATION_PATH . '/var/doctrine', 'txt');
                $this->setCacheDriver($cacheDriver);
                break;
            case 'mysql':
                $cacheDriver = new MySQLCache($cacheConfiguration['driver']['host'], $cacheConfiguration['driver']['dbname'], $cacheConfiguration['driver']['tablename'], $cacheConfiguration['driver']['user'], $cacheConfiguration['driver']['password'], $cacheConfiguration['driver']['port']);
                $this->setCacheDriver($cacheDriver);
                break;
            default:
                throw new \Exception ('Unknown authentication adapter type ' . $cacheConfiguration['driver']['type'] . '.');
                break;
        }

        $client = $this->getClient();
        $client->setCacheProvider($cacheDriver);

        // Now add the repositories
        $this->getRepositoryManager()->init();

        // Then run all modules
        foreach ($this->modules as $module)
        {
            $module['module']->run($this);
        }

        $this['repos']->setUserInfo($this['user']->getClientUserInfo());


        /** @var ConfigService $config */
        $config = $this['config'];
        if ($config->hasConfigurationSection('service'))
        {
           $section = $config->getConfigurationSection('service');
           if (array_key_exists('path',$section)) {
               $this['acrs'] = new Service($this, $config->getConfigurationSection('repositories'), $section['path'],
                   Service::API_RESTLIKE_1);
           }
        }
    }


    public function addRepository(Repository $repository)
    {
        $repository = $this->getClient()->addRepository($repository);
        $this->getRepositoryManager()->addRepository($repository->getName(), $repository, $repository->getTitle());
    }


    public function renderPage($templateFilename, $vars = array(), $displayMessages = true)
    {
        foreach ($this->modules as $module)
        {
            $module['module']->preRender($this);

        }

        $vars['requestLog'] = false;

        /** @var ContextManager $contextManager */
        $contextManager = $this['context'];

        $repository = $contextManager->getCurrentRepository();

        return $this['layout']->render($templateFilename, $vars, $displayMessages);
    }


    /**
     * Registers a before filter.
     *
     * Before filters are run before any route has been matched. This override additionally provides the application
     * object to the filter.
     *
     * @param mixed   $callback Before filter callback
     * @param integer $priority The higher this value, the earlier an event
     *                          listener will be triggered in the chain (defaults to 0)
     */
    public function before($callback, $priority = 0)
    {
        $app = $this;

        $this->on(KernelEvents::REQUEST, function (GetResponseEvent $event) use ($callback, $app)
        {
            if (HttpKernelInterface::MASTER_REQUEST !== $event->getRequestType())
            {
                return;
            }

            $ret = call_user_func($app['callback_resolver']->resolveCallback($callback), $event->getRequest(), $app);

            if ($ret instanceof Response)
            {
                $event->setResponse($ret);
            }
        }, $priority);
    }


    /**
     * Adds an event listener that listens on the specified events.
     *
     * @param string   $eventName The event to listen on
     * @param callable $listener  The listener
     * @param int      $priority  The higher this value, the earlier an event
     *                            listener will be triggered in the chain (defaults to 0)
     *
     * @see EventDispatcherInterface::addListener
     */
    public function addListener($eventName, $listener, $priority = 0)
    {
        $this['dispatcher']->addListener($eventName, $listener, $priority);
    }


    /**
     * Check if a route has been defined
     *
     * @param $name
     *
     * @return bool
     */
    public function routeExists($name)
    {
        $routeCollection = $this['routes'];

        return (null === $routeCollection->get($name)) ? false : true;
    }


    /**
     * Revision is used for caching, random number during development ($app['debug']=true)
     *
     * @return string
     */
    public function getRevision()
    {
        if ($this['debug'] == false)
        {

            if (file_exists(APPLICATION_PATH . '/config/revision.txt'))
            {
                return file_get_contents(APPLICATION_PATH . '/config/revision.txt');
            }
        }

        return substr(md5(uniqid()), 0, 8);
    }


    /**
     * @return UrlGenerator
     */
    public function getUrlGenerator()
    {
        return $this['url_generator'];
    }


    /**
     * @return LayoutManager
     */
    public function getLayoutManager()
    {
        return $this['layout'];
    }


    /**
     * @return MenuManager
     */
    public function getMenuManager()
    {
        return $this['menus'];
    }


    /**
     * @return RepositoryManager
     */
    public function getRepositoryManager()
    {
        return $this['repos'];
    }

}

