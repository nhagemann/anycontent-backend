<?php

namespace AnyContent\Backend\Modules\TimeShift;

use AnyContent\Backend\Controller\AbstractAnyContentBackendController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class TimeShiftController extends AbstractAnyContentBackendController
{
    #[Route('/timeshift/content/list/{contentTypeAccessHash}/page/{page}', name:'anycontent_timeshift_records', methods: ['POST'])]
    public function timeShiftListRecords(Request $request, $contentTypeAccessHash, $page = 1)
    {
        $this->applyTimeShift($request);

        return $this->redirect($this->generateUrl('anycontent_records', ['contentTypeAccessHash' => $contentTypeAccessHash, 'page' => $page]));
    }

    #[Route('/timeshift/content/edit/{contentTypeAccessHash}/{recordId}', name:'anycontent_timeshift_record_edit', methods: ['POST'])]
    public function timeShiftEditRecord(Request $request, $contentTypeAccessHash, $recordId)
    {
        $this->applyTimeShift($request);

        return $this->redirect($this->generateUrl('anycontent_record_edit', ['contentTypeAccessHash' => $contentTypeAccessHash, 'recordId' => $recordId]));
    }

    #[Route('/timeshift/content/sort/{contentTypeAccessHash}', name:'anycontent_timeshift_records_sort', methods: ['POST'])]
    public function timeShiftSortRecords(Request $request, $contentTypeAccessHash)
    {
        $this->applyTimeShift($request);

        return $this->redirect($this->generateUrl('anycontent_records_sort', ['contentTypeAccessHash' => $contentTypeAccessHash]));
    }

    #[Route('/timeshift/config/edit/{configTypeAccessHash}', name:'anycontent_timeshift_config_edit', methods: ['POST'])]
    public function timeShiftEditConfig(Request $request, $configTypeAccessHash)
    {
        $this->applyTimeShift($request);

        return $this->redirect($this->generateUrl('anycontent_config_edit', ['configTypeAccessHash' => $configTypeAccessHash]));
    }

    protected function applyTimeShift(Request $request)
    {
        if ($request->request->has('reset')) {
            $this->contextManager->resetTimeShift();
        } else {
            try {
                $date = new \DateTime($request->get('date') . ' ' . $request->get('time'));

                $this->contextManager->setCurrentTimeShift($date->getTimestamp());
            } catch (\Exception $e) {
                $this->contextManager->resetTimeShift();
            }
        }
    }
}
