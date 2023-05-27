<?php

namespace AnyContent\CMCK\Modules\Backend\Core\Application;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

use Symfony\Component\Yaml\Parser;

class ConfigService
{

    protected $app;

    protected $yml = null;


    public function __construct(Application $app)
    {
        $this->app = $app;

    }


    public function hasConfigurationSection($sectionName, $topic = null)
    {
        $yml = $this->getYML();

        if (!isset($yml[$sectionName]))
        {
            return false;
        }
        if ($topic != null)
        {
            if (!isset($yml[$topic]))
            {
                return false;
            }
        }

        return true;
    }


    public function getConfigurationSection($sectionName, $topic = null)
    {
        $yml = $this->getYML();

        if (!isset($yml[$sectionName]))
        {
            throw new \Exception ('Missing configuration section ' . $sectionName . '.');
        }

        $yml = $yml[$sectionName];

        if ($topic != null)
        {
            if (!isset($yml[$topic]))
            {
                throw new \Exception ('Missing configuration section ' . $sectionName . '/' . $topic . '.');
            }
            $yml = $yml[$topic];
        }

        return $yml;
    }


    public function getRepositoriesConfiguration()
    {
        $yml = $this->getYML();

        if (!isset($yml['repositories']) || !is_array($yml['repositories']))
        {
            throw new \Exception ('Missing or incomplete repositories configuration.');
        }

        $repositories = array();
        foreach ($yml['repositories'] as $shortcut => $repository)
        {
            $repositories[$shortcut]['url']      = $repository;
            $repositories[$shortcut]['shortcut'] = (string)$shortcut;
        }

        return $repositories;
    }


    public function getAppsConfiguration($repositoryName)
    {
        $yml = $this->getYML();

        $apps = array();
        if (isset($yml['apps']))
        {

            foreach ($yml['apps'] as $app)
            {
                $repositories = explode(',', $app['repositories']);
                $repositories = array_map('trim', $repositories);

                if (in_array($repositoryName, $repositories))
                {
                    $apps[] = $app;
                }
            }
        }

        return $apps;
    }


    public function getCacheConfiguration()
    {
        $yml = $this->getYML();

        $cache = array( 'driver' => array( 'type' => 'none' ), 'seconds_caching_menu' => 0, 'seconds_caching_api_responses' => 600, 'seconds_ignoring_eventually_concurrent_writes' => 0, 'seconds_ignoring_eventually_concurrent_file_updates' => 0 );

        if (isset($yml['cache']))
        {
            $cache = array_merge($cache, $yml['cache']);

            if ($cache['driver']['type'] == 'memcache' || $cache['driver']['type'] == 'memcached')
            {
                if (!isset($cache['driver']['host']))
                {
                    $cache['driver']['host'] = 'localhost';
                }
                if (!isset($cache['driver']['port']))
                {
                    $cache['driver']['port'] = '11211';
                }
            }
        }

        return $cache;
    }


    public function getAuthenticationConfiguration()
    {
        $yml = $this->getYML();

        if (isset($yml['authentication']))
        {
            return $yml['authentication'];
        }

        return null;
    }


    protected function getYML()
    {
        if ($this->yml)
        {
            return $this->yml;
        }

        if (!file_exists(APPLICATION_PATH . '/config/config.yml'))
        {
            throw new \Exception ('Missing configuration file /config/config.yml');
        }

        $configFile = file_get_contents(APPLICATION_PATH . '/config/config.yml');

        $yamlParser = new Parser();

        $this->yml = $yamlParser->parse($configFile);

        return $this->yml;
    }

}