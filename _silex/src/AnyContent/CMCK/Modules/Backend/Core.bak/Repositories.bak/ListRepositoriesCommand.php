<?php

namespace AnyContent\CMCK\Modules\Backend\Core\Repositories;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ListRepositoriesCommand extends \AnyContent\CMCK\Modules\Backend\Core\Application\Command
{

    protected function configure()
    {
        $this->setName('cmck:list')
             ->setDescription('Lists all currently registered repositories and content types.');

    }


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