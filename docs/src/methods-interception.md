# Manifest methods interception
Sometimes defining Kubernetes manifests involves code repetition, and you have to do the same repetitive job again and again. For example - you almost always have to define metadata labels at the top level of deployment manifest (`metadata.labels`), in pod spec template (`spec.template.metadata.labels`), and then section `spec.selector.matchLabels` should contain same labels. Thus you need copy-paste same labels 3 times every time you write a manifest. It would be great if there was a way to automate such repetitive tasks, and *manifests methods interception* is a way to do that in K8S Framework.

Dealroadshow K8S Framework uses [Proxy](https://en.wikipedia.org/wiki/Proxy_pattern) pattern to wrap every instance of your manifest classes into a proxy-object. Then, when manifests are [processed](lifecycle.md#processing-apps), all methods of your manifests classes are called on proxy objects. This proxy objects wrap every method of original manifest instance in order to dispatch events before and after calling original methods, thus allowing you to intercept **any** method call. [K8S Framework](https://github.com/dealroadshow/k8s-framework) uses [PSR-14](https://github.com/php-fig/event-dispatcher) `EventDispatcherInterface` in order to dispatch all events, and  [K8S Bundle](https://github.com/dealroadshow/k8s-budle) uses [Symfony Event Dispatcher](https://github.com/symfony/event-dispatcher) component that implements this interface. Before every manifest method call `ManifestMethodEvent` is dispatched. After manifest method was called, `ManifestMethodCalledEvent` is dispatched. Among other things, you then may replace method's return value in [event subscriber](https://symfony.com/doc/current/components/event_dispatcher.html#using-event-subscribers). 

Let's demonstrate possibilities of methods interceptions by automating the definition of `selector.matchLabels` and `metadata.labels` sections in all manifests, so that you don't have to write those ever again. For that, we will define  [event subscriber](https://symfony.com/doc/current/components/event_dispatcher.html#using-event-subscribers) class `SelectorLabelsSubscriber` in a standard Symfony project location `src/EventListener`:

```php title="src/EventListener/SelectorLabelSubscriber.php" linenums="1"
class SelectorLabelsSubscriber implements EventSubscriberInterface
{
    public function onManifestMethod(ManifestMethodEvent $event): void
    {
        $methodName = $event->methodName();
        $params = $event->methodParams();
        $manifest = $event->manifest();
        if ('selector' !== $methodName || !($manifest instanceof DeploymentInterface)) {
            // It's either not `selector()` method call, or not deployment's method being called
            return;
        }

        /** @var SelectorConfigurator $selector */
        $selector = $params['selector'];

        /** @var AppInterface $app */
        $app = PropertyAccessUtil::getPropertyValue($manifest, 'app');

        $selector
            ->addLabel('app', $app->alias())
            ->addLabel('component', $manifest::shortName())
        ;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ManifestMethodEvent::NAME => 'onManifestMethod'
        ];
    }
}

```

As we see, our class implements Symfony's native `EventSubscriberInterface`, so thanks to autoconfiguration, just defining this class and implementing method `getSubscribedEvents()` as above is enough for it to recieve event every time manifest's method is about to be called. All logic of interception resides in method `onManifestMethod()`, which recieves events, so let's analyze it:

- **lines 8-10**: we are checking if method being called is called `selector`, and if that manifest, on which this method is about to be called, is an instance of `DeploymentInterface`. If one of this conditions is false, we do nothing. Thus we are limiting our event subscriber to calls of method `selector()` on deployments.
- **line 14**: event object contains all parameters, passed to method being called. Method `DeploymentInterface::selector()` must recieve `SelectorConfigurator` instance as `$selector` argument, so we getting it from array of method params.
- **line 17**: there is no method that returns [app](concepts/apps.md) in `ManifestInterface`, but any manifest that inherits K8S Framework's corresponding abstract classes (like `AbstractDeployment` for deployments) inherits property `$app` that contains app instance. Since this property is [protected](https://www.php.net/manual/en/language.oop5.visibility.php), we use `Dealroadshow\Bundle\K8SBundle\Util\PropertyAccessUtil` class to retrieve it from manifest instance.
- **lines 19-22**: we are using `$selector` argument of `DeploymentInterface::selector()` method to define two labels: `app`, that contains our [app alias](concepts/apps.md#names-and-aliases), and `component`, which contains short name of the deployment. 

Thus any generated deployment will have selector labels automatically. K8S Framework copies all labels from `selector.matchLabels` to `metadata.labels` and `spec.template.metadata.labels` sections automatically - so you'll never have to write this labels again in your deployments - all thanks to one event subscriber.

K8S Bundle also has predefined abstract classes that you may inherit from if you want to intercept manifest's methods: `AbstractMethodSubscriber` and `AbstractMethodResultSubscriber` - for intercepting method before it was called, or after, respectively.  Below is a new version of our `SelectorLabelsSubscriber` that inherits `AbstractMethodSubscriber`:

```php title="src/EventListener/SelectorLabelSubscriber.php" linenums="1"
class SelectorLabelsSubscriber extends AbstractMethodSubscriber
{
    protected function supports(ManifestMethodEvent $event): bool
    {
        return 'selector' === $event->methodName() && $event->manifest() instanceof DeploymentInterface;
    }

    protected function beforeMethod(ManifestMethodEvent $event): void
    {
        /** @var SelectorConfigurator $selector */
        $selector = $event->methodParams()['selector'];

        $manifest = $event->manifest();
        /** @var AppInterface $app */
        $app = PropertyAccessUtil::getPropertyValue($manifest, 'app');

        $selector
            ->addLabel('app', $app->alias())
            ->addLabel('component', $manifest::shortName())
        ;
    }
}

```

This class is pretty self-explanatory now: method `supports()` is used to determine whether this subscriber should be called for the given event. Method `beforeMethod()` (for method result subscribers - `afterMethod()`) then does the real job, if `supports()` returned true.

Now that we have our subscriber, let's test it. First, let's define a simplest possible deployment class `ExampleDeployment` in `ExampleApp`:

```php title="src/Example/Manifest/ExampleDeployment.php"
class ExampleDeployment extends AbstractContainerDeployment
{
    public function image(): Image
    {
        return Image::fromName('my-cool/image');
    }

    public static function shortName(): string
    {
        return 'example';
    }
}

```

Now we can call console command to dump manifests into YAML files:

```bash
bin/console k8s:dump:all
```

After command finished, let's look what YAML was generated from our `ExampleDeployment` class:

```yaml title="example/example.deployment.yaml" linenums="1"
apiVersion: apps/v1
kind: Deployment
metadata:
  labels:
    app: example
    component: example
  name: example-example
spec:
  replicas: 1
  selector:
    matchLabels:
      app: example
      component: example
  template:
    metadata:
      annotations:
        dealroadshow.com/env-sources-checksum: d41d8cd98f00b204e9800998ecf8427e
        dealroadshow.com/volume-sources-checksum: d41d8cd98f00b204e9800998ecf8427e
      labels:
        app: example
        component: example
    spec:
      containers:
        -
          image: my-cool/image
          imagePullPolicy: IfNotPresent
          name: app

```

Notice **lines 4-6**, **11-13** and **19-21**: we have our labels everywhere we need them. Isn't this cool?

Now that you know about manifests methods interception, you may use this technique to automate many things in your manifests. Please note that method interception also works for container methods: for example, if your deployment class inherits `AbstractContainerDeployment`, you may intercept it's method `resources()` or any other method from `ContainerInterrface`.