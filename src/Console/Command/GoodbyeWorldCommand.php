<?php

namespace Tkotosz\FooApp\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GoodbyeWorldCommand extends Command
{
    protected function configure()
    {
        $this->setName('goodbye:world');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Goodbye World');

        return 0;
    }
}
