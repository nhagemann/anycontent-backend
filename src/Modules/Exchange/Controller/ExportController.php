<?php

namespace AnyContent\Backend\Modules\Exchange\Controller;

use AnyContent\Backend\Controller\AbstractAnyContentBackendController;
use AnyContent\Backend\Modules\Exchange\Exporter;
use AnyContent\Client\Repository;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ANYCONTENT')]
class ExportController extends AbstractAnyContentBackendController
{
    #[Route('/modal/content/export/{contentTypeAccessHash}', 'anycontent_records_export_modal')]
    public function modal(Request $request, $contentTypeAccessHash): Response
    {
        $vars = [];

        /** @var Repository $repository */
        $repository = $this->repositoryManager->getRepositoryByContentTypeAccessHash($contentTypeAccessHash);

        if ($repository) {
            $contentTypeDefinition = $repository->getContentTypeDefinition();
            $this->contextManager->setCurrentRepository($repository);
            $this->contextManager->setCurrentContentType($contentTypeDefinition);
        }

        $downloadToken = md5(microtime());

        $vars['links']['execute'] = $this->generateUrl('anycontent_records_export', ['contentTypeAccessHash' => $contentTypeAccessHash, 'token' => $downloadToken]);
        $vars['links']['list']    = $this->generateUrl('anycontent_records', ['contentTypeAccessHash' => $contentTypeAccessHash, 'page' => $this->contextManager->getCurrentListingPage(), 'workspace' => $this->contextManager->getCurrentWorkspace(), 'language' => $this->contextManager->getCurrentLanguage()]);
        $vars['token']            = $downloadToken;

        return $this->render('@AnyContentBackend/Export/exportrecords-modal.html.twig', $vars);
    }

    #[Route('/content/export/{contentTypeAccessHash}', 'anycontent_records_export')]
    public function executeExportRecords(Exporter $exporter, Request $request, $contentTypeAccessHash)
    {
        $token = $request->get('token');

        /** @var Repository $repository */
        $repository = $this->repositoryManager->getRepositoryByContentTypeAccessHash($contentTypeAccessHash);

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

            if ($format == 'j') {
                $data     = $exporter->exportJSON($repository, $repository->getContentTypeDefinition()
                    ->getName(), $workspace, $language);
                $filename = strtolower(date('Ymd') . '_export_' . $contentTypeDefinition->getName() . '_' . $workspace . '_' . $language . '.json');
            } else {
                $data     = $exporter->exportXLSX($repository, $repository->getContentTypeDefinition()
                    ->getName(), $workspace, $language);
                $filename = strtolower(date('Ymd') . '_export_' . $contentTypeDefinition->getName() . '_' . $workspace . '_' . $language . '.xlsx');
            }

            if ($exporter->gotErrors()) {
                foreach ($exporter->getErrors() as $error) {
                    $this->contextManager->addErrorMessage($error);
                }
            }

            if ($data) {
                // Redirect output to a client’s web browser
                header('Content-Type: application/vnd.ms-excel');
                header('Content-Disposition: attachment;filename="' . $filename . '"');
                header('Cache-Control: max-age=0');
                // If you're serving to IE 9, then the following may be needed
                header('Cache-Control: max-age=1');

                // If you're serving to IE over SSL, then the following may be needed
                header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
                header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
                header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
                header('Pragma: public'); // HTTP/1.0

                $response = new Response($data);
                $cookie   = new Cookie("anycontent-download", $token, 0, '/', null, false, false); //Not http only!
                $response->headers->setCookie($cookie);

                $this->contextManager->addSuccessMessage('Records exported to ' . $filename);

                return $response;
            }
        }
        $this->contextManager->addErrorMessage('Could not export records.');

        return new RedirectResponse($this->generateUrl('anycontent_records', ['contentTypeAccessHash' => $contentTypeAccessHash, 'page' => $this->contextManager->getCurrentListingPage(), 'workspace' => $this->contextManager->getCurrentWorkspace(), 'language' => $this->contextManager->getCurrentLanguage()]));
    }
}
