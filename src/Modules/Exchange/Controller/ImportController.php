<?php

namespace AnyContent\Backend\Modules\Exchange\Controller;

use AnyContent\Backend\Controller\AbstractAnyContentBackendController;
use AnyContent\Backend\Modules\Exchange\Importer;
use AnyContent\Client\Repository;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ANYCONTENT')]
class ImportController extends AbstractAnyContentBackendController
{
    #[Route('/modal/content/import/{contentTypeAccessHash}', 'anycontent_records_import_modal')]
    public function start(Request $request, $contentTypeAccessHash): Response
    {
        $vars = [];

        /** @var Repository $repository */
        $repository = $this->repositoryManager->getRepositoryByContentTypeAccessHash($contentTypeAccessHash);

        if ($repository) {
            $contentTypeDefinition = $repository->getContentTypeDefinition();
            $this->contextManager->setCurrentRepository($repository);
            $this->contextManager->setCurrentContentType($contentTypeDefinition);
        }

        $vars['links']['execute'] = $this->generateUrl('anycontent_records_import', ['contentTypeAccessHash' => $contentTypeAccessHash]);

        return $this->render('@AnyContentBackend/Export/importrecords-modal.html.twig', $vars);
    }

    #[Route('/content/import/{contentTypeAccessHash}', 'anycontent_records_import')]
    public function executeImportRecords(Importer $importer, Request $request, $contentTypeAccessHash)
    {
        /** @var Repository $repository */
        $repository = $this->repositoryManager->getRepositoryByContentTypeAccessHash($contentTypeAccessHash);
        $success    = false;
        $filename   = null;

        if ($repository) {
            $contentTypeDefinition = $repository->getContentTypeDefinition();
            $this->contextManager->setCurrentRepository($repository);
            $this->contextManager->setCurrentContentType($contentTypeDefinition);

            $workspace = $request->get('workspace');
            if (!$contentTypeDefinition->hasWorkspace($workspace)) {
                $workspace = 'default';
            }

            $language = $request->get('language');
            if (!$contentTypeDefinition->hasLanguage($language)) {
                $language = 'default';
            }

            $format = $request->get('format');

            if ($request->files->get('file')) {
                /** @var UploadedFile $uploadedFile */
                $uploadedFile = $request->files->get('file');

                if ($uploadedFile->isValid()) {
                    $filename = $uploadedFile->getClientOriginalName();

                    $importer->setTruncateRecords((bool)$request->get('truncate'));
                    $importer->setGenerateNewIDs((bool)$request->get('newids'));
                    $importer->setPropertyChangesCheck((bool)$request->get('propertyupdate'));
                    $importer->setNewerRevisionUpdateProtection((bool)$request->get('protectedrevisions'));

                    set_time_limit(0);

                    if ($format == 'j') {
                        $data = file_get_contents($uploadedFile->getRealPath());
                        if ($data) {
                            if ($importer->importJSON($repository, $contentTypeDefinition->getName(), $data, $workspace, $language)) {
                                $success = true;
                            }
                        }
                    } else {
                        if ($importer->importXLSX($repository, $contentTypeDefinition->getName(), $uploadedFile->getRealPath(), $workspace, $language)) {
                            $success = true;
                        }
                    }
                }
            } else {
                $this->contextManager->addInfoMessage('Did you actually upload a file? Nothing here.');
            }
        }
        if ($success) {
            $this->contextManager->addSuccessMessage($importer->getCount() . ' record(s) imported from ' . $filename);
        } else {
            $this->contextManager->addErrorMessage('Could not import records.');
        }

        return new RedirectResponse($this->generateUrl('anycontent_records', ['contentTypeAccessHash' => $contentTypeAccessHash, 'page' => $this->contextManager->getCurrentListingPage(), 'workspace' => $this->contextManager->getCurrentWorkspace(), 'language' => $this->contextManager->getCurrentLanguage()]));
    }
}
