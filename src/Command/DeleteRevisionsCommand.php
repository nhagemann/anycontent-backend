<?php

namespace AnyContent\Backend\Command;

use AnyContent\Backend\Helper\ConsolePrinter;
use AnyContent\Backend\Services\RepositoryManager;
use AnyContent\Client\Repository;
use AnyContent\Connection\Interfaces\RevisionWriteConnection;
use CMDL\ConfigTypeDefinition;
use CMDL\ContentTypeDefinition;
use CMDL\DataTypeDefinition;
use DateTime;
use RuntimeException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\ConfirmationQuestion;

#[AsCommand(
    name: 'anycontent:revisions:delete',
    description: 'Deletes revisions in MySQL Schemaless repositories.',
    aliases: [],
    hidden: false
)]
class DeleteRevisionsCommand extends Command
{
    private InputInterface $input;
    private OutputInterface $output;
    private QuestionHelper $questionHelper;

    public function __construct(
        private RepositoryManager $repositoryManager,
        private ConsolePrinter $printer
    ) {
        parent::__construct(null);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->input = $input;
        $this->output = $output;
        $questionHelper = $this->getHelper('question');
        assert ($questionHelper instanceof QuestionHelper);
        $this->questionHelper = $questionHelper;

        try {
            $this->printer->h1('AnyContent Revision Deleter');
            $this->printer->h2('Configuration');

            $repository = $this->selectRepository();
            $this->printer->writeln();
            $dataTypes = $this->selectDataTypes($repository);
            $this->printer->writeln();
            $untilDate = $this->selectUntilDate();

            $this->printer->h2('Executing');

            $connection = $repository->getWriteConnection();
            assert($connection instanceof RevisionWriteConnection);

            foreach ($dataTypes as $dataTypeDefinition) {
                if ($dataTypeDefinition instanceof ContentTypeDefinition) {
                    $this->printer->writeln(sprintf('Deleting revisions of content type <strong>%s</strong>.', $dataTypeDefinition->getName()));
                    $connection->truncateContentTypeRevisions($dataTypeDefinition, $untilDate);
                }
                if ($dataTypeDefinition instanceof ConfigTypeDefinition) {
                    $this->printer->writeln(sprintf('Deleting revisions of config type <strong>%s</strong>.', $dataTypeDefinition->getName()));
                }
            }
        } catch (RuntimeException $exception) {
            $this->printer->error($exception->getMessage());
            return Command::SUCCESS;
        }
        $this->printer->writeln();

        return Command::SUCCESS;
    }

    private function selectRepository(): Repository
    {
        $repositories = $this->repositoryManager->listRepositories();

        $selectables = [];
        $selectables[] = new Selectable('>>> quit <<<', null);

        foreach ($repositories as $repositoryInfo) {
            $repository = $this->repositoryManager->getRepositoryByRepositoryAccessHash($repositoryInfo['accessHash']);
            if ($repository->getWriteConnection() instanceof RevisionWriteConnection) {
                $selectables[] = new Selectable($repositoryInfo['title'], $repository);
            }
        }

        if (count($selectables) === 1) {
            throw new RuntimeException('Did not find any repository with revisions.');
        }

        $question = new ChoiceQuestion(
            'Please select repository to delete revisions from:',
            $selectables,
            0
        );

        /** @var Selectable $selected */
        $selected = $this->questionHelper->ask($this->input, $this->output, $question);

        if (!$selected->getObject() instanceof Repository) {
            throw new RuntimeException('Exiting.');
        }

        return $selected->getObject();
    }

    private function selectDataTypes(Repository $repository): array
    {
        $selectables = [];
        $selectables[] = new Selectable('>>> quit <<<', null);

        foreach ($repository->getContentTypeDefinitions() as $contentTypeDefinition) {
            $selectables[] = new Selectable($contentTypeDefinition->getTitle() ?: $contentTypeDefinition->getName(), $contentTypeDefinition);
        }

        foreach ($repository->getConfigTypeDefinitions() as $configTypeDefinition) {
            $selectables[] = new Selectable(sprintf('%s (Config)', $configTypeDefinition->getTitle() ?: $configTypeDefinition->getName()), $configTypeDefinition);
        }

        if (count($selectables) === 1) {
            throw new RuntimeException('Repository did not contain any data types.');
        }

        $question = new ChoiceQuestion(
            'Please select data type(s) to delete revisions from:',
            $selectables,
            0
        );
        $question->setMultiselect(true);

        $result = [];
        /** @var Selectable[] $selected */
        $selected = $this->questionHelper->ask($this->input, $this->output, $question);
        foreach ($selected as $selection) {
            if (!$selection->getObject() instanceof DataTypeDefinition) {
                throw new RuntimeException('Exiting.');
            }
            $result[] = $selection->getObject();
        }
        return $result;
    }

    private function selectUntilDate(): DateTime
    {
        $options = [
            '-1 second',
            '-1 minute',
            '-1 hour',
            '-1 day',
            '-1 month',
            '-3 month',
            '-1 year',
        ];
        $selectables = [];
        $selectables[] = new Selectable('>>> quit <<<', null);

        foreach ($options as $option) {
            $selectables[] = new Selectable($option, new DateTime($option));
        }

        $question = new ChoiceQuestion(
            'Please select from when to keep revision:',
            $selectables,
            0
        );
        $selected = $this->questionHelper->ask($this->input, $this->output, $question);

        if (!$selected->getObject() instanceof DateTime) {
            throw new RuntimeException('Exiting.');
        }
        $untilDate = $selected->getObject();

        $this->printer->writeln();
        $this->printer->info(sprintf('Selected Until Date: %s', $untilDate->format('d.m.Y H:i:s')));
        $this->printer->writeln();
        $question = new ConfirmationQuestion('Continue (y/n)?', false);

        if (!$this->questionHelper->ask($this->input, $this->output, $question)) {
            throw new RuntimeException('Exiting.');
        }

        return $untilDate;
    }
}
