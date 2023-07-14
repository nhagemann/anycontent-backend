<?php

namespace AnyContent\Backend\Helper;

use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;

class ConsolePrinter
{
    private OutputInterface $output;

    public function __construct()
    {
        $this->output = new ConsoleOutput();
        $this->initStyles();
    }

    public function getOutput(): OutputInterface
    {
        return $this->output;
    }

    public function writeln(string $message = '', bool $addEmptyLine = false)
    {
        $this->output->writeln($message);
        if ($addEmptyLine) {
            $this->output->writeln('');
        }
    }

    public function debug(string $message, bool $addEmptyLine = false)
    {
        $this->output->writeln(sprintf('<debug>%s</debug>', $message));
        if ($addEmptyLine) {
            $this->output->writeln('');
        }
    }

    private function initStyles(): void
    {
        $styles = [
            ['name' => 'debug', 'foreground' => '#999', 'background' => '', 'options' => []],
            ['name' => 'debug-strong', 'foreground' => '#F5F', 'background' => '', 'options' => []],
            ['name' => 'strong', 'foreground' => '#f07d00', 'background' => '', 'options' => []],
            ['name' => 'info', 'foreground' => '#424242', 'background' => '#f07d00', 'options' => []],
            ['name' => 'h1', 'foreground' => '#f07d00', 'background' => '#424242', 'options' => []],
            ['name' => 'h2', 'foreground' => '#424242', 'background' => '#cdcccc', 'options' => []],
        ];

        foreach ($styles as $style) {
            $outputStyle = new OutputFormatterStyle($style['foreground'], $style['background'], $style['options']);
            $this->output->getFormatter()->setStyle($style['name'], $outputStyle);
        }
    }

    public function info(string $message, bool $addEmptyLine = false)
    {
        $this->output->writeln(sprintf('<info>%s</info>', $message));
        if ($addEmptyLine) {
            $this->output->writeln('');
        }
    }

    public function h1(string $message)
    {
        $message = str_pad($message, 60, ' ', STR_PAD_BOTH);
        $this->output->writeln('');
        $this->output->writeln(sprintf('<h1>%s</h1>', str_repeat(' ', strlen($message))));
        $this->output->writeln(sprintf('<h1>%s</h1>', $message));
        $this->output->writeln(sprintf('<h1>%s</h1>', str_repeat(' ', strlen($message))));
        $this->output->writeln('');
    }

    public function h2(string $message)
    {
        $message = str_pad($message, 60, ' ', STR_PAD_BOTH);
        $this->output->writeln('');
        $this->output->writeln(sprintf('<h2>%s</h2>', $message));
        $this->output->writeln('');
    }

    public function error(string $message, bool $addEmptyLine = false)
    {
        $this->output->writeln(sprintf('<error>%s</error>', $message));
        if ($addEmptyLine) {
            $this->output->writeln('');
        }
    }
}
