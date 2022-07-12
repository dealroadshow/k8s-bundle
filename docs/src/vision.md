# Dealroadshow K8S Framework vision
You may ask, why [Dealroadshow K8S Framework](https://github.com/dealroadshow/k8s-framework) was created, when there are such awesome tools with similar specific, as [Helm](https://helm.sh/), [Amazon CDK8S](https://cdk8s.io/) and [Pulumi Kubernetes](https://www.pulumi.com/docs/get-started/kubernetes/), and what is the difference between K8S Framework and this tools. Those questions are discussed below.

## What is Dealroadshow K8S
[Dealroadshow K8S Framework](https://github.com/dealroadshow/k8s-framework) and [Dealroadshow K8S Bundle](https://github.com/dealroadshow/k8s-bundle) together form a tool that allows developers to define Kubernetes manifests using PHP and [Symfony Framework](https://symfony.com) - one of the best general-purpose frameworks. 

!!! tip "Tip"
    Below in the text combination of Dealroadshow K8S Framework and Dealroadshow K8S Bundle is referred as "K8s framework".

*K8S framework* focuses on **declarativeness**, **developer productivity** and constitutes our attempt to bring best practices from "software developers world" to "DevOps / SRE world".

### Declarativeness
In order to define manifest - just define a PHP class for it. Simple class with a [flat structure](concepts/manifests.md#manifest-class-has-flat-structure) allows you to generate complex YAML manifest with many nested levels. You can understand how your manifest will look like by looking at this PHP class.

### Developer productivity
K8S Framework allows to generate skeleton classes for almost any Kubernetes resource you may need. For example, to define a simple [manifest](concepts/manifests.md) class, just use Symfony console command for that:

```bash
bin/console k8s:generate:manifest my-manifest-name
```

Follow instructions in this command, and your ready-to-go class will be generated.

K8S Framework also uses simple conventions (like [this](concepts/manifests.md#manifest-method-names-convention) or [this](concepts/manifests.md#most-manifest-methods-are-void), that help you to define any manifest you want by simply following your knowledge of Kubernetes itself and the hints your IDE gives you. 

!!! tip "Tip"
    By the way, dude - don't be a dinosaur, use a [good IDE](https://www.jetbrains.com) - it's NOT an advertisement. Using good IDE for the programming language of your choice makes you many times more productive, and a process of your work is much more enjoyable. Don't follow "conservatives" who say "I do everything in Notepad / Vim / Emacs / Atom" - it's just not cool in 2022.

PHP has strict types comparing to other dynamic languages, so you have both productivity of work with interpretable programming language and the level of IDE analysis comparable to compiled language. When using K8S Framework and Symfony - IDE is your best friend and can hint you nearly everything. K8S framework itself decreases possibilities of an error in your manifests on early stages - for example your manifests generation command (which itself is Symfony application) will quickly fail if your classes lack of methods, required for your manifests to be valid; arguments in methods of your manifest classes are limiting you to a strict set of valid choices and so on.

Symfony's approaches to structuring your code, such as [autowiring](https://symfony.com/doc/current/service_container/autowiring.html) make development easy and productive. Usage of a full-fledged framework itself frees your from searching tools for many common tasks, such as configuring your [application](how-to-read.md#apps-and-application), [environments management](env-management.md), defining console commands etc.

## K8S Framework compared to other tools

### K8S Framework and Helm
[Helm](https://helm.sh) is a tool that helps you to define, install, and upgrade Kubernetes applications. So generating Kubernetes manifests is one of it's responsibilities, while it's the **only** responsibility of K8S Framework, and K8S framework aims to be the best tool for that.

The downside of defining manifests using Helm is the fact that **you are writing an application using template language instead of programming language**: you define templates using [Go templates language](https://pkg.go.dev/text/template). It's not uncommon to use conditionals, loops, functions etc in your templates. After some time, your Helm chart becomes a big *application* that generates YAML manifests. But instead of being written using programming language, it's written using template language without using [OOP](https://en.wikipedia.org/wiki/Object-oriented_programming), proper code structuring, programming patterns, helpful libraries and many other good things. Often Helm templates are using so much template language that they become barely readable even by their author.

Therefore the obvious solution to this issue is to write application using programming language. That is where K8S Framework comes in hand.  But since you still need a tool to deploy your software to Kubernetes cluster, upgrade and rollback it, and Helm being a good tool for that - you may use Helm together with K8S Framework: K8S generates manifests, and Helm deploys them.

### K8S Framework and cdk8s
K8S Framework is a pretty similar tool to [cdk8s](https://cdk8s.io/). They both aim to be the best tool for defining manifests using programming code and both focus only on that task, leaving responsibility to deploy and upgrade generated manifests toother tools. Also they both built their abstractions on top of code, automatically generated from Kubernetes API specs ([k8s-resources](https://github.com/dealroadshow/k8s-resources) library for K8S Framework), in order to always stay relevant and to keep up with new Kubernetes versions.

The first difference between this tools is languages they support. cdk8s supports few different languages, such as Python, Go, Typescript etc., while K8S Framework is a tool for PHP developers and / or DevOps / Site Reliability engineers, familiar with PHP. Since PHP is still often undeservedly hated by "old-school sysadmins", it probably should not be used if in your company Kubernetes manifests is a responsibility of sysadmins / DevOps specialists instead of software developers.

The fact that K8S framework is designed to be used with Symfony is both it's upside and downside comparing to cdk8s and Pulumi: on the one hand, Symfony simplifies so many general-purpose tasks, such as application configuration, environments management, code structuring etc. that you would need to implement yourself if you use cdk8s. Symfony also has one of the best implementations of [Dependency Injection](https://en.wikipedia.org/wiki/Dependency_injection) pattern among all programming languages, comparable only with the implementation in [Spring](https://spring.io/) Java framework. And once you tried what application development with Dependency Injection feels like, there is no way back. Javascript and Typescript (languages, most often used with cdk8s), on the other hand, don't have really good implementations of Dependency Injection pattern and are limited with few DI surrogates. This makes depelopment and maintainance big applications using Javascript harder, and big Javascript applications more often become a mess.

!!! attention "Note for Javascript developers"
    In no way do we want to offend Javascript developers, so don't you start to talk about our mother: there are plenty of great applications, written in Javascript, and developer experience means much more than the choice of language. It's just general rule that using [Inversion of Control](https://en.wikipedia.org/wiki/Inversion_of_control) principle / Dependency Injection pattern is better than not using it.

On the other hand, if you are not PHP / Symfony developer and don't plan to use this language anywhere, learning new programming language and a big framework only to define Kubernetes manifests is an overkill. This fact narrows the usage scope of K8S Framework comparing to cdk8s, which supports many different languages.

As a general rule - use K8S Framework if your product is written using PHP and developers write Kubernetes manifests (or planning to start doing so) in your company. Use cdk8s otherwise.

### K8S Framework and Pulumi
Since Pulumi supports different programming languages, samely as cdk8s, and most often used with Javascript / Typescript - the same upsides and downsides as in case with cdk8s apply to it.

## Combining the best of both worlds
Above we mentioned that K8S Framework is an 
> attempt to  bring best practices from "software developers world" to "DevOps / SRE world".

Symfony Framework, on which shoulders K8S Framework is standing, brings such important things, as [Inversion of Control](https://en.wikipedia.org/wiki/Inversion_of_control) principle,  [Dependency Injection](https://en.wikipedia.org/wiki/Dependency_injection) pattern, mentioned above, and many other best practices and patterns. It's good if your team consists of both developers and DevOps engineers -  this way by using K8S Framework and Symfony DevOps engineers will learn many useful programming techniques, and will be able to use this knowledge when working on [Infrastructure as a code](https://en.wikipedia.org/wiki/Infrastructure_as_code) and other tasks. Developers, on the other hand, will deepen their knowledge of Kubernetes, IaC and other topics.

Not only should we bring the best things from "developer's world" to "SRE's world", it should also work  the other way: we believe developers should define, how their applications will be run, e.g. they should define Kubernetes manifests and should be familiar with CI/CD pipeline that is used in your company.