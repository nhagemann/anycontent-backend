<?php

namespace AnyContent\CMCK\Modules\Backend\Core\User;

use AnyContent\CMCK\Modules\Backend\Core\Context;

class ConsoleAuthenticationAdapter extends BaseAuthenticationAdapter
{

    protected $session;

    protected $prefix = 'user_';

    protected $users = array();

    protected $user = array();


    public function __construct($config, $session, $options)
    {

    }


    public function isLoggedIn()
    {
        return true;
    }


    public function login($username, $password)
    {

        return true;
    }


    public function logout()
    {

    }


    public function getUserName()
    {
        return 'john@doe.com';
    }


    public function getFirstName()
    {
        return 'John';
    }


    public function getLastName()
    {
        return 'Doe';
    }

}