# Environments management
Today's product development lifecycle has many stages, which can include feature development, autotests, quality assurance (manual testing), demo for product owners on staging environment, and finally, deploy to production. It's considered good practice to keep all your environments similar, so if you are using Kubernetes for production - it's a good idea if your QA environment, staging etc. use Kubernetes too. If that is your case - you probably need slightly different manifests for each environment. For example:

- you need different credentials to your databases and third-party services;
- you may allocate many resources (cpu and memory) for your container's in production due to high load, but you don't want to overpay for the same resources in QA environment, and probably you want to give as little resources as possible to your workloads in your dev environment, if it resides on your laptop;
- some workloads should be running only on some environments.

Simplest way to manage environments for your manifests using K8S Framework is to generate different manifests depending on [Symfony environment](https://symfony.com/doc/current/configuration.html#configuration-environments) your generation command (like `bin/console k8s:dump:all`) was run with. Symfony derives it's environment from `APP_ENV` environment variable. This variable is also defined in your `.env` file if you've generated Symfony project by `symfony new` console command. When Symfony application is started, environment name is stored in `kernel.environment` [DI parameter](https://symfony.com/doc/current/configuration.html#accessing-configuration-parameters). 

!!! tip Tip
    If you don't understand concept behind `.env` file, please read about that in [Symfony docs](https://symfony.com/doc/current/configuration.html#configuring-environment-variables-in-env-files).

Generally, if you use Symfony environment as environment for your manifests, you have **two main options** to generate different manifests for different envs.

## Using configuration files
**First option** is to use [Symfony configuration environments](https://symfony.com/doc/current/configuration.html#configuration-environments) to define different values for different environments. You then may [access](https://symfony.com/doc/current/configuration.html#accessing-configuration-parameters) and use your configuration values in manifest classes. Short info on configuring your manifests is located in [Manifests](concepts/manifests.md#manifest-configuration) article. 

!!! tip "Tip"
    If you are writing [reusable app](reusable-apps.md) and want to distribute it - you probably want to create your own configuration structure for that app. [Dedicated article](reusable-apps.md) contains all needed information and links to Symfony docs for that matter.

The downside of this solution is that your manifest classes are not descriptive - you just return some configuration values, and you cannot tell what are that values just by looking at your class. For example, let's say you have `ExampleDeployment` class, and you are defining resources for it using configuration values:

```php title="ExampleDeployment.php"
class ExampleDeployment extends AbstractContainerDeployment
{
    public function resources(ContainerResourcesInterface $resources): void
    {
        $config = $this->config;
        $resources
            ->requestCPU(CPU::millicores($config['cpu']['requests']))
            ->requestMemory(Memory::mebibytes($config['memory']['requests']))
            ->limitCPU(CPU::millicores($config['cpu']['limits']))
            ->limitMemory(Memory::mebibytes($config['memory']['limits']))
        ;
    }
    
    //...
}

```

What can you say about this deployment's resources consumption without generating YAML from it and looking into it? Probably nothing. In order to know real values, you must look at Symfony configuration file for the corresponding environment. But K8S Framework was designed for you to write self-descriptive manifest classes, so below is our **second option**.

## Using env-suffixed methods
**Second option** to manage your envs is to use *env-suffixed methods*. This option is a recomended way to manage environments for your manifests, unless you are writing [reusable app](reusable-apps.md), in which case you need to give your users a way to configure it.

*Env-suffixed methods* technique allows you to create a copy of a method you want to modify for some environment, with a sufix of environment name. It's easier to demonstrate that than explain, so let's rewrite our `ExampleDeployment::resources()` method from above example to show you how it's done:

```php
class ExampleDeployment extends AbstractContainerDeployment
{
    public function resources(ContainerResourcesInterface $resources): void
    {
        $resources
            ->requestCPU(CPU::millicores(200))
            ->requestMemory(Memory::mebibytes(400))
            ->limitCPU(CPU::millicores(500))
            ->limitMemory(Memory::mebibytes(800))
        ;
    }

    public function resourcesProd(ContainerResourcesInterface $resources): void
    {
        $resources
            ->limitCPU(CPU::cores(2))
            ->limitMemory(Memory::gibibytes(2))
        ;
    }

    public function resourcesDev(ContainerResourcesInterface $resources): void
    {
        $resources
            ->requestCPU(CPU::millicores(50))
            ->requestMemory(Memory::mebibytes(100))
        ;
    }
    
    //...
}
```

Let's discuss what hapens here. Default method `resources()` will be called **always**, e.g. for any environment. If environment is `prod`, method `resourcesProd()` will be called **after** method `resources()` and thus will modify only cpu and memory limits, leaving requests as defined in `resources()`. If environment is `dev`, method `resourcesDev()` will be called after method `resources()` and will rewrite only cpu and memory requests, leaving limits as defined in `resources()`. We can check that by dumping manifests first with `prod` env, and then with `dev` env.

Dumping with `prod` env:
```bash
bin/console k8s:dump:all --env=prod
```

If we look at resulting manifest, we'll find that `resources` section looks like follows:

```yaml title="example.deployment.yaml"
# ...
resources:
    limits: { cpu: '2', memory: 2Gi }
    requests: { cpu: 200m, memory: 400Mi }
```

As we see, we have `requests` from original `resources()` method and `limits` from `resourcesProd()`.

And if we dump manifests by calling `bin/console k8s:dump:all --env=dev` and look at the `resources` section in `example.deployment.yaml`, it will look like follows:

```yaml title="example.deployment.yaml"
# ...
resources:
    limits: { cpu: 500m, memory: 800Mi }
    requests: { cpu: 50m, memory: 100Mi }
```

Again, we see `limits` from `resources()` method and `requests` from `resourcesDev()`.

So, the easiest way to define *env-suffuxed* method is to copy original method (`resources()` method in examples above) and paste it in your class, then add to it's name suffix with a name of the environment, for which you want this method to be called. Then just replace the body of the env-suffixed method with whatever modifications you want to do.

### Env-suffixed methods availability
By default only `resources()` and `replicas()` methods support env-suffixed variants.
But it's very easy to make any method you want to work like this. Just create [event subscriber](https://symfony.com/doc/current/components/event_dispatcher.html#using-event-subscribers) class that extends special abstract class `AbstractEnvAwareMethodSubscriber`, defined by K8S bundle. For example, if you want to use env-suffixed variants of method `containers()`, create such event subscriber:

```php title="ContainersMethodSubscriber.php"
class ContainersMethodSubscriber extends AbstractEnvAwareMethodSubscriber
{
    protected function methodName(): string
    {
        return 'containers';
    }

    protected function supports(ManifestMethodCalledEvent $event): bool
    {
        return $event->manifest() instanceof DeploymentInterface;
    }
}
```

Now you can use env-suffixed variants of `containers()` method in all your deployment classes:

```php title="ExampleDeployment.php"
class ExampleDeployment extends AbstractContainerDeployment
{
    public function containers(): iterable
    {
        yield $this;
        yield new NginxContainer();
    }

    public function containersProd(): iterable
    {
        yield $this;
        yield new NginxContainer();
        yield new MonitoringContainer();
    }
    
    //...
}

```

## Using methods interception for env management
Sometimes you want to set reasonable defaults for some environment without defining env-suffixed methods in every class. For example, you may want to set default resource requests for **all** your deployments to some minimal value for `dev` environment. You may use [methods interception](methods-interception.md) technique for that. If you haven't read about methods interception yet, please read that article before you continue to read this section. Let's create event subscriber that will set minimal resources for `dev` environment for all deployments that don't define them explicitly:

```php title="DevResourcesSubscriber.php" linenums="1"
#[When(env: 'dev')]
class DevResourcesSubscriber extends AbstractMethodResultSubscriber
{
    protected function supports(ManifestMethodCalledEvent $event): bool
    {
        return $event->manifest() instanceof AbstractContainerDeployment
            && 'resources' === $event->methodName();
    }

    protected function afterMethod(ManifestMethodCalledEvent $event): void
    {
        if (method_exists($event->manifest(), 'resourcesDev')) {
            // Resources for dev env are handled explicitly
            return;
        }

        /** @var ContainerResourcesInterface $resources */
        $resources = $event->methodParams()['resources'];
        $resources
            ->requestCPU(CPU::millicores(50))
            ->requestMemory(Memory::mebibytes(50))
        ;
    }
}
```

Now, every time we generate manifests for `dev` environment, all deployments will request 50 mebibytes of memory and 50 millicores, unless they defined their resources requests for `dev` env explicitly by defining method `resourcesDev()`.

!!! success "Interesting"
    Please notice PHP attribute `When` in line 1 above - this attribute comes with Symfony, and using it with our subscriber class ensures that this class will be registered in Symfony's [Dependency Injection Container](https://symfony.com/doc/current/components/dependency_injection.html) only when Symfony environment is `dev`. Thanks to that, we don't check current environment in subscriber code.

## Summary
Though a choice of environmants management method is a matter of taste, generally you should use *env-suffixed* methods, since it makes your manifest classes self-descriptive - you can see everything and for all environments just by looking at your class.

If you create [reusable app](reusable-apps.md), please package it as a Symfony bundle and create friendly configuration for it.

Use [methods interception](methods-interception.md) technique if you want to change some behavior in all manifests for some environment.
