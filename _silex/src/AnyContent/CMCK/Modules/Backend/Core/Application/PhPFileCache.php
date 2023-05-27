<?php

namespace AnyContent\CMCK\Modules\Backend\Core\Application;

class PhPFileCache extends \Doctrine\Common\Cache\PhpFileCache
{
    protected function doFetch($id)
    {
        $data = parent::doFetch($id);

        if ($data)
        {
            return unserialize($data);
        }
        return $data;
    }

    protected function doSave($id, $data, $lifeTime = 0)
    {
        $data = serialize($data);

        return parent::doSave($id, $data, $lifeTime);
    }
}