<?php

namespace AnyContent\Backend\Forms\FormElements\ReferenceFormElements;

use AnyContent\Backend\Forms\FormElements\SelectionFormElements\FormElementMultiSelection;
use AnyContent\Client\DataDimensions;
use AnyContent\Client\Repository;
use AnyContent\CMCK\Modules\Backend\Core\Repositories\RepositoryManager;
use CMDL\FormElementDefinitions\MultiReferenceFormElementDefinition;

class FormElementMultiReference extends FormElementMultiSelection
{
    /** @var  MultiReferenceFormElementDefinition */
    protected $definition;

    protected function getOptionsForSelectBox()
    {
        /** @var Repository $repository */
        $repository = $this->contextManager->getCurrentRepository();

        if ($this->definition->hasRepositoryName()) {
            /** @var RepositoryManager $repositoryManager */
            $repositoryManager = $this->repositoryManager;

            $repository = $repositoryManager->getRepositoryById($this->definition->getRepositoryName());

            if (!$repository) {
                $this->contextManager->addAlertMessage('Could not find repository named ' . $this->definition->getRepositoryName());
            }
        }

        $options = [];

        if ($repository->selectContentType($this->definition->getContentType())) {
            $contentTypeDefinition = $repository->getContentTypeDefinition();

            $workspace = $this->definition->getWorkspace();
            $language  = $this->definition->getLanguage();
            $timeshift = $this->definition->getTimeShift();

            $order = $this->definition->getOrder();

            $viewName = $contentTypeDefinition->getListViewDefinition()->getName();

            $dataDimensions = new DataDimensions();
            $dataDimensions->setWorkspace($workspace);
            $dataDimensions->setLanguage($language);
            $dataDimensions->setTimeShift($timeshift);
            $dataDimensions->setViewName($viewName);

            $records = $repository->getRecords('', $order, 1, null, $dataDimensions);

            foreach ($records as $record) {
                $options[$record->getId()] = '#' . $record->getId() . ' ' . $record->getName();
            }
        } else {
            $this->contextManager->addAlertMessage('Could not find referenced content type ' . $this->definition->getContentType() . '.');
        }

        return $options;
    }
}
