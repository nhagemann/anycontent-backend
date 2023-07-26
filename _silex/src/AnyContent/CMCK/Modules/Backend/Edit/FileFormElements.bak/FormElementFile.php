<?php

namespace AnyContent\CMCK\Modules\Backend\Edit\FileFormElements;

use AnyContent\Client\File;

class FormElementFile extends \AnyContent\CMCK\Modules\Backend\Core\Edit\FormElementDefault
{

    protected $template = 'formelement-file.twig';


    public function render($layout)
    {

        //$this->vars['types'] = $this->definition->getFileTypes();

        $info = pathinfo($this->getValue());

        if (isset($info['dirname'])) {
            $path = $info['dirname'];
        } else {
            $path = $this->definition->getPath();
        }

        $path = '/'.trim($path, '/');

        $this->vars['url_modal'] = $this->app['url_generator']->generate(
            'formElementFileModal',
            array('repositoryAccessHash' => $this->getCurrentRepositoryAccessHash(), 'path' => $path)
        );

        $this->vars['url_view'] = rtrim(
                $this->app['url_generator']->generate(
                    'viewFile',
                    array('repositoryAccessHash' => $this->getCurrentRepositoryAccessHash(), 'id' => '/')
                ),
                '/'
            ).'/';

        $this->vars['url_download'] = rtrim(
                $this->app['url_generator']->generate(
                    'downloadFile',
                    array('repositoryAccessHash' => $this->getCurrentRepositoryAccessHash(), 'id' => '/')
                ),
                '/'
            ).'/';

        $this->vars['preview'] = false;
        $id = $this->getValue();
        if ($id != '') {
            $type = strtolower(pathinfo($id, PATHINFO_EXTENSION));
            if (in_array($type, array('jpg', 'jpeg', 'gif', 'png'))) {
                $this->vars['preview'] = true;
                $repository = $this->context->getCurrentRepository();
                $fileManager = $repository->getFileManager();
                if ($fileManager) {
                    if ($fileManager->getPublicUrl() != '') {

                        $this->vars['url_view'] = trim($fileManager->getPublicUrl(), '/').'/';
                    }
                }
            }
        }


        return $this->twig->render($this->template, $this->vars);
    }

}