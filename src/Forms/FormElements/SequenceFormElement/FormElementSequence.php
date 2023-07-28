<?php

namespace AnyContent\Backend\Forms\FormElements\SequenceFormElement;

use AnyContent\Backend\Forms\FormElements\FormElementDefault;
use AnyContent\Backend\Services\ContextManager;
use AnyContent\Backend\Services\FormManager;
use AnyContent\Backend\Services\RepositoryManager;
use AnyContent\Client\Record;
use CMDL\FormElementDefinitions\SequenceFormElementDefinition;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class FormElementSequence extends FormElementDefault
{
    /** @var  SequenceFormElementDefinition */
    protected $definition;

    protected string $template = '@AnyContentBackend/Forms/formelement-sequence.html.twig';

    public function init(RepositoryManager $repositoryManager, ContextManager $contextManager, FormManager $formManager, UrlGeneratorInterface $urlGenerator): void
    {
        $recordId = '-';
        if ($contextManager->isConfigContext()) {
            $dataType = 'config';
            $dataTypeAccessHash = $contextManager->getCurrentConfigTypeAccessHash();
        } else {
            $dataTypeAccessHash = $contextManager->getCurrentContentTypeAccessHash();
            if ($contextManager->getCurrentRecord() instanceof Record) {
                $recordId = $contextManager->getCurrentRecord()->getId();
            }
            $dataType = 'content';
        }

        if ($dataTypeAccessHash !== null) {
            // the sequence rendering form must know if the sequence form element has be inserted via an insert to find its definition
            $insertName = '-';
            if ($this->definition->isInsertedByInsert()) {
                $insertName = $this->definition->getInsertedByInsertName();
            }

            $url = $urlGenerator->generate('anycontent_sequence_edit', [
                'dataType' => $dataType,
                'dataTypeAccessHash' => $dataTypeAccessHash,
                'viewName' => 'default',
                'insertName' => $insertName,
                'recordId' => $recordId,
                'property' => $this->definition->getName(),
            ]);
            $this->vars['src'] = $url;
            return;
        }

        $this->template = '@AnyContentBackend/Forms/formelement-sequence-not-found.html.twig';
    }
}
