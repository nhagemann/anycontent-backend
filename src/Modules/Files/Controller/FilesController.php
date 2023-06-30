<?php

namespace AnyContent\Backend\Modules\Files\Controller;

use AnyContent\Backend\Controller\AbstractAnyContentBackendController;
use AnyContent\Client\File;
use AnyContent\Client\Repository;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ANYCONTENT')]
class FilesController extends AbstractAnyContentBackendController
{
    /**
    $app->get('/files/{repositoryAccessHash}/{path}', 'AnyContent\CMCK\Modules\Backend\Core\Files\Controller::listFiles')
    ->assert('path', '.*')->bind('listFiles');

    $app->get('/file/{repositoryAccessHash}/view/{id}', 'AnyContent\CMCK\Modules\Backend\Core\Files\Controller::viewFile')
    ->assert('id', '.*')->bind('viewFile');

    $app
    ->get('/file/{repositoryAccessHash}/download/{id}', 'AnyContent\CMCK\Modules\Backend\Core\Files\Controller::downloadFile')
    ->assert('id', '.*')->bind('downloadFile');

    $app
    ->get('/file/{repositoryAccessHash}/delete/{id}', 'AnyContent\CMCK\Modules\Backend\Core\Files\Controller::deleteFile')
    ->assert('id', '.*')->bind('deleteFile');

    $app->post('/files/{repositoryAccessHash}/{path}', 'AnyContent\CMCK\Modules\Backend\Core\Files\Controller::post')
    ->assert('path', '.*')->value('mode','page');


    // routes for file selection (as used in file form elements)

    $app->get('/file-select/{repositoryAccessHash}/{path}', 'AnyContent\CMCK\Modules\Backend\Core\Files\Controller::listFiles')
    ->assert('path', '.*')->value('mode','modal')->bind('listFilesSelect');

    $app->post('/file-select/{repositoryAccessHash}/{path}', 'AnyContent\CMCK\Modules\Backend\Core\Files\Controller::post')
    ->assert('path', '.*')->value('mode','modal')->value('mode','modal');
     */
    #[Route('/files/{repositoryAccessHash}/{path}', 'anycontent_files', requirements: ['path' => '.*'])]
    public function listFiles(Request $request, $repositoryAccessHash, $path = '', $mode = 'page')
    {
        $vars         = [];
        $vars['root'] = false;

        if ($mode == 'modal') {
            $listFilesRouteName    = 'listFilesSelect';
            $listFilesTemplateName = '@AnyContentBackend/Files/files-list-modal.html.twig';
            //$app['layout']->addJsFile('files-modal.js');
        } else {
            $listFilesRouteName    = 'anycontent_files';
            $listFilesTemplateName = '@AnyContentBackend/Files/files-list-page.html.twig';
        }

        $vars['links']['files'] = $this->generateUrl($listFilesRouteName, ['repositoryAccessHash' => $repositoryAccessHash, 'path' => '']);
        $vars['links']['newwindow'] = $this->generateUrl('anycontent_files', ['repositoryAccessHash' => $repositoryAccessHash, 'path' => $path]);

        /** @var Repository $repository */
        $repository = $this->repositoryManager->getRepositoryByRepositoryAccessHash($repositoryAccessHash);

        if ($repository) {
            $this->contextManager->setCurrentRepository($repository);
            $path = '/' . trim($path, '/');

            $vars['delete_folder_path'] = $path;
            $vars['create_folder_path'] = trim($path, '/') . '/';

            $breadcrumbs = explode('/', $path);

            if ($path == '/') {
                $breadcrumbs  = [''];
                $vars['root'] = true;
            }

            $folders  = [];
            $nextPath = '';
            foreach ($breadcrumbs as $subPath) {
                $nextPath .= '/' . $subPath;
                $folder = $repository->getFolder($nextPath);

                if ($folder) {
                    $items = [];
                    foreach ($folder->listSubFolders() as $id => $name) {
                        $id   = trim($id, '/');
                        $item = ['name' => $name, 'class' => '', 'url' => $this->generateUrl($listFilesRouteName, ['repositoryAccessHash' => $repositoryAccessHash, 'path' => $id])];
                        if (strstr($path, $id)) {
                            $item['class'] = 'active';
                        }
                        $items[] = $item;
                    }
                    $folders[] = $items;

                    $files = [];
                    /* @var $file File */
                    foreach ($folder->getFiles() as $file) {
                        $item                      = [];
                        $item['file']              = $file;
                        $item['links']['download'] = $this->generateUrl('anycontent_file_download', ['repositoryAccessHash' => $repositoryAccessHash, 'id' => $file->getId()]);

                        if ($file->hasPublicUrl()) {
                            $item['links']['view'] = $file->getUrl('default');
                        } else {
                            $item['links']['view'] = $this->generateUrl('anycontent_file_view', ['repositoryAccessHash' => $repositoryAccessHash, 'id' => $file->getId()]);
                        }

                        $item['links']['delete']   = $this->generateUrl('anycontent_file_delete', ['repositoryAccessHash' => $repositoryAccessHash, 'id' => $file->getId()]);

                        if ($file->hasPublicUrl()) {
                            $item['links']['src'] = $file->getUrl('default');
                        } else {
                            $item['links']['src'] = $item['links']['view'];
                        }

                        $files[] = $item;
                    }
                } else {
                    return new RedirectResponse($vars['links']['files'], 303);
                }
            }

            $vars['folders'] = $folders;
            $vars['files']   = $files;
            $vars['tiles'] = false;
        }

        $vars['menu_mainmenu'] = $this->menuManager->renderMainMenu();

        $buttons      = [];
        $buttons[100] = ['label' => 'Upload File', 'url' => '', 'glyphicon' => 'glyphicon-cloud-upload', 'id' => 'form_files_button_upload_file'];
        $buttons[200] = ['label' => 'Create Folder', 'url' => '', 'glyphicon' => 'glyphicon-folder-open', 'id' => 'form_files_button_create_folder'];
        $buttons[300] = ['label' => 'Delete Folder', 'url' => '', 'glyphicon' => 'glyphicon-trash', 'id' => 'form_files_button_delete_folder'];

        $vars['buttons'] = $this->menuManager->renderButtonGroup($buttons);

        return $this->render($listFilesTemplateName, $vars);
    }

    #[Route('/file/{repositoryAccessHash}/view/{id}', 'anycontent_file_view', requirements: ['id' => '.*'])]
    public function viewFile(Request $request, $repositoryAccessHash, $id)
    {
        if ($id) {
            /** @var Repository $repository */
            $repository = $this->repositoryManager->getRepositoryByRepositoryAccessHash($repositoryAccessHash);

            if ($repository) {
                $this->contextManager->setCurrentRepository($repository);
                /** @var File $file */
                $file = $repository->getFile($id);

                if ($file) {
                    if ($file->hasPublicUrl()) {
                        return new RedirectResponse($file->getUrl('default'));
                    };

                    $binary = $repository->getBinary($file);

                    if ($binary !== false) {
                        $headers = ['Content-Type' => 'application/unknown', 'Content-Disposition' => 'inline'];

                        if ($file->isImage()) {
                            switch (strtolower(pathinfo($file->getName(), PATHINFO_EXTENSION))) {
                                case 'jpg':
                                    $headers = ['Content-Type' => 'image/jpg'];
                                    break;
                                case 'gif':
                                    $headers = ['Content-Type' => 'image/gif'];
                                    break;
                                case 'png':
                                    $headers = ['Content-Type' => 'image/png'];
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

    #[Route('/file/{repositoryAccessHash}/download/{id}', 'anycontent_file_download', requirements: ['id' => '.*'])]
    public function downloadFile(Request $request, $repositoryAccessHash, $id)
    {
        /** @var Repository $repository */
        $repository = $this->repositoryManager->getRepositoryByRepositoryAccessHash($repositoryAccessHash);

        if ($repository) {
            $this->contextManager->setCurrentRepository($repository);
            /** @var File $file */
            $file = $repository->getFile($id);

            if ($file) {
                $binary = $repository->getBinary($file);

                if ($binary !== false) {
                    $headers = ['Content-Type' => 'application/octet-stream', 'Content-Disposition' => 'attachment;filename="' . $file->getName() . '"'];

                    return new Response($binary, 200, $headers);
                }
            }
        }

        return new Response('File not found', 404);
    }

    #[Route('/file/{repositoryAccessHash}/delete/{id}', 'anycontent_file_delete', requirements: ['id' => '.*'])]
    public function deleteFile(Request $request, $repositoryAccessHash, $id)
    {
        /** @var Repository $repository */
        $repository = $this->repositoryManager->getRepositoryByRepositoryAccessHash($repositoryAccessHash);

        if ($repository) {
            $this->contextManager->setCurrentRepository($repository);
            /** @var File $file */
            $file = $repository->getFile($id);

            if ($file) {
                $this->contextManager->addSuccessMessage('File ' . $id . ' deleted.');
            } else {
                $this->contextManager->addAlertMessage('File ' . $id . ' not found.');
            }
        }

        $path = pathinfo($id, PATHINFO_DIRNAME);

        $url = $this->generateUrl('listFiles', ['repositoryAccessHash' => $repositoryAccessHash, 'path' => $path]);

        return new RedirectResponse($url, 303);
    }

    #[Route('/files/{repositoryAccessHash}/{path}', 'anycontent_files_post', requirements: ['path' => '.*'])]
    public function post(Request $request, $repositoryAccessHash, $path = '', $mode = 'page')
    {
        /** @var Repository $repository */
        $repository = $this->repositoryManager->getRepositoryByRepositoryAccessHash($repositoryAccessHash);

        if ($repository) {
            if ($request->request->has('create_folder_path')) {
                $path = trim($request->get('create_folder_path'), '/');
                $repository->createFolder($path);
                $this->contextManager->addSuccessMessage('Folder /' . $path . ' created.');
            }

            if ($request->request->has('delete_folder')) {
                $repository->deleteFolder($path, true);
                $this->contextManager->addSuccessMessage('Folder ' . $path . ' deleted.');
            }

            if ($request->request->has('delete_file')) {
                $repository->deleteFile($path . '/' . $request->get('delete_file'), true);
                $this->contextManager->addSuccessMessage('File ' . $request->request->get('delete_file') . ' deleted.');
            }

            if ($request->request->has('file_original')) {
                $file = $repository->getFile($request->request->get('file_original'));
                if ($file) {
                    $binary = $repository->getBinary($file);
                    if ($binary !== false) {
                        $repository->saveFile($request->request->get('file_rename'), $binary);
                        $path = trim(pathinfo($request->request->get('file_rename'), PATHINFO_DIRNAME), '/');
                        $this->contextManager->addSuccessMessage('File ' . $request->request->get('file_original') . ' renamed to ' . $request->request->get('file_rename') . '.');

                        $repository->deleteFile($request->request->get('file_original'));
                    }
                }
            }

            if ($request->files->count() > 0) {
                if ($request->files->get('upload_file')) {
                    /** @var UploadedFile $file */
                    $file = $request->files->get('upload_file');
                    $id   = trim($path . '/' . $file->getClientOriginalName(), '/');

                    $binary = file_get_contents($file->getRealPath());

                    $result = $repository->saveFile($id, $binary);

                    if ($result) {
                        $this->contextManager->addSuccessMessage('File upload complete.');
                    } else {
                        $this->contextManager->addErrorMessage('File upload failed.');
                    }
                } else {
                    $this->contextManager->addAlertMessage('No file selected.');
                }
            }
        }

        $url = $this->generateUrl('listFiles', ['repositoryAccessHash' => $repositoryAccessHash, 'path' => $path]);

        if ($mode == 'modal') {
            $url = $this->generateUrl('listFilesSelect', ['repositoryAccessHash' => $repositoryAccessHash, 'path' => $path]);
        }

        return new RedirectResponse($url, 303);
    }
}
