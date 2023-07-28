<?php

namespace AnyContent\Backend\Forms\FormElements\SequenceFormElement;

use AnyContent\Backend\Controller\AbstractAnyContentBackendController;
use AnyContent\Client\Repository;
use CMDL\ClippingDefinition;
use CMDL\DataTypeDefinition;
use CMDL\FormElementDefinition;
use CMDL\FormElementDefinitions\SequenceFormElementDefinition;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SequenceController extends AbstractAnyContentBackendController
{
    #[Route('/sequence/edit/{dataType}/{dataTypeAccessHash}/{viewName}/{insertName}/{recordId}/{property}', name:'anycontent_sequence_edit', methods: ['GET'])]
    public function editSequence(
        Request $request,
        $dataType,
        $dataTypeAccessHash,
        $viewName,
        $insertName,
        $property,
        ?string $recordId
    ) {
        $vars = [];
        $vars['action']['submit'] = $this->generateUrl(
            'anycontent_sequence_post',
            [
                'dataType' => $dataType,
                'dataTypeAccessHash' => $dataTypeAccessHash,
                'viewName' => 'default',
                'insertName' => $insertName,
                //'recordId' => $recordId,
                'property' => $property,
            ]
        );
        $vars['action']['add'] = $this->generateUrl(
            'anycontent_sequence_add',
            [
                'dataType' => $dataType,
                'dataTypeAccessHash' => $dataTypeAccessHash,
                //'viewName' => 'default',
                'insertName' => $insertName,
                'property' => $property,
            ]
        );

        $vars['property'] = $property;

        $repository = $this->getRepository($dataType, $dataTypeAccessHash);

        $dataTypeDefinition = $this->getDataTypeDefinition($repository, $dataType, $dataTypeAccessHash);

        // set recordId to null for sequences of not yet stored records
        if ($recordId === '-') {
            $recordId = null;
        }

        if ($repository && $dataTypeDefinition) {
            $repository->selectWorkspace($this->contextManager->getCurrentWorkspace());
            $repository->selectLanguage($this->contextManager->getCurrentLanguage());
            $repository->setTimeShift($this->contextManager->getCurrentTimeShift());
            $repository->selectView($viewName);

            $vars['definition'] = $dataTypeDefinition;

            $formElementDefinition = $this->getFormElementDefinition(
                $request,
                $dataTypeDefinition,
                $insertName,
                $property
            );

            if ($formElementDefinition) {
                if ($formElementDefinition instanceof SequenceFormElementDefinition) {
                    $sequence = [];

                    if ($dataType === 'content') {
                        if ($recordId) {
                            $record = $repository->getRecord((int)$recordId);
                            $sequence = $record->getProperty($property, []);
                        } else {
                            if (is_array($formElementDefinition->getDefaultValue())) {
                                $sequence = $formElementDefinition->getDefaultValue();
                            }
                        }

                        if (is_string($sequence)) {
                            $sequence = json_decode($sequence, true);
                            if (json_last_error() != JSON_ERROR_NONE or !is_array($sequence)) {
                                $sequence = [];
                            }
                        }
                    }

                    if ($dataType === 'config') {
                        $config = $repository->getConfig($dataTypeDefinition->getName());

                        $sequence = $config->getProperty($property, []);

                        if (is_string($sequence)) {
                            $sequence = json_decode($sequence, true);
                            if (json_last_error() != JSON_ERROR_NONE or !is_array($sequence)) {
                                $sequence = [];
                            }
                        }
                    }

                    $vars['count'] = count($sequence);
                    $vars['items'] = [];

                    $inserts = $formElementDefinition->getInserts();

                    $vars['inserts'] = $inserts;

                    // silently render all potential inserts to add their Javascript-Files to the Layout
//                    foreach (array_keys($inserts) as $k) {
//                        $clippingDefinition = $dataTypeDefinition->getClippingDefinition($k);
//                        $this->formManager->renderFormElements(
//                            'form_sequence',
//                            $clippingDefinition->getFormElementDefinitions(),
//                            [],
//                            [
//                                'language' => $this->contextManager->getCurrentLanguage(),
//                                'workspace' => $this->contextManager->getCurrentWorkspace(),
//                            ],
//                            null
//                        );
//                    }

                    $i = 0;
                    foreach ($sequence as $item) {
                        $insert = key($item);
                        $properties = array_shift($item);

                        if (
                            $dataTypeDefinition->hasClippingDefinition(
                                $insert
                            )
                        ) { // ignore eventually junk data after cmdl changes
                            $i++;

                            /** @var ClippingDefinition $clippingDefinition */
                            $clippingDefinition = $dataTypeDefinition->getClippingDefinition($insert);
                            $item = [];
                            $item['form'] = $this->formManager->renderFormElements(
                                'form_sequence',
                                $clippingDefinition->getFormElementDefinitions(),
                                $properties,
                                [
                                    'language' => $this->contextManager->getCurrentLanguage(),
                                    'workspace' => $this->contextManager->getCurrentWorkspace(),
                                ],
                                'item_' . $i
                            );
                            $item['type'] = $insert;
                            $item['title'] = $inserts[$insert];
                            $item['sequence'] = $i;
                            $vars['items'][] = $item;
                        }
                    }

                    return $this->render('@AnyContentBackend/Forms/Sequence/editsequence.html.twig', $vars);
                }
            }
        }

        return new Response('Error getting repository from dataTypeAccessHash.');
    }

    #[Route('/sequence/edit/{dataType}/{dataTypeAccessHash}/{viewName}/{insertName}/{property}', name:'anycontent_sequence_post', methods: ['POST'])]
    public function postSequence(
        Request $request,
        $dataType,
        $dataTypeAccessHash,
    ) {
        $repository = $this->getRepository($dataType, $dataTypeAccessHash);

        $dataTypeDefinition = $this->getDataTypeDefinition($repository, $dataType, $dataTypeAccessHash);

        if ($repository && $dataTypeDefinition) {
            $items = [];
            foreach ($request->request->getIterator() as $key => $value) {
                $split = explode('_', $key);

                if (count($split) >= 3) {
                    if ($split[0] == 'item') {
                        $nr = (int)($split[1]);
                        $l = strlen('item_' . $nr);
                        $property = substr($key, $l + 1);
                        $items[$nr][$property] = $value;
                    }
                }
            }

            $sequence = [];
            if ($request->request->has('sequence')) {
                $types = $request->get('type');
                $i = 0;
                foreach ($request->get('sequence') as $nr) {
                    $item = $items[$nr];
                    $type = $types[$i];

                    $clippingDefinition = $dataTypeDefinition->getClippingDefinition($type);

                    $bag = new ParameterBag();
                    $bag->add($item);

                    $item = $this->formManager->extractFormElementValuesFromPostRequest(
                        $bag,
                        $clippingDefinition->getFormElementDefinitions(),
                        []
                    );

                    $sequence[] = [$type => $item];
                    $i++;
                }
            }

            return new JsonResponse(['sequence' => $sequence]);
        }
    }

    #[Route('/sequence/add/{dataType}/{dataTypeAccessHash}/{insertName}/{property}', 'anycontent_sequence_add', methods: ['GET'])]
    public function addSequenceItem(
        Request $request,
        $dataType,
        $dataTypeAccessHash,
        $insertName,
        $property
    ) {
        $repository = $this->getRepository($dataType, $dataTypeAccessHash);
        $dataTypeDefinition = $this->getDataTypeDefinition($repository, $dataType, $dataTypeAccessHash);

        if ($repository && $dataTypeDefinition) {
            $formElementDefinition = $this->getFormElementDefinition(
                $request,
                $dataTypeDefinition,
                $insertName,
                $property
            );

            if ($formElementDefinition) {
                if ($formElementDefinition instanceof SequenceFormElementDefinition) {
                    if ($request->query->has('insert') and $request->query->has('count')) {
                        $vars = [];
                        $inserts = $formElementDefinition->getInserts();
                        $insert = $request->query->get('insert');
                        $count = $request->query->get('count');

                        $clippingDefinition = $dataTypeDefinition->getClippingDefinition($insert);
                        $item = [];
                        $item['form'] = $this->formManager->renderFormElements(
                            'form_sequence',
                            $clippingDefinition->getFormElementDefinitions(),
                            [],
                            [
                                'language' => $this->contextManager->getCurrentLanguage(),
                                'workspace' => $this->contextManager->getCurrentWorkspace(),
                            ],
                            'item_' . $count
                        );
                        $item['type'] = $insert;
                        $item['sequence'] = $count;
                        $item['title'] = $inserts[$insert];
                        $vars['item'] = $item;

                        $vars['inserts'] = $formElementDefinition->getInserts();

                        return $this->render('@AnyContentBackend/Forms/Sequence/editsequence-additem.html.twig', $vars);
                    }
                }
            }
        }

        return new Response('');
    }

    protected function getRepository($dataType, $dataTypeAccessHash): ?Repository
    {
        if ($dataType == 'content') {
            $repository = $this->repositoryManager->getRepositoryByContentTypeAccessHash($dataTypeAccessHash);
        } else {
            $repository = $this->repositoryManager->getRepositoryByConfigTypeAccessHash($dataTypeAccessHash);
        }

        if ($repository) {
            $this->contextManager->setCurrentRepository($repository);
        }

        return $repository;
    }

    protected function getDataTypeDefinition(
        Repository $repository,
        $dataType,
        $dataTypeAccessHash
    ): ?DataTypeDefinition {
        if ($dataType == 'content') {
            $dataTypeDefinition = $repository->getContentTypeDefinition();
            $this->contextManager->setCurrentContentType($dataTypeDefinition);
        } else {
            $dataTypeDefinition = $this->repositoryManager->getConfigTypeDefinitionByConfigTypeAccessHash($dataTypeAccessHash);

            $this->contextManager->setCurrentConfigType($dataTypeDefinition);
        }

        return $dataTypeDefinition;
    }

    protected function getFormElementDefinition(Request $request, $contentTypeDefinition, $insertName, $property): ?FormElementDefinition
    {
        $formElementDefinition = null;
        if ($insertName != '-') {
            $clippingDefinition = $contentTypeDefinition->getClippingDefinition($insertName);
            $formElementDefinition = $clippingDefinition->getFormElementDefinition($property);
            $formElementDefinition->setInsertedByInsert($insertName);
        } else {
            $viewDefinition = $contentTypeDefinition->getViewDefinition('default');
            if ($viewDefinition->hasProperty($property)) {
                $formElementDefinition = $viewDefinition->getFormElementDefinition($property);
            }
        }

        return $formElementDefinition;
    }
}
