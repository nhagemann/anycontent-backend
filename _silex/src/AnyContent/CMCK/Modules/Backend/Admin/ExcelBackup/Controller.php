<?php

namespace AnyContent\CMCK\Modules\Backend\Admin\ExcelBackup;

use AnyContent\CMCK\Modules\Backend\Core\Repositories\RepositoryManager;
use AnyContent\CMCK\Modules\Backend\Edit\Exchange\Exporter;
use AnyContent\CMCK\Modules\Backend\Edit\Exchange\Importer;
use AnyContent\Service\Exception\NotFoundException;
use CMDL\ConfigTypeDefinition;
use CMDL\Parser;
use CMDL\Util;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

use AnyContent\CMCK\Modules\Backend\Core\Application\Application;

use AnyContent\Client\Repository;

use CMDL\ContentTypeDefinition;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;

class Controller
{

    public static function adminBackupContentType(Application $app, $contentTypeAccessHash)
    {

        /** @var RepositoryManager $repositoryManager */
        $repositoryManager = $app['repos'];

        $repository = $repositoryManager->getRepositoryByContentTypeAccessHash($contentTypeAccessHash);
        if ($repository) {

            $exporter = new Exporter();
            $binary   = $exporter->backupXLSX($repository, $repository->getCurrentContentTypeName());

            $filename = $repository->getName() . '_' . $repository->getCurrentContentTypeName() . '_' . date('Ymd_hi') . '.xlsx';

            return new Response($binary, 200, array(
                'Content-type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Content-Disposition' => sprintf('attachment; filename="%s"', $filename)
            ));
        }
    }

    public static function adminBackupRepository(Application $app, $repositoryAccessHash)
    {

        /** @var RepositoryManager $repositoryManager */
        $repositoryManager = $app['repos'];

        $repository = $repositoryManager->getRepositoryByRepositoryAccessHash($repositoryAccessHash);

        if ($repository) {
            $exporter = new Exporter();
            $binary   = $exporter->backupXLSX($repository);

            $filename = $repository->getName() . '_all_' . date('Ymd_hi') . '.xlsx';

            return new Response($binary, 200, array(
                'Content-type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Content-Disposition' => sprintf('attachment; filename="%s"', $filename)
            ));
        }
    }

    public static function adminPostBackupRepository(Application $app, Request $request, $repositoryAccessHash)
    {
        /** @var RepositoryManager $repositoryManager */
        $repositoryManager = $app['repos'];

        $repository = $repositoryManager->getRepositoryByRepositoryAccessHash($repositoryAccessHash);

        if ($repository) {
            /** @var UploadedFile $uploadedFile */
            $uploadedFile = $request->files->get('upload_file');

            if ($uploadedFile->isValid()) {
                $importer = new Importer();
                $importer->importBackupXLSX($repository, $uploadedFile->getRealPath());

                $app['context']->addInfoMessage('File processed.');
            }
        }

        $url = $app['url_generator']->generate('admin');
        return new RedirectResponse($url);
    }

}