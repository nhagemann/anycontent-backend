<?php

namespace AnyContent\Backend\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'anycontent:repositories',
    description: 'Lists all currently registered repositories and content types.',
    aliases: [],
    hidden: false
)]
class ListRepositoriesCommand extends Command
{


    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $app = $this->getSilexApplication();

        $repositories = $app['repos']->listRepositories();

        foreach ($repositories as $url => $repository)
        {
            $output->writeln('');
            $output->writeln(self::escapeGreen . $repository['title'] . self::escapeReset . ' (' . $url . ')');
            $contentTypes = $app['repos']->listContentTypes($url);

            foreach ($contentTypes as $contentTypeName => $contentType)
            {
                $output->writeln(self::escapeMagenta . $contentType['title'] . self::escapeReset . ' (' . $contentTypeName . ')');
            }
            $output->writeln('');
        }
        $output->writeln('');
        $output->writeln('');

    }
}