<?php

namespace AnyContent\CMCK\Modules\Backend\Core\Files;

use AnyContent\Client\ContentFilter;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\File\UploadedFile;

use AnyContent\CMCK\Modules\Backend\Core\Application\Application;

use AnyContent\Client\Repository;
use AnyContent\Client\Folder;
use AnyContent\Client\File;
use AnyContent\Client\UserInfo;

class Controller
{

    public static function listFiles(Application $app, Request $request, $repositoryAccessHash, $path = '', $mode = 'page')
    {
        $vars         = array();
        $vars['root'] = false;

        //https://sunnywalker.github.io/jQuery.FilterTable/
        $app['layout']->addJsFile('jquery.filtertable.min.js');
        
        if ($mode == 'modal')
        {
            $listFilesRouteName    = 'listFilesSelect';
            $listFilesTemplateName = 'files-list-modal.twig';
            $app['layout']->addJsFile('files-modal.js');
        }
        else
        {
            $listFilesRouteName    = 'listFiles';
            $listFilesTemplateName = 'files-list-page.twig';
        }

        $vars['links']['files'] = $app['url_generator']->generate($listFilesRouteName, array( 'repositoryAccessHash' => $repositoryAccessHash, 'path' => '' ));
        $vars['links']['newwindow'] = $app['url_generator']->generate('listFiles', array( 'repositoryAccessHash' => $repositoryAccessHash, 'path' => $path ));

        /** @var Repository $repository */
        $repository = $app['repos']->getRepositoryByRepositoryAccessHash($repositoryAccessHash);

        if ($repository)
        {

            $app['context']->setCurrentRepository($repository);
            $path = '/' . trim($path, '/');

            $vars['delete_folder_path'] = $path;
            $vars['create_folder_path'] = trim($path, '/') . '/';

            $breadcrumbs = explode('/', $path);

            if ($path == '/')
            {
                $breadcrumbs  = array( '' );
                $vars['root'] = true;
            }

            $folders  = array();
            $nextPath = '';
            foreach ($breadcrumbs as $subPath)
            {

                $nextPath .= '/' . $subPath;
                $folder = $repository->getFolder($nextPath);

                if ($folder)
                {
                    $items = array();
                    foreach ($folder->listSubFolders() as $id => $name)
                    {
                        $id   = trim($id, '/');
                        $item = array( 'name' => $name, 'class' => '', 'url' => $app['url_generator']->generate($listFilesRouteName, array( 'repositoryAccessHash' => $repositoryAccessHash, 'path' => $id )) );
                        if (strstr($path, $id))
                        {
                            $item['class'] = 'active';
                        }
                        $items[] = $item;
                    }
                    $folders[] = $items;

                    $files = array();
                    /* @var $file File */
                    foreach ($folder->getFiles() as $file)
                    {
                        $item                      = array();
                        $item['file']              = $file;
                        $item['links']['download'] = $app['url_generator']->generate('downloadFile', array( 'repositoryAccessHash' => $repositoryAccessHash, 'id' => $file->getId() ));

                        if ($file->hasPublicUrl())
                        {
                            $item['links']['view'] = $file->getUrl('default');
                        }
                        else {
                            $item['links']['view'] = $app['url_generator']->generate('viewFile', array('repositoryAccessHash' => $repositoryAccessHash, 'id' => $file->getId()));
                        }

                        $item['links']['delete']   = $app['url_generator']->generate('deleteFile', array( 'repositoryAccessHash' => $repositoryAccessHash, 'id' => $file->getId() ));

                        if ($file->hasPublicUrl())
                        {
                            $item['links']['src'] = $file->getUrl('default');
                        }
                        else
                        {
                           $item['links']['src'] = $item['links']['view'];
                        }

                        $files[] = $item;
                    }

                }
                else
                {
                    return new RedirectResponse($vars['links']['files'], 303);
                }

            }

            $vars['folders'] = $folders;
            $vars['files']   = $files;
            $vars['tiles']=false;

        }

        $vars['menu_mainmenu'] = $app['menus']->renderMainMenu();

        $buttons      = array();
        $buttons[100] = array( 'label' => 'Upload File', 'url' => '', 'glyphicon' => 'glyphicon-cloud-upload', 'id' => 'form_files_button_upload_file' );
        $buttons[200] = array( 'label' => 'Create Folder', 'url' => '', 'glyphicon' => 'glyphicon-folder-open', 'id' => 'form_files_button_create_folder' );
        $buttons[300] = array( 'label' => 'Delete Folder', 'url' => '', 'glyphicon' => 'glyphicon-trash', 'id' => 'form_files_button_delete_folder' );

        $vars['buttons'] = $app['menus']->renderButtonGroup($buttons);

        return $app->renderPage($listFilesTemplateName, $vars);
    }


    public static function viewFile(Application $app, Request $request, $repositoryAccessHash, $id)
    {

        if ($id) {
            /** @var Repository $repository */
            $repository = $app['repos']->getRepositoryByRepositoryAccessHash($repositoryAccessHash);

            if ($repository) {
                $app['context']->setCurrentRepository($repository);
                /** @var File $file */
                $file = $repository->getFile($id);

                if ($file) {

                    if ($file->hasPublicUrl()) {
                        return new RedirectResponse($file->getUrl('default'));
                    };

                    $binary = $repository->getBinary($file);

                    if ($binary !== false) {

                        $headers = array('Content-Type' => 'application/unknown', 'Content-Disposition' => 'inline');

                        if ($file->isImage()) {

                            switch (strtolower(pathinfo($file->getName(), PATHINFO_EXTENSION))) {
                                case 'jpg':
                                    $headers = array('Content-Type' => 'image/jpg');
                                    break;
                                case 'gif':
                                    $headers = array('Content-Type' => 'image/gif');
                                    break;
                                case 'png':
                                    $headers = array('Content-Type' => 'image/png');
                                    break;
                            }
                        }

                        return new Response($binary, 200, $headers);
                    }
                }
            }
        }

        return new Response('File not found', 404);

    }


    public static function downloadFile(Application $app, Request $request, $repositoryAccessHash, $id)
    {

        /** @var Repository $repository */
        $repository = $app['repos']->getRepositoryByRepositoryAccessHash($repositoryAccessHash);

        if ($repository)
        {
            $app['context']->setCurrentRepository($repository);
            /** @var File $file */
            $file = $repository->getFile($id);

            if ($file)
            {

                $binary = $repository->getBinary($file);

                if ($binary !== false)
                {

                    $headers = array( 'Content-Type' => 'application/octet-stream', 'Content-Disposition' => 'attachment;filename="' . $file->getName() . '"' );

                    return new Response($binary, 200, $headers);

                }

            }

        }

        return new Response('File not found', 404);

    }


    public static function deleteFile(Application $app, Request $request, $repositoryAccessHash, $id)
    {

        /** @var Repository $repository */
        $repository = $app['repos']->getRepositoryByRepositoryAccessHash($repositoryAccessHash);

        if ($repository)
        {
            $app['context']->setCurrentRepository($repository);
            /** @var File $file */
            $file = $repository->getFile($id);

            if ($file)
            {
                $app['context']->addSuccessMessage('File ' . $id . ' deleted.');

            }
            else
            {
                $app['context']->addAlertMessage('File ' . $id . ' not found.');
            }

        }

        $path = pathinfo($id, PATHINFO_DIRNAME);

        $url = $app['url_generator']->generate('listFiles', array( 'repositoryAccessHash' => $repositoryAccessHash, 'path' => $path ));

        return new RedirectResponse($url, 303);

    }


    public static function post(Application $app, Request $request, $repositoryAccessHash, $path = '', $mode ='page')
    {
        /** @var Repository $repository */
        $repository = $app['repos']->getRepositoryByRepositoryAccessHash($repositoryAccessHash);


        if ($repository)
        {
            if ($request->request->has('create_folder_path'))
            {
                $path = trim($request->get('create_folder_path'), '/');
                $repository->createFolder($path);
                $app['context']->addSuccessMessage('Folder /' . $path . ' created.');

            }

            if ($request->request->has('delete_folder'))
            {
                $repository->deleteFolder($path, true);
                $app['context']->addSuccessMessage('Folder ' . $path . ' deleted.');
            }

            if ($request->request->has('delete_file'))
            {
                $repository->deleteFile($path . '/' . $request->get('delete_file'), true);
                $app['context']->addSuccessMessage('File ' . $request->request->get('delete_file') . ' deleted.');
            }

            if ($request->request->has('file_original'))
            {
                $file = $repository->getFile($request->request->get('file_original'));
                if ($file)
                {
                    $binary = $repository->getBinary($file);
                    if ($binary !== false)
                    {
                        $repository->saveFile($request->request->get('file_rename'), $binary);
                        $path = trim(pathinfo($request->request->get('file_rename'), PATHINFO_DIRNAME), '/');
                        $app['context']->addSuccessMessage('File ' . $request->request->get('file_original') . ' renamed to ' . $request->request->get('file_rename') . '.');

                        $repository->deleteFile($request->request->get('file_original'));
                    }

                }

            }

            if ($request->files->count() > 0)
            {
                if ($request->files->get('upload_file'))
                {
                    /** @var UploadedFile $file */
                    $file = $request->files->get('upload_file');
                    $id   = trim($path . '/' . $file->getClientOriginalName(), '/');

                    $binary = file_get_contents($file->getRealPath());

                    $result = $repository->saveFile($id, $binary);

                    if ($result)
                    {
                        $app['context']->addSuccessMessage('File upload complete.');
                    }
                    else
                    {
                        $app['context']->addErrorMessage('File upload failed.');
                    }
                }
                else
                {
                    $app['context']->addAlertMessage('No file selected.');
                }

            }
        }

        $url = $app['url_generator']->generate('listFiles', array( 'repositoryAccessHash' => $repositoryAccessHash, 'path' => $path ));

        if ($mode =='modal')
        {
           $url = $app['url_generator']->generate('listFilesSelect', array( 'repositoryAccessHash' => $repositoryAccessHash, 'path' => $path ));
        }

        return new RedirectResponse($url, 303);
    }
}