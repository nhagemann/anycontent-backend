<?php

namespace AnyContent\CMCK\Modules\Backend\Core\User;

use AnyContent\CMCK\Modules\Backend\Core\Application\Application;
use AnyContent\CMCK\Modules\Backend\Core\Context;

class BaseAuthenticationAdapter
{

    public function getFullName()
    {
        return trim($this->getFirstName() . ' ' . $this->getLastName());
    }


    public function canDo(Application $app, $action, $object1 = null, $object2 = null, $object3 = null)
    {

        return true;
        switch ($action)
        {
            case 'add':  // repository , contentTypeDefinition
                return false;
                break;
            case 'edit':  // repository , contentTypeDefinition, record
                return false;
                break;
            case 'delete':   // repository , contentTypeDefinition, recordId  or // repository , contentTypeDefinition, null
                return false;
                break;
            case 'sort':   // repository , contentTypeDefinition
                return true;
                break;
            case 'import': // repository , contentTypeDefinition
                return false;
                break;
            case 'export':  // repository , contentTypeDefinition
                return false;
                break;
        }

        return true;
    }
}