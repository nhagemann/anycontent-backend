<?php

namespace AnyContent\Backend\Forms\FormElements\ReferenceFormElements;

use AnyContent\Backend\Forms\FormElements\SelectionFormElements\FormElementSelection;
use AnyContent\Backend\Services\ContextManager;
use AnyContent\Backend\Services\RepositoryManager;
use AnyContent\Client\DataDimensions;
use CMDL\FormElementDefinitions\ReferenceFormElementDefinition;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class FormElementReference extends FormElementSelection
{
    /** @var  ReferenceFormElementDefinition */
    protected $definition;

    protected string $type = 'reference';

    protected string $template = '@AnyContentBackend/Forms/formelement-reference.html.twig';

    protected $optionsForSelectBox = false;

    public function __construct(private ContextManager $contextManager, private RepositoryManager $repositoryManager, private UrlGeneratorInterface $urlGenerator)
    {
    }

    protected function getOptionsForSelectBox()
    {
        if ($this->optionsForSelectBox) {
            return $this->optionsForSelectBox;
        }

        $repository = $this->contextManager->getCurrentRepository();

        if ($this->definition->hasRepositoryName()) {
            $repositoryManager = $this->repositoryManager;

            $repository = $repositoryManager->getRepositoryById($this->definition->getRepositoryName());

            if (!$repository) {
                $this->contextManager->addAlertMessage('Could not find repository named ' . $this->definition->getRepositoryName());
            }
        }

        $options = [];

        if ($repository) {
            if ($repository->selectContentType($this->definition->getContentType())) {
                $contentTypeDefinition = $repository->getContentTypeDefinition();

                $currentDataDimensions = $repository->getCurrentDataDimensions();

                $workspace = $this->definition->getWorkspace();
                $language  = $this->definition->getLanguage();

                $referenceDataDimensions = new DataDimensions();
                $referenceDataDimensions->setWorkspace($workspace);
                $referenceDataDimensions->setLanguage($language);
                $referenceDataDimensions->setViewName($contentTypeDefinition->getListViewDefinition()->getName());
                $referenceDataDimensions->setTimeShift($this->definition->getTimeShift());

                $repository->setDataDimensions($referenceDataDimensions);

                $records = [ ];
                foreach ($repository->getRecords('', $this->definition->getOrder(), 1, null) as $record) {
                    $records[$record->getId()] = $record->getName();
                }

                $repositoryManager = $this->repositoryManager;

                $accessHash = $repositoryManager->getAccessHash($repository, $contentTypeDefinition);

                $editUrl = '#';
                if ($this->value != '') {
                    $editUrl = $this->urlGenerator->generate('anycontent_record_edit', ['contentTypeAccessHash' => $accessHash, 'recordId' => $this->value, 'workspace' => $workspace, 'language' => $language]);
                }

                $this->vars['editUrl'] = $editUrl;

                $editUrlPattern = $this->urlGenerator->generate('anycontent_record_edit', ['contentTypeAccessHash' => $accessHash, 'recordId' => 'recordId', 'workspace' => $workspace, 'language' => $language]);

                $this->vars['editUrlPattern'] = $editUrlPattern;

                $repository->setDataDimensions($currentDataDimensions);

                foreach ($records as $id => $name) {
                    $options[$id] = '#' . $id . ': ' . $name;
                }
            } else {
                $this->contextManager->addAlertMessage('Could not find referenced content type ' . $this->definition->getContentType() . '.');
            }
        }

        $this->optionsForSelectBox = $options;

        return $this->optionsForSelectBox;
    }
}
