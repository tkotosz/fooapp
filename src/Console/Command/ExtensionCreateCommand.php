<?php

namespace Tkotosz\FooApp\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Tkotosz\CliAppWrapperApi\Api\V1\ApplicationManager;

class ExtensionCreateCommand extends Command
{
    /** @var ApplicationManager */
    private $applicationManager;

    public function __construct(ApplicationManager $applicationManager)
    {
        $this->applicationManager = $applicationManager;
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('extension:create')
            ->setDescription('Create New Extension')
            ->addArgument('package_name', InputArgument::REQUIRED, 'Package Name (<vendor>/<name>)')
            ->addArgument('path', InputArgument::REQUIRED, 'Path to save the extension')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $packageName = $input->getArgument('package_name');
        $appVersion = '*';
        $extensionNamespace = implode('\\\\', array_map('ucfirst', explode('/', implode('', array_map('ucfirst', explode('-', str_replace('fooapp-', 'FooApp/', $packageName)))))));
        $extensionClassName  = array_reverse(explode('\\\\', $extensionNamespace))[0];

        $path = $input->getArgument('path');
        if (!(strpos($path, '~') === 0 || strpos($path, '/') === 0)) {
            $path = getcwd() . DIRECTORY_SEPARATOR . $path;
        }

        $composerJson = <<<FOOAPPEXT
{
    "name": "$packageName",
    "type": "tkotosz-fooapp-extension",
    "license": "MIT",
    "require": {
        "tkotosz/fooapp-src": "$appVersion"
    },
    "autoload": {
        "psr-4": {
            "$extensionNamespace\\\\": "src"
        }
    },
    "minimum-stability": "dev",
    "prefer-stable": true,
    "extra": {
        "tkotosz-fooapp-extension-class": "$extensionNamespace\\\\$extensionClassName"
    }
}
FOOAPPEXT;

        $extensionClassFile = <<<'EXTFILE'
<?php

namespace %s;

use Symfony\Component\Console\Application;
use Tkotosz\FooApp\Extension;

class %s implements Extension
{
    public function addCommands(Application $application): void
    {
        // TODO
    }
}
EXTFILE;

        @mkdir($path, 0777, true);
        file_put_contents($path . DIRECTORY_SEPARATOR . 'composer.json', $composerJson);
        @mkdir($path  . DIRECTORY_SEPARATOR . 'src');
        file_put_contents(
            $path . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . $extensionClassName . '.php',
            sprintf($extensionClassFile, str_replace('\\\\', '\\', $extensionNamespace), $extensionClassName)
        );

        return 0;
    }
}
