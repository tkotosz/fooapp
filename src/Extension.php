<?php

namespace Tkotosz\FooApp;

use Symfony\Component\Console\Application;

interface Extension
{
    public function addCommands(Application $application): void;
}