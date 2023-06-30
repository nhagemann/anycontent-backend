<?php

namespace AnyContent\CMCK\Modules\Backend\Edit\SequenceFormElement;

use AnyContent\CMCK\Modules\Backend\Core\Application\Application;

use AnyContent\CMCK\Modules\Backend\Core\Repositories\RepositoryManager;
use CMDL\ConfigTypeDefinition;
use CMDL\ContentTypeDefinition;
use CMDL\ViewDefinition;
use CMDL\FormElementDefinition;
use CMDL\ClippingDefinition;

use AnyContent\Client\Repository;
use AnyContent\Client\Record;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ParameterBag;

class Controller
{

    public static function editSequence(
        Application $app,
        Request $request,
        $dataType,
        $dataTypeAccessHash,
        $recordId,
        $viewName,
        $insertName,
        $property
    ) {

        $vars = array();
        $vars['action']['submit'] = $app['url_generator']->generate(
            'postSequence',
            array(
                'dataType' => $dataType,
                'dataTypeAccessHash' => $dataTypeAccessHash,
                'viewName' => 'default',
                'insertName' => $insertName,
                'recordId' => $recordId,
                'property' => $property,
            )
        );
        $vars['action']['add'] = $app['url_generator']->generate(
            'addSequenceItem',
            array(
                'dataType' => $dataType,
                'dataTypeAccessHash' => $dataTypeAccessHash,
                'viewName' => 'default',
                'insertName' => $insertName,
                'property' => $property,
            )
        );

        $app['layout']->addJsFile('editsequence.js');

        $vars['property'] = $property;

        /** @var Repository $repository */
        $repository = self::getRepository($app, $dataType, $dataTypeAccessHash);

        $dataTypeDefinition = self::getDataTypeDefinition($app, $repository, $dataType, $dataTypeAccessHash);

        if ($repository && $dataTypeDefinition) {
            $repository->selectWorkspace($app['context']->getCurrentWorkspace());
            $repository->selectLanguage($app['context']->getCurrentLanguage());
            $repository->setTimeShift($app['context']->getCurrentTimeShift());
            $repository->selectView($viewName);

            $vars['definition'] = $dataTypeDefinition;

            $formElementDefinition = self::getFormElementDefinition(
                $request,
                $dataTypeDefinition,
                $insertName,
                $property
            );

            if ($formElementDefinition) {

                if ($formElementDefinition->getFormElementType() == 'sequence') {

                    $sequence = [];

                    if ($dataType === 'content') {
                        if ($recordId) {
                            /** @var Record $record */
                            $record = $repository->getRecord($recordId);
                            $sequence = $record->getProperty($property, array());
                        } else {
                            $sequence = $formElementDefinition->getDefaultValue();
                        }

                        $sequence = @json_decode($sequence, true);
                        if (json_last_error() != JSON_ERROR_NONE OR !is_array($sequence)) {
                            $sequence = array();
                        }
                    }

                    if ($dataType === 'config') {
                        $config = $repository->getConfig($dataTypeDefinition->getName());

                        $sequence = $config->getProperty($property, array());

                        $sequence = @json_decode($sequence, true);
                        if (json_last_error() != JSON_ERROR_NONE OR !is_array($sequence)) {
                            $sequence = array();
                        }
                    }

                    $vars['count'] = count($sequence);
                    $vars['items'] = array();

                    $inserts = $formElementDefinition->getInserts();

                    $vars['inserts'] = $inserts;

                    // silently render all potential inserts to add their Javascript-Files to the Layout
                    foreach ($inserts as $k => $v) {

                        $clippingDefinition = $dataTypeDefinition->getClippingDefinition($k);
                        $app['form']->renderFormElements(
                            'form_sequence',
                            $clippingDefinition->getFormElementDefinitions(),
                            array(),
                            array(
                                'language' => $app['context']->getCurrentLanguage(),
                                'workspace' => $app['context']->getCurrentWorkspace(),
                            ),
                            null
                        );
                    }

                    $i = 0;
                    foreach ($sequence as $item) {
                        $insert = key($item);
                        $properties = array_shift($item);

                        if ($dataTypeDefinition->hasClippingDefinition(
                            $insert
                        )
                        ) // ignore eventually junk data after cmdl changes
                        {
                            $i++;

                            /** @var ClippingDefinition $clippingDefinition */
                            $clippingDefinition = $dataTypeDefinition->getClippingDefinition($insert);
                            $item = array();
                            $item['form'] = $app['form']->renderFormElements(
                                'form_sequence',
                                $clippingDefinition->getFormElementDefinitions(),
                                $properties,
                                array(
                                    'language' => $app['context']->getCurrentLanguage(),
                                    'workspace' => $app['context']->getCurrentWorkspace(),
                                ),
                                'item_'.$i
                            );
                            $item['type'] = $insert;
                            $item['title'] = $inserts[$insert];
                            $item['sequence'] = $i;
                            $vars['items'][] = $item;
                        }

                    }

                    return $app->renderPage('editsequence.twig', $vars);
                }
            }

        }

        return new Response('Error getting repository from dataTypeAccessHash.');

    }


    public static function postSequence(
        Application $app,
        Request $request,
        $dataType,
        $dataTypeAccessHash,
        $recordId,
        $property
    ) {

        /** @var Repository $repository */
        $repository = self::getRepository($app, $dataType, $dataTypeAccessHash);
        $dataTypeDefinition = self::getDataTypeDefinition($app, $repository, $dataType, $dataTypeAccessHash);

        if ($repository && $dataTypeDefinition) {

            $items = array();
            foreach ($request->request->getIterator() as $key => $value) {
                $split = explode('_', $key);

                if (count($split >= 3)) {

                    if ($split[0] == 'item') {
                        $nr = (int)($split[1]);
                        $l = strlen('item_'.$nr);
                        $property = substr($key, $l + 1);
                        $items[$nr][$property] = $value;

                    }
                }

            }

            $sequence = array();
            if ($request->request->has('sequence')) {
                $types = $request->get('type');
                $i = 0;
                foreach ($request->get('sequence') as $nr) {
                    $item = $items[$nr];
                    $type = $types[$i];

                    $clippingDefinition = $dataTypeDefinition->getClippingDefinition($type);

                    $bag = new ParameterBag();
                    $bag->add($item);

                    $item = $app['form']->extractFormElementValuesFromPostRequest(
                        $bag,
                        $clippingDefinition->getFormElementDefinitions(),
                        array()
                    );

                    $sequence[] = array($type => $item);
                    $i++;
                }
            }

            return $app->json(array('sequence' => $sequence));
        }

    }


    public static function addSequenceItem(
        Application $app,
        Request $request,
        $dataType,
        $dataTypeAccessHash,
        $insertName,
        $property
    ) {
        /** @var Repository $repository */
        $repository = self::getRepository($app, $dataType, $dataTypeAccessHash);
        $dataTypeDefinition = self::getDataTypeDefinition($app, $repository, $dataType, $dataTypeAccessHash);

        if ($repository && $dataTypeDefinition) {

            $formElementDefinition = self::getFormElementDefinition(
                $request,
                $dataTypeDefinition,
                $insertName,
                $property
            );

            if ($formElementDefinition) {
                if ($formElementDefinition->getFormElementType() == 'sequence') {
                    if ($request->query->has('insert') AND $request->query->has('count')) {
                        $inserts = $formElementDefinition->getInserts();
                        $insert = $request->query->get('insert');
                        $count = $request->query->get('count');

                        $clippingDefinition = $dataTypeDefinition->getClippingDefinition($insert);
                        $item = array();
                        $item['form'] = $app['form']->renderFormElements(
                            'form_sequence',
                            $clippingDefinition->getFormElementDefinitions(),
                            array(),
                            array(
                                'language' => $app['context']->getCurrentLanguage(),
                                'workspace' => $app['context']->getCurrentWorkspace(),
                            ),
                            'item_'.$count
                        );
                        $item['type'] = $insert;
                        $item['sequence'] = $count;
                        $item['title'] = $inserts[$insert];
                        $vars['item'] = $item;

                        $vars['inserts'] = $formElementDefinition->getInserts();

                        return $app['layout']->render('editsequence-additem.twig', $vars);
                    }
                }
            }
        }

        return '';
    }


    protected static function getRepository(Application $app, $dataType, $dataTypeAccessHash)
    {
        if ($dataType == 'content') {
            $repository = $app['repos']->getRepositoryByContentTypeAccessHash($dataTypeAccessHash);
        } else {
            $repository = $app['repos']->getRepositoryByConfigTypeAccessHash($dataTypeAccessHash);
        }

        if ($repository) {
            $app['context']->setCurrentRepository($repository);
        }

        return $repository;
    }


    protected static function getDataTypeDefinition(
        Application $app,
        Repository $repository,
        $dataType,
        $dataTypeAccessHash
    ) {
        /** @var RepositoryManager $repositoryManager */
        $repositoryManager = $app['repos'];

        if ($repository) {
            if ($dataType == 'content') {
                /** @var ContentTypeDefinition $dataTypeDefinition */
                $dataTypeDefinition = $repository->getContentTypeDefinition();
                $app['context']->setCurrentContentType($dataTypeDefinition);

            } else {
                /** @var ConfigTypeDefinition $dataTypeDefinition */
                $dataTypeDefinition = $repositoryManager->getConfigTypeDefinitionByConfigTypeAccessHash(
                    $dataTypeAccessHash
                );

                $app['context']->setCurrentConfigType($dataTypeDefinition);
            }

            return $dataTypeDefinition;
        }

        return false;

    }


    protected static function getFormElementDefinition(Request $request, $contentTypeDefinition, $insertName, $property)
    {
        $formElementDefinition = null;
        if ($insertName != '-') {
            $clippingDefinition = $contentTypeDefinition->getClippingDefinition($insertName);
            $formElementDefinition = $clippingDefinition->getFormElementDefinition($property);
            $formElementDefinition->setInsertedByInsert($insertName);

        } else {
            /* @var ViewDefinition */
            $viewDefinition = $contentTypeDefinition->getViewDefinition('default');
            if ($viewDefinition->hasProperty($property)) {

                /** @var FormElementDefinition $formElementDefinition */
                $formElementDefinition = $viewDefinition->getFormElementDefinition($property);
            }
        }

        return $formElementDefinition;
    }
}