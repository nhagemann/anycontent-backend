<?php

namespace AnyContent\Backend\Forms\FormElements\SequenceFormElement;

use AnyContent\Backend\Forms\FormElements\FormElementDefault;
use AnyContent\Backend\Services\ContextManager;
use AnyContent\Backend\Services\FormManager;
use AnyContent\Backend\Services\RepositoryManager;
use AnyContent\Client\Record;
use CMDL\FormElementDefinition;
use CMDL\FormElementDefinitions\SequenceFormElementDefinition;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class FormElementSequence extends FormElementDefault
{
    /** @var  SequenceFormElementDefinition */
    protected $definition;

    protected string $type = 'sequence';

    protected string $template = '@AnyContentBackend/Forms/formelement-sequence.html.twig';

    public function __construct(private  ContextManager $contextManager, private UrlGeneratorInterface $urlGenerator){

    }

    public function init(FormElementDefinition $definition, ?string $id, mixed $value = ''): void
    {
        parent::init($definition, $id, $value);
        $recordId = '-';
        if ($this->contextManager->isConfigContext()) {
            $dataType = 'config';
            $dataTypeAccessHash = $this->contextManager->getCurrentConfigTypeAccessHash();
        } else {
            $dataTypeAccessHash = $this->contextManager->getCurrentContentTypeAccessHash();
            if ($this->contextManager->getCurrentRecord() instanceof Record) {
                $recordId = $this->contextManager->getCurrentRecord()->getId();
            }
            $dataType = 'content';
        }

        if ($dataTypeAccessHash !== null) {
            // the sequence rendering form must know if the sequence form element has be inserted via an insert to find its definition
            $insertName = '-';
            if ($this->definition->isInsertedByInsert()) {
                $insertName = $this->definition->getInsertedByInsertName();
            }

            if ($recordId==='-'){
                $recordId =null;
            }

            $url = $this->urlGenerator->generate('anycontent_sequence_edit', [
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
