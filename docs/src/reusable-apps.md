# Reusable apps distribution
You've probably heard about [Helm charts](https://helm.sh/docs/topics/charts/) as a simple way of getting some functionality for applications that use Kubernetes - whether you need simple MySQL database or big RabbitMQ cluster - you can just choose a dedicated Helm chart for that, and Helm will generate Kubernetes manifests for that software. You also can configure some values in your charts to change default behavior.

K8S Framework does not define it's own artifact hub, since all that you need already exists in PHP ecosystem: PHP has [Composer](https://getcomposer.org), one of the greatest package manages among all programming languages, and Symfony has it's excellent [bundle system](https://symfony.com/doc/current/bundles.html) that allows to install some functionality to Symfony via Composer. 

So if you want to create distributable reusable artifact for K8S Framework, here are the simple steps for that:

- Create a K8S Framework [app](concepts/apps.md), for example `MySQLApp`.
- Package it into a [Symfony Bundle](https://symfony.com/doc/current/bundles.html).
- Publish your bundle using [Packagist](https://packagist.org/)

After that everyone can install your reusable app using Composer:

```bash
composer require your-nickname/mysql-app-k8s
```

If you are writing reusable app, you probably want to define some [friendly configuration](https://symfony.com/doc/current/bundles/configuration.html) for the Symfony bundle it comes with and use this configuration to tweak manifests. You can read about manifests configuration [here](concepts/manifests.md#manifest-configuration).