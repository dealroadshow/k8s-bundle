# K8S Framework application lifecycle
## Prerequisites
This article assumes that you are:

- familiar with Kubernetes itself
- have read about [manifests](manifests.md)
- have basic understanding of [apps](apps.md) concept

## Console commands
[K8S Bundle](https://github.com/dealroadshow/k8s-bundle) defines **4** console commands for manifest generation, which can be used with [Symfony Console entry-point](https://symfony.com/doc/current/console.html#running-commands) **`bin/console`**:

- `k8s:dump:all` - dump all manifests as YAML files to configured location (by default - `src/App/Resources/k8s-manifests` dir)
- `k8s:dump:apps` - dump manifests from chosen apps
- `k8s:print:all` - print all manifests to stdout in YAML format
- `k8s:print:apps` - print manifests from chosen apps to stdout in YAML format

Despite small differences in this commands, app's lifecycle is the same for any of them.

## Application lifecycle
"*Application*" here refers to the Symfony application that generates manifests when you call one of the console commands listed above, not [*App*](apps.md) in K8S Framework. So what happens when applicaton starts?

### Collecting app instances
Class **`AppRegistry`** has a simple API that allows to register / store [app](apps.md) instances and retrieve them. 

!!! note "Note"
    Thanks to awesome [Dependency Injection](https://symfony.com/doc/current/service_container.html) in Symfony (including [autowiring](https://symfony.com/doc/current/service_container/autowiring.html) and [autoconfiguration](https://symfony.com/doc/current/service_container.html#the-autoconfigure-option)) you just need to define an App class, and it's instance will be created and registered in Symfony's Dependency Injection container.

When Symfony application starts, K8S framework retrieves all app instances from Symfony DI-container and registers them in **`AppRegistry`**. 

!!! note "Good to know"
    This job is done by `AppsPass` class in K8S Bundle. `AppsPass` is an example of an important concept in Symfony - [Compiler Pass](https://symfony.com/doc/current/service_container/compiler_passes.html#working-with-compiler-passes-in-bundles). If you want to dive deeply into how K8S Framework works - you may want to know this concept.

After all apps are collected to `AppRegistry` - it's time to collect manifests and register them in `ManifestRegistry`.

### Collecting manifest instances
Just like `AppRegistry` stores app instances, **`ManifestRegistry`** class stores and allows to retrieve instances of manifests. Manifests are stored with the information about app they *belong to*. How does K8S Framework decides, which app *owns* manifest? By default this is done by checking if manifest class resides in *app namespace*. Every app is generated in dedicated directory. For example, if we have app class `K8S\Hello\HelloApp` - app namespace is `K8S\Hello\` and any namespace below. So if we have manifest class `K8S\Hello\Manifest\WorldDeployment` - K8S framework sees that this deployment's namespase is under `HelloApp`'s namespace `K8S\Hello\` and thus registers `WorldDeloyment` instance in `ManifestRegistry` as manifest that belongs to `HelloApp`.

!!! note "Good to know"
    Just like `AppsPass` registers apps in `AppRegistry`, the job of collecting manifest instances and adding them to `ManifestRegistry` is done by another compiler pass - `ManifestsPass`.

### Processing apps
Now that all apps and manifests are registered in corresponding registries, K8S framework can start to *process* apps. It iterates over chosen app names, retrieves app instances from `AppRegistry` and for each app retrieves all instances of manifests that belong to this app. It then iterates over manifests and processes them - native Kubernetes API objects are created and filled with data by calling methods from your manifest instances. The result of processing each manifest is an API object or that implements `APIResourceInterface` and can be directly encoded into a valid JSON, understandable by your Kubernetes cluster. This API resource object is added to the app instance by calling it's method `addManifestFile()` (this method is a part of `AppInterface`). App is *processed* when it's manifest are processed and corresponding API objects are added to app.

!!! note "Note"
    You may have noticed that we are calling result of manifest processing "API object" or "API resource", but method in `AppInterface` is called `addManifestFile()`. "Manifest file" is a combination of API object and filename it will be dumped with.

### Dumping manifests
When all apps are *processed* - framework iterates once again over all app instances. This time for each app it retrieves all API resources, added during previous stage, encodes them to YAML format and saves them to filesystem or prints them to stdout, depending on what console command is called. After all manifests are dumped - application made it's job, so it finishes.

## Summary
In this article we learned about lifecycle of manifests generation. If you got to this page by clicking link in [Apps](apps.md) article - feel free to return to it.