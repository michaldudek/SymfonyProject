Symfony MD Flavour Edition
==========================

This is a custom edition of Symfony2 project skeleton with highly opinionated structure changes.

# Using

You can setup the project quickly via Composer:

    $ composer create-project michaldudek/symfony-project -s dev ./path/to/your/project

At the moment there isn't any generator/installer available which would automatically build your full application
structure so right after cloning the project some manual adjustments are required.

- Create `config/parameters.yml` by copying `config/parameters.yml.dist` and adjust it
- Setup application PHP code:
    - Rename `src/Project` to your application/project name - this will be your top level namespace `[ProjectNamespace]`
    - Rename `src/Project/ProjectApp.php` to your application/project name - this will be your application class
`[Project]App` - it is prefered to include a `*App` suffix.
    - Edit the new `src/[ProjectNamespace]/[Project]App.php`:
        - Update its namespace on line 2
        - Update its author on line 9 (good practice to include all developers working on the project here)
        - Update its class name on line 11
    - Edit `src/[ProjectNamespace]/Welcome/Controller/Home.php` and update the namespace on line 2.
    - Edit `src/[ProjectNamespace]/Resources/services/services.yml` and update the controller namespace on line 9.
- `web/app.php`: On line 5 change the referenced class name to your project class
- `web/app_dev.php`: On line 14 change the referenced class name to your project class
- `console`: On line 6 change the referenced class name to your project class
- `Vagrantfile`:
    - On line 1 set your application name.
    - On line 23 you can adjust the VM IP to something unique

If you wish to use Capistrano for deployments:

- `config/build/deploy.rb`:
    - On line 4 set your application name
    - On line 6 set your git repository URL
- `config/build/stages/dev.rb`:
    - On line 4 configure your server
    - On line 5 configure your deploy location

Now a `$ vagrant up` command should bring up your Vagrant VM up and make it available under `www.[APP_NAME].dev` domain.

# Philosophy

The main philosophy behind the proposed changes, structure and rules is *separation of concerns* and *inversion of
control*. This means that each file, folder or "package" should only be concerned with a single purpose (of varied
scope of course) and it should not know about its execution context, instead being controled by an outside entity.

Think `.cache` directory which only stores cache or `src` directory which only stores the backend application code.
Think `tests` directory which only stores testing code or `views` directory which only stores view templates.

# Changes

## Directory and file structure

The directory structure is quite different than that suggested by Symfony2 (or 3). The main difference is that there
is no `./app` directory. The idea is that all application code (including configuration) should be located inside
`./src` and all "artifacts" (cache files, log files, dynamic parameter files) should live outside.

    .cache/                                 # stores all auto-generated cache files
    config/                                 # various configuration files which are not related to app structure
        build/                              # build configuration including asset packaging and deployments
            assets/                         # asset packaging configuration (see further down the README)
                js.json
                less.json
            deploy/                         # deployment configuration (see further down the README)
        parameters.yml                      # git ignored file that should include environment specific configuration
    logs/                                   # logs directory
    resources/                              # various project resources can be stored here, including docs or code coverage
    src/                                    # backend application PHP code
        [ProjectNamespace]/                 # top level namespace of your application (typically app name)
            Resources/                      # Symfony2 related configuration files
                config/                     # configuration files
                    config.yml
                    config_*.yml
                routing/                    # routing files
                    routing.yml
                    routing_dev.yml
                services/                   # dependency injection container configuration files
                    services.yml
            [Project]App.php                # main application file that registers bundles
        autoload.php
    tests/                                  # contains all tests
    vendor/                                 # composer dependencies
    views/                                  # Twig view templates
    web/                                    # publicly accessible directory
        assets/                             # contains dynamically built frontend assets (see further down the README)
        components/                         # Bower components location
        js/                                 # frontend JavaScript
        less/                               # frontend CSS LESS source files
        app.php                             # prod env front controller
        app_dev.php                         # dev env front controller
    .BUILD                                  # contains the current build version
    console                                 # console file (used to be app/console)
    Gulpfile                                # Gulpfile for building frontend
    Makefile                                # Makefile that contains various common tasks
    phpunit.xml.dist                        # default PHPUnit configuration

The above list omits some other files that are not strictly Symfony related, but rather ease other aspected of web app
development or add other functionalities or handle frontend, etc.

## No Bundles, Not Even AppBundle

Current Symfony2 best practice is to have a single [application bundle](http://symfony.com/doc/current/best_practices/creating-the-project.html#application-bundles),
preferably called `AppBundle` in which all your application logic resides. MD\Flavour goes a step further and doesn't
even require any such bundle. The idea is that your code should not be tied to something so framework specific as
"a bundle" and even having the word "Bundle" in your PHP namespace might feel uncomfortable.

In MD\Flavour your application is just that - an application built using PHP classes that heavily use dependency
injection pattern and therefore are not bound to any specific framework. The application flow can then be configured
with outside configuration files (think `services.yml` or `routing.yml`).

The only two Symfony2 specific places are `./src/Project/ProjectApp.php` file in which you configure the Symfony bundles
that you want to use (`AppKernel.php` in the standard edition) and `./src/Project/Resources/` directory in which various
configuration files are located.

## Annotations are Forbidden!

The above also partially explains the fact that MD\Flavour strictly advises not to use annotations configuration in your
PHP code. While we understand that there is no performance difference between using annotations and e.g. YML files for
configuration, we believe that using annotations is tightly coupling your code with a specific framework or ORM.

An annotation requires to include an appropriate `use` statement in the head of your PHP file which may trigger errors
in various QA analyses as well as potentially autoload those classes (which then need to be present).

Ideally a PHP class should not know the context in which it is used. While convenient, using annotations does set that
context in the code. This may lead to readability and debug problems and most importantly can introduce issues when
switching context from e.g. Symfony2 to some other framework.

## Controllers as Services

Clean separation is also another reason why so called "controllers" should be considered nothing more than just service
classes that need to be built with dependency injection. In many frameworks, including Symfony2, controllers are somehow
treated in a special way. They are allowed to have the dependency injection container injected to them, they are allowed
using hidden dependencies through that container and they are allowed to configure themselves e.g. by using annotations.

We propose that controllers are the same level citizen as any other PHP class. The main advantage of this is ability to
unit test them. With clear dependencies it is very easy to mock all required services and test the controller logic.

Refer to [Symfony2 Cookbook](http://symfony.com/doc/current/cookbook/controller/service.html) in order to read more
about this approach, but in summary the only two things you need to do is register your controller as a service:

```yml
    # services.yml
    services:

        hello.controller:
            class: Project\Controller\HelloController
            arguments:
                - @world
```

and refer to it in your route definition:

```yml
    # routing.yml
    hello:
        path: /
        defaults: { _controller: hello.controller:hello }
```

Note that argument mapping from route parameters to method arguments still works using this way.

### Controller Results

Taking the above rule one step further we propose that controllers should not know in what context their response should
be used. For easy reusability they shouldn't specifically return HTML or JSON responses. They should just return a data
result which then can be wrapped in appropriate format by a different party.

This means that the same controller could be used in normal HTTP request to return an HTML page rendered with Twig,
but when called with XHR they could return JSON formated data, while when a `?callback=jsonp` query parameter was sent
they can be wrapped in a JSONP response. These are just common examples and there obviously are many more
possibilities, but the core of it is that it's not up to the controller to decide what type of response it should give.

To allow and ease this mechanic, MD\Flavour package offers `MD\Flavour\Controller\ResultConverter` class
(`md.controller.result_converter` service) which converts a return value from a controller to a `Response` object.
That return value can typically be an array, but in cases where the controller also wants to set the response's HTTP
status code it can be an instance of `MD\Flavour\Controller\Result` class which takes an array and a status code as two
constructor arguments.

```php
    # HelloController.php

    public function hello()
    {
        return [
            'hello' => 'world'
        ];
    }
```

is the same as

```php
    # HelloController.php
    use MD\Flavour\Controller\Result

    public function hello()
    {
        return new Result([
            'hello' => 'world'
        ], 200);
    }
```

Additionally, the `ResultConverter` will attempt to autoload a template to be rendered based on the controller name,
by removing the `controller` word from it and changing all `.`'s and `:`'s to dir separators `/`, e.g.
`home.controller:index -> home/index.html.twig` or `projects.list.controller:delete -> projects/list/delete.html.twig`.

The template to render for the route path can also be set with `_template` attribute.

### One URL per Controller Rule

Another controller rule that MD\Flavour proposes is keeping the number of mappings from a URL route to a controller
class to a minimum, preferably 1. A single URL path should be mapped to a single controller class with the controller
methods handling different HTTP methods.

An exception to the rule are "resource" routes with CRULD (Create-Read-Update-List-Delete) operations where an `id`
parameter is optional. The proposal is then to map them like this:

    GET /resource_url         -> resource.controller:index
    GET /resource_url/{id}    -> resource.controller:show
    POST /resource_url        -> resource.controller:create
    PUT /resource_url/{id}    -> resource.controller:update
    DELETE /resource_url/{id} -> resource.controller:delete

## Bye Bye Doctrine, Hello Knit

Because of past issues and rough history with Doctrine2, MD\Flavour drops this ORM in favour of custom made simple
data mapper that follows a repository pattern and abstracts away communication with a database of your choice.

Visit [Knit](https://github.com/michaldudek/Knit) and [KnitBundle](https://github.com/michaldudek/KnitBundle) for more
information.

Let's just say that using it is as simple as registering a repository service (which Knit forces as a best practice) and
injecting it wherever you please:

```yml
    # services.yml
    services:

        user_repository:
            parent: knit.repository        # handles repository instantiation
            arguments:
                - Project\Entity\User      # managed object class name
                - "users"                  # collection / table name in your database
```

## Let Frontend Manage Frontend - No Assetic

In MD\Flavour opinion, the backend should be managed by the backend and the frontend should be managed by the frontend.
These two should be kept separate and building frontend assets (compiling CSS, JS, optimizing images, etc) should be
done with tools that are designed for it and with which frontend developers feel more comfortable. Therefore there is
no need for `Assetic`.

# Frontend Additions

While not particularly Symfony or MD\Flavour related, this project skeleton also adds several tools and makes some
recommendations on how to manage the frontend.

## Bower Components

It is prefered to use Bower as the asset manager. To be accessible by the web, all Bower components are downloaded to
`./web/components/` directory. Please refer to [Bower](http://bower.io/) for information on how to use it.

## Building JS & CSS Assets

This project introduces quite complex but powerful build system for frontend assets. At its core it uses Gulp and some
popular gulp plugins for minification, concatenation, generating sourcemaps, etc. But the configuration of it is quite
different.

If you look into `./config/build/assets/` directory you will find two files: `js.json` and `less.json`. Their contents 
define and configure so called "packages" into which the assets should be compiled on build time.

The idea is that while concatenation is a great way to speed up page loading, having a single huge file to download
might sometimes be slower than simultaneously downloading two or three smaller files. This also helps a lot with
browser caching. Therefore, the two JSON files define "packages" that should be built.

The top level key is the package name and the generated file will be of the same name and its two properties define
which files should be included in the package (`files`) and what files should be watched by Gulp when its watching for
changes in a given package (`watch`). The `watch` parameter is optional and will default to what `files` is set to,
but it's especially useful when only one file that imports all other is compiled, but all the imported files still need
to be watched, e.g.:

```json
    {
        "lib": {
            "files": "web/less/lib.less"
        },
        "app": {
            "files": "web/less/page.less",
            "watch": "web/less/**/*.less"
        }
    }
```

To build the assets simply run `$ gulp` in the terminal or `$ gulp watch` to watch the files. Also `$ gulp js` and
`$ gulp less` are available. Or, preferably, use the `Makefile`.

# Development

Several tools and concepts are introduced specifically for development process.

## Makefile

Because various dev and build processes might be complex and require to run several commands in proper order or with
specific arguments, a `Makefile` is a very useful tool to standardize and simplify all this.

The `Makefile` included in this project contains several useful tasks, such as:

    make install       # installs all dependencies required in production
    make install_dev   # installs all dependencies required in development
    make assets        # builds all frontend assets
    make watch         # watches the frontend assets for changes and compiles if necessary
    make test          # runs all tests
    make lint          # lints all the code
    make qa            # runs all registered Quality Assurance checks
    make report        # creates a report on the code (e.g. code coverage report)

See `$ make help` for list of all high-level tasks and view [Makefile](Makefile) for specifics.

It is highly encouraged to wrap all commands inside the `Makefile` and advise all developers involved in the project
to use it.

## The BUILD Version

MD\Flavour introduces a concept of a *build version*. It is most useful when referencing dynamically built frontend
assets inside Twig templates. Because the project's [.htaccess](web/.htaccess) file adds heavy caching to almost all
frontend files, CSS and JavaScript files are built with a build version appended to their name, e.g.
`lib-2015100801.js`. That build version is read from `.BUILD` file located in the root dir of the project (git ignored),
and if it doesn't exist or is empty, it defaults to `dev`.

This `.BUILD` file should be written by whatever deployment tool you are using (in our case - Capistrano) with whatever
version tag. In case of Capistrano this is a release timestamp, but it can be anything else, a release number or git
commit hash or a UNIX timestamp.

It can be referenced in PHP code using `%kernel.build%` container parameter, `{{ kernel.build }}` variable in Twig or
`[Project]\[Project]App::getBuild()` method.

An example reference to a frontend file can look like this:

    <link rel="stylesheet" href="/assets/css/lib-{{ kernel.build }}.css">

## Vagrant

A default `Vagrantfile` is included in this project to build a basic Vagrant VM with PHP 5.6 and MySQL 5.6 as well as
configured Apache2 for immediate development. Upon installing the project you should be safe to run `$ vagrant up` and
get the machine running. Please refer to [./resources/docs/Vagrant.md](resources/docs/Vagrant.md) file for more docs
on Vagrant configuration.

## Quality Assurance

We believe that Quality Assurance should be part of the development process from the very beginning. It's much easier to
keep high code standards straight on rather than go back and fix issues. Therefore the project is set with several tools
to ease QA checks from the get go.

To run all these checks simply run `$ make qa` in your terminal. This will execute several tasks, including:

- **PHPUnit** - which obviously runs all PHP unit tests located in `./tests/` directory and which can be configured in
`./phpunit.xml.dist` file;
- **PHPCS** - [PHP_CodeSniffer](https://github.com/squizlabs/PHP_CodeSniffer) which checks for proper coding style
standards (tip: run `$ make phpcs_fix` to automatically fix what can be fixed) and which can be adjusted and configured
in `./phpcs.xml` file;
- **PHPMD** - [PHP Mess Detector](http://phpmd.org/) which does static analysis of the PHP code and looks for common
bad practice, caveats, errors, etc. While this can be a very annoying tool if you're just starting with it, it proved
to be invaluable in keeping code standards really high. It can be configured in `./phpmd.xml` file.
- **JSHint** - [JSHint](http://jshint.com/about/) which runs static code analysis on JavaScript code located in
`./web/js/` directory and can be configured in `./.jshintrc`.

# Deployments

TBD.

## Capistrano

TBD.

## Chef Knife

TBD.

# Other

TBD.

## GitLab CI

TBD.
