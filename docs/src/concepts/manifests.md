# Manifests explained
## Prerequisites
This article and any other articles in this docs assume that you use [Dealroadshow K8S Bundle](https://github.com/dealroadshow/k8s-bundle) with a Symfony project and default Symfony configuration ([autowiring](https://symfony.com/doc/current/service_container/autowiring.html) and [autoconfiguration](https://symfony.com/doc/current/service_container.html#the-autoconfigure-option) enabled).

## Defining manifests
Defining manifest in Dealroadshow K8S Framework - means to define it's PHP class.
Thanks to Symfony's [autowiring](https://symfony.com/doc/current/service_container/autowiring.html) and autoconfiguration, K8S framework then finds all manifest classes, processes them and dumps them to YAML files.

But what classes are considered as manifests? Well, all classes, that implement `ManifestInterface`. Let's look at this interface:

```php title="ManifestInterface.php"
interface ManifestInterface extends AppAwareInterface, ConfigAwareInterface, MetadataAwareInterface
{
    public static function kind(): string;
    public static function shortName(): string;
    public function fileNameWithoutExtension(): string;
}

```

We can see three methods that this interface defines and thus every manifest must have: `kind()`, `shortName()` and `fileNameWithoutExtension()`. As you may have guessed, method `kind()` just returns good old Kubernetes **kind** - `Deployment`, `Service`, `Secret`, `Ingress` or any other kind that is understendable by Kubernetes itself. You will probably never define this method by yourself, since you will use abstract classes, defined by framework, such as `AbstractDeployment` or `AbstractSecret`- they all have this method defined. Method `fileNameWithoutExtension()` is internal and is used in order to generate filenames of dumped manifests. You don't have to define it either.

## Manifest names
The only method you need to define in every manifest is `shortName()`. Method is called `shortName()`, not `name()`, because it should return part of name, "relative" to app.
By default, full name of generated manifest will be formed as `{app name}-{manifest shortName}`, so for example if you have an app class `HelloApp`, which method `HelloApp::name()` returns `hello`, and manifest `WorldDeployment`, which method `WorldDeployment::shortName()` returns `world`, generated YAML file will have name `hello/world.deployment.yaml` and will contain deployment with name `hello-world`.

!!! tip "Tip"
    Don't worry if you don't like this naming convention. K8S framework is very flexible and allows to change this behavior. If you want to use your own naming convention for manifests - please read [Change naming convention](../howtos/change-naming-convention.md)

!!! tip "Good to know"
    In fact, manifest names are prefixed not with app names, but with [app aliases](apps.md#names-and-aliases). Term "app name" above is a slight simplification, good enough for the most of users, since by default "app alias" will be equal to "app name".


## Using app object inside manifests 
Please look again at `ManifestInterface` above. You may note that this interface extends other interfaces, first of which is `AppAwareInterface`. This interface declares a single method - `setApp()` and is used to inject app instance into manifest. You don't have to do anything: app will be injected into every manifest you write, and inside manifest methods you may access it as `$this->app`. If you want to know, what useful methods you can leverage from app instance - please read dedicated [Apps](apps.md) article.

## Manifest configuration
`ManifestInterface` also extends `ConfigAwareInterface`, which declares single method `setConfig()`. As with `setApp()`, you don't call this method either - it is called by framework on every manifest. In manifest class you can use inherited property `$this->config`, which has `array` type. By default this property will contain an empty array. If you want to pass some configuration to manifest - use bundle configuration, which should be stored in file `config/packages/dealroadshow_k8s.yaml` in your Symfony project:

```yaml title="dealroadshow_k8s.yaml"
dealroadshow_k8s:
	apps:
	    - alias: YOUR_APP_NAME
	      class: YOUR_APP_CLASS
          manifests:
              world:
                  replicas: 8
                  foo: bar
```

Manifests configs are stored under key `manifests:` in this config. Each key in `manifests` object is a short name of manifest you want to configure, and value for that key - is manifest configuration. So if you have `WorldDeployment` class with short name `world` and a configuration as above - property `$config` in your deployment will contain such array:
```php
$this->config === ['replicas' => 8, 'foo' => 'bar']; // true
```

You than may use this configuration as follows:

```php
public function replicas(): int
{
    return $this->config['replicas'];
}
```

Using bundle configuration file `dealroadshow_k8s.yaml` allows you to manage your environments easily, since you may create separate bundle configuration files for different envs - like `config/packages/prod/dealroadshow_k8s.yaml` for production and `config/packages/dev/dealroadshow_k8s.yaml` for development: a good example is option `replicas` above - you may want 8 replicas for your deployment in production, but you definetely want just 1 replica when you run your code locally on laptop. Configuration Environments is a default feature of Symfony Framework and you can read about this in [official docs](https://symfony.com/doc/current/configuration.html#configuration-environments).

But as good as this Symfony feature is, **it is NOT considered as best practice** for environments management in K8S framework. The main downside of using configuration values in your manifests is that your code is not very *descriptive* - for example, if you look at method `replicas()` above - you don't see how much replicas this development will actually have; you must navigate to configuration file for corresponding environment and look for this `replicas` value there. It would be nice if looking at class itself is all you need to imagine how resulting YAML manifest will look like. More on that in [Environments Management](../env-management.md).

## Manifest metadata
You may have noticed that every Kubernetes manifest has keys `kind` and `metadata`. In fact, manifest name is defined under `metadata` key, and every manifest has name. That's why `ManifestInterface` also extends `MetadataAwareInterface` and thus needs method `metadata()` defined. Since names for manifests are generated automatically, it means that `metadata` key is always generated. Method `metadata` is defined in any abstract class that you will use, such as `AbstractDeployment`, `AbstractIngress` etc.
But apart from manifest name, `metadata` should also contain the same labels, as `selector` section - this is needed for deployment to be able to "find" pods it manages.
Luckily, you don't have to copy that labels from `selector` section to `metadata` section - just define `selector()` method, and framework will make sure that `selector` labels are copied to `metadata` section in a top level of manifest and to pod template `metadata` section for workloads (deployments, jobs etc.).

## Manifest class has flat structure
For demonstration purposes, let's look at YAML manifest, generated from `WorldDeployment` class, defined in [Getting Started](../getting-started.md) article:

```php title="hello/world.deployment.yaml" linenums="1"
apiVersion: apps/v1
kind: Deployment
metadata:
  labels:
    app: hello
    name: world
  name: hello-world
spec:
  replicas: 1
  selector:
    matchLabels:
      app: hello
      name: world
  template:
    metadata:
      annotations:
        dealroadshow.com/env-sources-checksum: d41d8cd98f00b204e9800998ecf8427e
        dealroadshow.com/volume-sources-checksum: d41d8cd98f00b204e9800998ecf8427e
      labels:
        app: hello
        name: world
    spec:
      containers:
        -
          image: nginx
          imagePullPolicy: IfNotPresent
          name: app
          ports:
            - { containerPort: 80, name: http }
            - { containerPort: 443, name: https }

```

By looking at `ports` key in **line 28** we see that this key is located deep down in YAML object structure: `spec` -> `template` -> `spec` -> `containers[0]` -> `ports`.
Oddly enough, `ports()` is a method in `WorldDeployment` class - this method is not "hidden" somewhere deep down in class hierarchy. This is a one of the main conventions in K8S framework - **classes flatten actual YAML structure** where possible. That's why method `ports()` is a top-level citizen of deployment class.

## Defining container methods in workload classes
Some of you may have noticed weird part in a previous section: `ports` is a part of container specification, and `containers` section in line 23 in our YAML file is actually an array. So how do we define `ports()` method in deployment class, if there can be many containers, thus many `ports` sections?

The trick here is `AbstractContainerDeployment` class, which we extend in our `WorldDeployment` class. Any deployment class has `containers()` method, which should return a collection of `ContainerInterface` instances. `AbstractContainerDeployment` uses the fact that most of the multi-container [workloads](https://kubernetes.io/docs/concepts/workloads/) have one "main" container, which is an application itself and one or more "auxiliary" ones. For example, you may have deployment with 2 containers - `php-fpm` and `nginx`. You have your application in `php-fpm` container, therefore it's a "main" container here, and `nginx` container is just "auxiliary" one that helps you to expose your application via Web. You could create 2 classes, `PhpFpmContainer` and `NginxContainer`, and then return them in your method `WorldDeployment::containers()` like follows:

```php title="WorldDeployment.php"
class WorldDeployment extends AbstractDeployment
{
    public function containers(): iterable
    {
        yield new PhpFpmContainer();
        yield new NginxContainer();
    }
    
    //...
}

```

But having said that `php-fpm` is our `main` deployment and contains a big part of "deployment purpose" - it would be more descriptive to have this container logic in deployment class itself. That's why `AbstractContainerDeployment` implements both `ContainerInterface` and `DeploymentInterface` and it's `containers()` method looks like follows:

```php title="AbstractContainerDeployment.php"
public function containers(): iterable
{
    yield $this;
}
```

i.e. deployment returns itself as `ContainerInterface` and has methods both for *deployment* and *container*. Again, if we want to use `php-fpm` and `nginx` containers in our `WorldDeployment` class - we may extend it from `AbstractContainerDeployment` instead of `AbstractDeployment`, then define all methods for `php-fpm` container in our class, and then redeclare `containers()` method as follows:

```php title="WorldDeployment.php"
public function containers(): iterable
{
    yield $this;
    yield new NginxContainer();
}
```

More on containers in [Containers](containers.md) article.

## Manifest method names convention
At this moment you most likely already noticed it, but most methods in manifest classes, except `shortName()` and some internal framework methods are using the same names as corresponding sections in standard Kubernetes YAML manifest. So, basically if you know what you want to define in YAML manifest, you will not have problems with defining it in manifest class. 

For example, if you want to define `restartPolicy` section - you don't have to remember where in YAML object hierarchy this setting is (thanks to flat structure of manifest classes), or how to define it in your deployment class - just overwrite method `restartPolicy()` in your class. Start typing method name and your IDE will help you with defining this method. There are some exceptions for this rule, but they are **very** rare.

## Most manifest methods are void
This is a part of framework's design. The main idea behind this decision is this: there should be as little space for mistakes, as possible. Your IDE should generate your method along with it's parameters when you start typing method's name. Then you should be able to type something like `$someArgument->`, and your IDE will tell you, what methods this argument has (what are you limited to). This way you are limited to "valid" options in terms of manifests syntax, and only some logic mistakes are possible.

If method needs to return instance of some class - basically convention is that this class should have one or more static constructors. Just type `[ClassName]::` and see if there are some.

For example, if your IDE generated method `restartPolicy()` for you, you'll probably see it like follows:

```php
public function restartPolicy(): RestartPolicy|null
{
    return parent::restartPolicy();
}
```

See the `RestartPolicy` return type? Just start typing `return RestartPolicy::` in a method body, and IDE should give you a hint. After you've chosen one of static constructors, your method will look something like this:

```php
public function restartPolicy(): RestartPolicy
{
    return RestartPolicy::always();
}
```

This simple conventions will ease your experience of writing manifests and will drastically decrease number of errors.

## Summary
In this article we learned how to define manifests, some best practices for writing manifest classes and some conventions that K8S Framework has in order to make your life easier. 
You may also want to learn about [Environments Management](../env-management.md) or dive deeper into [Containers](containers.md).