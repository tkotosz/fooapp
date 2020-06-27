#!/bin/env php
<?php

$config = json_decode(file_get_contents('cli-app-wrapper-config.json'), true);

@mkdir('build-tmp');
@mkdir('build-tmp/bin');


$appConfig = $config;
unset($appConfig['build_target']);
file_put_contents('build-tmp/config.php', sprintf('<?php return %s;', var_export($appConfig, true)));


$boxConfig = [
    'compactors' => [
        'KevinGH\Box\Compactor\Php',
        'KevinGH\Box\Compactor\PhpScoper'
    ],
    'php-scoper' => 'scoper.inc.php',
    'dump-autoload' => true,
    'output' => '../' . $config['build_target'],
    'compression' => 'GZ'
];

file_put_contents('build-tmp/box.json', json_encode($boxConfig, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));


$scoperConfig = <<<'SCOPERCONFIG'
<?php

return [
    'patchers' => [
        function ($filePath, $prefix, $contents) {
            return str_replace(
                '\\'.$prefix.'\Composer\Autoload\ClassLoader',
                '\Composer\Autoload\ClassLoader',
                $contents
            );
        },
    ],
    'files-whitelist' => ['config.php'],
    'whitelist' => [
        'Tkotosz\CliAppWrapperApi\*',
        'Composer\Autoload\ClassLoader'
    ]
];
SCOPERCONFIG;

file_put_contents('build-tmp/scoper.inc.php', $scoperConfig);


$composerConfig = <<<'COMPOSERCONFIG'
{
    "name": "build/wrapped-app",
    "require": {
        "tkotosz/cli-app-wrapper": "*"
    },
    "bin": ["bin/wrapped-app"],
    "minimum-stability": "dev",
    "prefer-stable": true
}
COMPOSERCONFIG;

file_put_contents('build-tmp/composer.json', $composerConfig);

$makeFile = <<<MAKEFILE
build:
\tcomposer install
\tbox compile
MAKEFILE;

file_put_contents('build-tmp/Makefile', $makeFile);


$wrappedApp = <<<'WRAPPEDAPP'
#!/bin/env php
<?php

use Tkotosz\CliAppWrapper\CliAppWrapper;
use Tkotosz\CliAppWrapperApi\Api\V1\Model\ApplicationConfig;

require __DIR__ . '/../vendor/autoload.php';

(new CliAppWrapper)
    ->createWrappedApplication(ApplicationConfig::fromArray(require __DIR__ . '/../config.php'))
    ->run();
WRAPPEDAPP;

file_put_contents('build-tmp/bin/wrapped-app', $wrappedApp);


system('cd build-tmp && make build && cd -');

echo "DONE" . PHP_EOL;