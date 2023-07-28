<?php

namespace AnyContent\Backend\Forms\FormElements\FileFormElements;

use AnyContent\Backend\Forms\FormElements\FormElementDefault;
use AnyContent\Backend\Services\ContextManager;
use CMDL\FormElementDefinitions\FileFormElementDefinition;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment;

class FormElementFile extends FormElementDefault
{
    /** @var  FileFormElementDefinition */
    protected $definition;

    protected string $type = 'file';
    protected string $template = '@AnyContentBackend/Forms/formelement-file.html.twig';

    public function __construct(
        private ContextManager $contextManager,
        private UrlGeneratorInterface $urlGenerator
    ) {
    }

    public function render(Environment $twig)
    {
        $info = pathinfo($this->getValue());

        if (isset($info['dirname'])) {
            $path = $info['dirname'];
        } else {
            $path = $this->definition->getPath();
        }

        $path = '/' . trim($path, '/');

        $this->vars['url_modal'] = $this->urlGenerator->generate(
            'anycontent_files_select_modal',
            ['repositoryAccessHash' => $this->contextManager->getCurrentRepositoryAccessHash(), 'path' => $path]
        );

        $this->vars['url_view'] = rtrim(
            $this->urlGenerator->generate(
                'anycontent_file_view',
                ['repositoryAccessHash' => $this->contextManager->getCurrentRepositoryAccessHash(), 'id' => '/']
            ),
            '/'
        ) . '/';

        $this->vars['url_download'] = rtrim(
            $this->urlGenerator->generate(
                'anycontent_file_download',
                ['repositoryAccessHash' => $this->contextManager->getCurrentRepositoryAccessHash(), 'id' => '/']
            ),
            '/'
        ) . '/';

        $this->vars['preview'] = false;
        $id = $this->getValue();
        if ($id != '') {
            $type = strtolower(pathinfo($id, PATHINFO_EXTENSION));
            if (in_array($type, ['jpg', 'jpeg', 'gif', 'png'])) {
                $this->vars['preview'] = true;
                $repository = $this->contextManager->getCurrentRepository();
                $fileManager = $repository->getFileManager();
                if ($fileManager) {
                    if ($fileManager->getPublicUrl() != '') {
                        $this->vars['url_view'] = trim($fileManager->getPublicUrl(), '/') . '/';
                    }
                }
            }
        }

        return parent::render($twig);
    }
}
