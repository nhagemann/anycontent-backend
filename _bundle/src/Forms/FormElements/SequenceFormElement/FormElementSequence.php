<?php

namespace AnyContent\Backend\Forms\FormElements\SequenceFormElement;

use AnyContent\Backend\Forms\FormElements\FormElementDefault;
use AnyContent\Backend\Services\ContextManager;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class FormElementSequence extends FormElementDefault
{
    protected string $template = '@AnyContentBackend/Forms/formelement-sequence.html.twig';

    public function init(ContextManager $contextManager, UrlGeneratorInterface $urlGenerator): void
    {
        if ($contextManager->isContentContext()) {
            // the sequence rendering form must know, if the sequence form element has be inserted via a insert to find it's definition
            $insertName = '-';
            if ($this->definition->isInsertedByInsert()) {
                $insertName = $this->definition->getInsertedByInsertName();
            }

            $url = $urlGenerator->generate('anycontent_sequence_edit', [
                'dataType' => 'content',
                'dataTypeAccessHash' => $contextManager->getCurrentContentTypeAccessHash(),
                'viewName' => 'default',
                'insertName' => $insertName,
                'recordId' => $contextManager->getCurrentRecord()->getId(),
                'property' => $this->definition->getName(),
            ]);
            $this->vars['src'] = $url;
            return;
        }

        $this->template = '@AnyContentBackend/Forms/formelement-sequence-not-found.html.twig';
    }

//    public function render($layout)
//    {
//        $routeParams = $this->app['request']->get('_route_params');
//
//        if (array_key_exists('contentTypeAccessHash', $routeParams))
//        {
//            $dataTypeAccessHash = $routeParams['contentTypeAccessHash'];
//            $dataType           = 'content';
//
//            $record   = $this->app['context']->getCurrentRecord();
//            $recordId = 0;
//            if ($record)
//            {
//                $recordId = $record->getId();
//            }
//        }
//        elseif (array_key_exists('configTypeAccessHash', $routeParams))
//        {
//            $dataTypeAccessHash = $routeParams['configTypeAccessHash'];
//            $dataType           = 'config';
//            $recordId           = '-';
//        }
//        else
//        {
//            return $this->twig->render('formelement-sequence-not-found.twig', $this->vars);
//
//        }
//
//        // the sequence rendering form must know, if the sequence form element has be inserted via a insert to find it's definition
//        $insertName = '-';
//        if ($this->definition->isInsertedByInsert())
//        {
//            $insertName = $this->definition->getInsertedByInsertName();
//        }
//
//        $url = $this->app['url_generator']->generate('editSequence', array( 'dataType' => $dataType, 'dataTypeAccessHash' => $dataTypeAccessHash, 'viewName' => 'default', 'insertName' => $insertName, 'recordId' => $recordId, 'property' => $this->definition->getName() ));
//
//        $this->vars['src'] = $url;
//
//        return $this->twig->render('formelement-sequence.twig', $this->vars);
//
//    }
}
