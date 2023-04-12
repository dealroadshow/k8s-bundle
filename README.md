# Dealroadshow K8S Bundle
This bundle integrates [Dealroadshow K8S framework](https://github.com/dealroadshow/k8s-framework) 
with Symfony framework. 

## Installation

Use Composer to install this bundle into your Symfony application:

```bash
composer require dealroadshow/k8s-bundle
```

## Basic usage

Let's generate your first Kubernetes App (you may think of App like of Helm chart):

```bash
bin/console k8s:generate:app example
```

This command will create `src/K8S/Example` directory and
`App\K8S\Example\ExampleApp` PHP class.

App is an abstraction, which exists in order to group together some Kubernetes manifests,
like Deployments, CronJobs or ConfigMaps.

Now that we have a project and an app, we can start to define our Kubernetes manifests.
Let's start by creating a deployment:

```bash
bin/console k8s:generate:manifest nginx deployment example
```

After executing this command you'll see a new class 
`App\K8S\Example\Manifests\Nginx\NginxDeployment`.

This new class may look like follows:

```php
<?php

namespace App\K8S\Example\Manifests\Nginx;

use Dealroadshow\K8S\Framework\Core\Deployment\AbstractDeployment;
use Dealroadshow\K8S\Framework\Core\LabelSelector\SelectorConfigurator;
use Dealroadshow\K8S\Framework\Core\MetadataConfigurator;
use Dealroadshow\K8S\Framework\Core\Pod\Containers\PodContainers;

class NginxDeployment extends AbstractDeployment
{
    public function selector(SelectorConfigurator $selector): void
    {
    }

    public static function shortName(): string
    {
        return 'nginx';
    }

    public function fileNameWithoutExtension(): string
    {
        return 'nginx.deployment';
    }

    public function containers(PodContainers $containers): void
    {
    }
}
```

Let's start by implementing some of existing methods and some others, from basic class:

```php
<?php

namespace App\K8S\Example\Nginx;

use Dealroadshow\K8S\Framework\Core\Container\AbstractContainer;
use Dealroadshow\K8S\Framework\Core\Container\Env\EnvConfigurator;
use Dealroadshow\K8S\Framework\Core\Container\Image\Image;
use Dealroadshow\K8S\Framework\Core\Deployment\AbstractDeployment;
use Dealroadshow\K8S\Framework\Core\Container\Resources\CPU;
use Dealroadshow\K8S\Framework\Core\Container\Resources\Memory;
use Dealroadshow\K8S\Framework\Core\Container\Resources\ResourcesConfigurator;
use Dealroadshow\K8S\Framework\Core\LabelSelector\SelectorConfigurator;
use Dealroadshow\K8S\Framework\Core\MetadataConfigurator;
use Dealroadshow\K8S\Framework\Core\Pod\Containers\PodContainers;

class NginxDeployment extends AbstractDeployment
{
    public function selector(SelectorConfigurator $selector): void
    {
        $selector
            ->addLabel('app', 'example-app')
            ->addLabel('component', 'my-first-deployment')
        ;
    }

    public static function shortName(): string
    {
        return 'nginx';
    }

    public function fileNameWithoutExtension(): string
    {
        return 'nginx.deployment';
    }

    public function containers(PodContainers $containers): void
    {
        $container = new class extends AbstractContainer {
            public function name(): string
            {
                return 'nginx';
            }

            public function image(): Image
            {
                return Image::fromName('nginx');
            }
            
            public function resources(ResourcesConfigurator $resources): void
            {
                $resources
                    ->requestCPU(CPU::millicores(500))
                    ->limitCPU(CPU::cores(2))
                    ->requestMemory(Memory::mebibytes(128))
                    ->limitMemory(Memory::mebibytes(256))
                ;
            }

            public function env(EnvConfigurator $env): void
            {
                $env
                    ->var('APP_NAME', 'nginx-example-app')
                ;
            }
        };
        
        $containers->add($container);
    }
}
```


We've defined a basic deployment, and we can generate Yaml manifest from it:

```bash
bin/console k8s:dump:all
```

Now you can see your Yaml manifest in `Resources/k8s-manifests` directory
inside your project. Nice!
