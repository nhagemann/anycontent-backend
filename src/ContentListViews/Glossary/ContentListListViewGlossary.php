<?php

namespace AnyContent\Backend\ContentListViews\Glossary;

use AnyContent\Backend\ContentListViews\AbstractContentListView;

class ContentListListViewGlossary extends AbstractContentListView
{
    public function getTitle()
    {
        return 'Glossary';
    }

    public function getTemplate(): string
    {
        return '@AnyContentBackend/Listing/listing-contentview-glossary.html.twig';
    }

    public function __invoke(&$vars)
    {
        $glossary = [];

        $repository = $this->contextManager->getCurrentRepository();

        $repository->registerRecordClassForContentType($this->contextManager->getCurrentDataTypeDefinition()->getName(), GlossaryRecord::class);

        $records = $repository->getRecords('', 'name');

        /** @var GlossaryRecord $record */
        foreach ($records as $record) {
            $record->setEditUrl($this->urlGenerator->generate('anycontent_record_edit', [
                'contentTypeAccessHash' => $this->contextManager->getCurrentContentTypeAccessHash(),
                'workspace' => $this->contextManager->getCurrentWorkspace(),
                'language' => $this->contextManager->getCurrentLanguage(),
                'recordId' => $record->getId(),
            ]));

            $index = '0-9';
            $firstLetter = strtoupper(substr($record->getName(), 0, 1));
            if ($firstLetter >= 'A' && $firstLetter <= 'Z') {
                $index = $firstLetter;
            }
            $glossary[$index][] = $record;
        }

        ksort($glossary);

        foreach ($glossary as $index => $items) {
            $c = max(25, count($items));
            $c = ceil($c / 3);
            $glossary[$index] = array_chunk($items, $c);
        }

        $vars['glossary'] = $glossary;

        return $vars;
    }
}
