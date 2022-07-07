# Containers
Containers are the heart of any [workload](https://kubernetes.io/docs/concepts/workloads/). No matter what workload type are you dealing with ([Deployment](../core/deployment.md), [Job](../core/job.md), [StatefulSet](../core/stateful-set.md) etc.), you'll have to define containers for it - but the good news is that container API object is the same for any workload, so you'll need to learn it once and use in any manifest.

All containers in K8S Framework must implement `ContainerInterface`. Containers returned from method `containers()` in workload. You may return array of them, or (which looks better) use `yield` keyword, for examle:

```php
public function containers(): iterable
{
    yield new PhpFpmContainer();
    yield new NginxContainer();
}

```

Also every workload type has `Container`-prefixed abstract class, which is a recommended way of writing your manifest: for example, there is `AbstractDeplyment` class that you may inherit, but there is also `Container`-prefixed class `AbstractContainerDeployment`, which implements both `DeploymentInterface` and `ContainerInterface` - by inheriting this class, you'll make your manifest much more descriptive - define your "main" container methods in deployment class itself, and if you also need some auxiliary containers - rewrite `containers()` method amd return them as well.

For example, if your deployment `ExampleDeployment` has `php-fpm` and `nginx` containers - just extend `AbstractContainerDeployment` class, define `php-fpm` container method in `ExampleDeployment` itself (since `php-fpm` holds your application code and may be considered as "main" container), and that rewrite your `containers()` method as follows:

```php
class ExampleDeployment extends AbstractContainerDeployment
{
    public function containers(): iterable
    {
        yield $this;
        yield new NginxContainer();
    }
    
    //...
}

```

Separate container classes, like `NginxContainer` from example above, should be defined in `Container` namespace inside your app. 

!!! tip "Tip"
    App's directory structure is explained in [Apps](apps.md) article.

Since every container class must implement `ContainerInterface`, there is an abstract class `AbstractContainer` for you, which helps you with that by defining most of the methods from `ContainerInterface` with reasonable defaults. You then just have to modify what should be modified:

```php
namespace App\K8S\Hello\Container;

use Dealroadshow\K8S\Framework\Core\Container\AbstractContainer;
use Dealroadshow\K8S\Framework\Core\Container\Image\Image;

class NginxContainer extends AbstractContainer
{
    public function image(): Image
    {
        return Image::fromName('nginx');
    }
}

```

This is a perfectly valid container class. `AbstractContainer` requires from you to define only `image()` method. 

## Container images
All containers are created from images, so every container needs one. Defining image your container will use is as simple as defining `image()` method in your manifest, as shown in the example above. As you see, this method must return instance of `Image` class. `Image` is a pretty easey to use value-object. Basically, you can just use one of the static constructors: `Image::fromName()` or `Image::fromString()`. The difference between two is that `Image::fromString` expects full image information as parameter - along with repository url, image tag and so on. For example, if you image is stored in [AWS ECR](https://aws.amazon.com/ecr/) and you want to deploy tag `1.2.3` of your `my-organization/cool-application` image, your method `image()` may look as follows:

```php
public function image(): Image
{
    return Image::fromString(
        '49367101234912.dkr.ecr.us-east-1.amazonaws.com/my-organization/cool-application:1.2.3'
    );
}

```

If you prefer using `Image::fromName()` constructor, this is how method `image()` would like if you want to return the same image as above:

```php
public function image(): Image
{
    return Image::fromName('cool-application')
        ->setPrefix('my-organization')
        ->setTag('1.2.3')
        ->setRegistryUrl('49367101234912.dkr.ecr.us-east-1.amazonaws.com');
}
```

But the one thing that may come to your mind, is "It's not cool to modify `image()` method to change tag every time I need to deploy new version of my application". And you are absolutely right. This problem above is pretty easy to solve by using environment variable in code and passing this variable from whatever CI/CD tool you use. You may write `setTag(getenv('MY_APPLICATION_TAG'))` instead of `setTag(1.2.3)` in your code. But even after that - if you use Kubernetes, there are probably plenty of different images built in your organization, so you'll need to repeat `setRegistryUrl()` and `setPrefix()` parts many times. There is a solution in K8S Framework, specifically created to address issues like that. If you want to want to avoid such repetitiveness in your code - please read [Images Middleware](images-middleware.md).

## Defining environment variables for container

Passing environment variables to your containers are one of the most often used features in Kubernetes manifests. All environment variables for your container are defined in `env()` method. Just after your IDE have generated this method for you, it should look like follows:

```php
public function env(EnvConfigurator $env): void
{
}

```

To define single env variable - use `$env->var()` method:

### Simple env variable

```php
public function env(EnvConfigurator $env): void
{
    $env
        ->var('VARIABLE_NAME', 'SOME VALUE')
        ->var('FOO', 'Bar!');
}

```

In Kubernetes manifests you can also define env variable that gets it's value from a plenty of different sources:

### Env variable from ConfigMap or Secret
Env variable may get it's value from some key in [ConfigMap](../core/configmap.md) or [Secret](../core/secret.md). K8S Framework follows simple rule: **You don't deal with manifest names in manifest classes**, since simple typo in name of resource you want to use will lead to errors that's not immediately detectable. Instead, framework makes it easy to deal with connections between different Kubernetes resources by using their class names:

```php
public function env(EnvConfigurator $env): void
{
    $env
        ->varFromConfigMap(
            varName: 'MY_VAR',
            configMapClass: SomeConfigMap::class,
            configMapKey: 'someKey'
        )
        ->varFromSecret(
            varName: 'MY_VAR',
            secretClass: SomeSecret::class,
            secretKey: 'someKey'
        )
    ;
}

```

### Env variable from container resources
Kubernetes allows you to pass your resources requests and limits as values of env variables:

```php
public function env(EnvConfigurator $env): void
{
    $env
        ->varFromContainerResources(
            varName: 'MY_CPU_LIMITS',
            field: ContainerResourcesField::cpuLimits()
        )
        ->varFromContainerResources(
            varName: 'MY_MEMORY_REQUESTS',
            field: ContainerResourcesField::memoryRequests()
        )
    ;
}
```

### Env variable from pod fields
Kubernetes also allows to pass some info about pod:

```php
public function env(EnvConfigurator $env): void
{
    $env
        ->varFromPod(
            varName: 'MY_IP',
            podField: PodField::podIp()
        )
        ->varFromPod(
            varName: 'MY_METADATA_LABELS',
            podField: PodField::metadataLabels()
        )
    ;
}

```



### Import all keys from ConfigMap or Secret as env variables
Sometimes you'll want to import an entire [ConfigMap](../core/configmap.md) or [Secret](../core/secret.md) as env variables source. So all keys of ConfigMap or Secret will be names of environment variables:

```php
public function env(EnvConfigurator $env): void
{
    $env
        ->addConfigMap(SomeConfigMap::class)
        ->addSecret(SomeSecret::class)
    ;
}

```

Or, even simpler:

```php
public function env(EnvConfigurator $env): void
{
    $env
        ->addFrom(SomeConfigMap::class)
        ->addFromClasses(
            SomeOtherConfigMap::class,
            SomeSecret::class,
            SomeOtherSecret::class
        )
    ;
}

```

### Using env ConfigMaps or Secrets that are in different app
Examples above will work only if  [ConfigMaps](../core/configmap.md) and [Secrets](../core/secret.md) you add belong to the same [app](apps.md) as your *container class*.

For example, if you have container class `K8S\Hello\Container\NginxContainer`, where `K8S\Hello` is your app namespace, and you are trying to add variables from `K8S\World\Manifest\SomeConfigMap` as above - it will not work. Using env sources from other apps is considered a bad practice, since it will couple your apps, and it means that your apps are not structured correctly. But if you absolutely have to do this - here is how it's done:

```php
public function env(EnvConfigurator $env): void
{
    $env
        ->withExternalApp(WorldApp::name())
            ->addFrom(SomeConfigMap::class)
        ->withExternalApp(SomeOtherApp::name())
            ->addFromClasses(YetOneConfigMap::class, YetOneSecret::class)
    ;
}
```

Method `EnvConfigurator::withExternalApp()` returns other `EnvConfigurator` instance, configured to look for env source classes in other app, which name you passed to this method as parameter.

If it's not absolutely clear to you, what does it mean for manifest "to belong to app" - please read [Apps](apps.md) article.

## Volumes and VolumeMounts
An issue with [Volumes](https://kubernetes.io/docs/concepts/storage/volumes/) and VolumeMounts is that VolumeMounts is a part of container specification, while Volumes are defined on pod spec level, so `volumes()` method is not a part of `ContainerInterface`.  This is one more reason to use `Container`-prefixed [workloads](https://kubernetes.io/docs/concepts/workloads/), such as `AbstractContainerDeployment`, `AbstractContainerCronjob` etc. By extending this classes you'll have methods `volumes()` and `volumeMounts()` in one class. Here is an example using our `WorldDeployment` class from [Getting Started](../getting-started.md):

```php title="WorldDeployment.php"
class WorldDeployment extends AbstractContainerDeployment
{
    private const VOLUME_TEMP_DIR = 'temp-dir';
    private const VOLUME_PHP_CONF = 'php-conf';

    //...

    public function volumes(VolumesConfigurator $volumes): void
    {
        $volumes
            ->fromEmptyDir(self::VOLUME_TEMP_DIR)
            ->setSizeLimit(Memory::mebibytes(100))
            ->useRAM();

        $volumes->fromConfigMap(self::VOLUME_PHP_CONF, PhpConfigMap::class);
    }

    public function volumeMounts(VolumeMountsConfigurator $mounts): void
    {
        $mounts->add(self::VOLUME_TEMP_DIR, '/tmp');
        $mounts->add(self::VOLUME_PHP_CONF, '/etc/php/');
    }

    //...
}

```

Again, as with env sources, if ConfigMap you want to mount belongs to another app - use `withExternalApp()` method:


```php
public function volumes(VolumesConfigurator $volumes): void
{
    $volumes
        ->withExternalApp(SomeOtherApp::name())
        ->fromConfigMap(self::VOLUME_PHP_CONF, PhpConfigMap::class);
}

```

## Summary
Please see `ContainerInterface` in order to see what methods are available for you to define in this class.