<?php
namespace AnyContent\CMCK\Modules\Backend\Edit\ReferenceFormElements;

use AnyContent\Client\DataDimensions;
use AnyContent\Client\Repository;
use AnyContent\CMCK\Modules\Backend\Core\Application\Application;
use AnyContent\CMCK\Modules\Backend\Core\Repositories\RepositoryManager;
use CMDL\FormElementDefinitions\ReferenceFormElementDefinition;

class FormElementReference extends \AnyContent\CMCK\Modules\Backend\Edit\SelectionFormElements\FormElementSelection
{
    /** @var  ReferenceFormElementDefinition */

    protected $definition;

    protected $templateName = 'formelement-reference.twig';

    protected $optionsForSelectBox = false;

    protected function getOptionsForSelectBox()
    {

        if ($this->optionsForSelectBox)
        {
            return $this->optionsForSelectBox;
        }

        /** @var Repository $repository */
        $repository = $this->app['context']->getCurrentRepository();


        if ($this->definition->hasRepositoryName())
        {
            /** @var RepositoryManager $repositoryManager */
            $repositoryManager = $this->app['repos'];

            $repository = $repositoryManager->getRepositoryById($this->definition->getRepositoryName());

            if (!$repository)
            {
                $this->app['context']->addAlertMessage('Could not find repository named '.$this->definition->getRepositoryName());
            }
        }

        $options = array();

        if ($repository)
        {
            if ($repository->selectContentType($this->definition->getContentType()))
            {
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
                foreach ($repository->getRecords('', $this->definition->getOrder(), 1, null) as $record)
                {
                    $records[$record->getId()] = $record->getName();
                }

                /** @var RepositoryManager $repositoryManager */
                $repositoryManager = $this->app['repos'];

                $accessHash = $repositoryManager->getAccessHash($repository, $contentTypeDefinition);

                $editUrl = '#';
                if ($this->value != '')
                {
                    $editUrl = $this->app->getUrlGenerator()
                                   ->generate('editRecord', array( 'contentTypeAccessHash' => $accessHash, 'recordId' => $this->value, 'workspace' => $workspace, 'language' => $language ));
                }

                $this->vars['editUrl'] = $editUrl;

                $editUrlPattern = $this->app->getUrlGenerator()
                                      ->generate('editRecord', array( 'contentTypeAccessHash' => $accessHash, 'recordId' => 'recordId', 'workspace' => $workspace, 'language' => $language ));

                $this->vars['editUrlPattern'] = $editUrlPattern;

                $repository->setDataDimensions($currentDataDimensions);

                foreach ($records as $id => $name)
                {
                    $options[$id] = '#' . $id . ': ' . $name;
                }
            }
            else
            {
                $this->app['context']->addAlertMessage('Could not find referenced content type ' . $this->definition->getContentType() . '.');
            }

        }

        $this->optionsForSelectBox = $options;

        return $this->optionsForSelectBox;
    }




}