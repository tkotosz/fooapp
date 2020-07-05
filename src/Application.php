<?php

namespace Tkotosz\FooApp;

use Symfony\Component\Console\Application as ConsoleApplication;
use Tkotosz\FooApp\Console\Command\AppUpdateCommand;
use Tkotosz\FooApp\Console\Command\ExtensionInstallCommand;
use Tkotosz\FooApp\Console\Command\ExtensionListCommand;
use Tkotosz\FooApp\Console\Command\ExtensionRemoveCommand;
use Tkotosz\FooApp\Console\Command\ExtensionSourceAddCommand;
use Tkotosz\FooApp\Console\Command\ExtensionSourceListCommand;
use Tkotosz\FooApp\Console\Command\ExtensionSourceRemoveCommand;
use Tkotosz\FooApp\Console\Command\GlobalCommand;
use Tkotosz\FooApp\Console\Command\GoodbyeWorldCommand;
use Tkotosz\FooApp\Console\Command\HelloWorldCommand;
use Tkotosz\CliAppWrapperApi\Api\V1\Application as ApplicationInterface;
use Tkotosz\CliAppWrapperApi\Api\V1\ApplicationManager;

class Application implements ApplicationInterface
{
    /** @var ApplicationManager */
    private $applicationManager;

    public function __construct(ApplicationManager $applicationManager)
    {
        $this->applicationManager = $applicationManager;
    }

    public function init(): int
    {
        // no-op
        return 0;
    }

    public function run(): void
    {
        $consoleApp = new ConsoleApplication('Foo App', '0.1.0');

        $consoleApp->add(new GlobalCommand($this->applicationManager));

        $consoleApp->add(new ExtensionSourceListCommand($this->applicationManager));
        $consoleApp->add(new ExtensionSourceAddCommand($this->applicationManager));
        $consoleApp->add(new ExtensionSourceRemoveCommand($this->applicationManager));

        $consoleApp->add(new ExtensionListCommand($this->applicationManager));
        $consoleApp->add(new ExtensionInstallCommand($this->applicationManager));
        $consoleApp->add(new ExtensionRemoveCommand($this->applicationManager));

        $consoleApp->add(new AppUpdateCommand($this->applicationManager));

        $consoleApp->add(new HelloWorldCommand());
        $consoleApp->add(new GoodbyeWorldCommand());

        foreach ($this->applicationManager->findInstalledExtensions() as $extension) {
            $extensionClass = $extension->extensionClass();
            $extension = new $extensionClass;
            $extension->addCommands($consoleApp);
        }

        $consoleApp->run();
    }
}