Symfony Project by Michał Pałys-Dudek
=====================================

[Symfony 2](http://www.symfony.com) project skeleton with opinionated customizations and additions.

#### Why?

Because.

# Changes

## What is removed?

- **AsseticBundle** Public assets (especially CSS and JavaScript) should be managed by
frontend tools like Gulp.
- **SwiftmailerBundle** Not every application needs to send emails.
- **SensioFrameworkExtraBundle** I am strongly against using annotations for configuration.
- **Other:** SensioGeneratorBundle, Composer parameter handler.
- Few unnecessary files.

## What is changed?

- **Directory structure.** Some directories have been moved to the top of the tree for
better visibility and better purpose sharing (e.g. logs dir also includes logs from
other sources, not only Symfony)

    .cache/             # cache dir
    app/                # original application dir
    logs/               # all logs (including Apache2) kept here
    resources/          # container for various resources
        docs/           # documentation
    src/                # all PHP code
    tests/              # tests code
        unit/           # PHP unit tests
    vendor/             # Composer vendors
    web/                # public web directory

## What is added?

- **Vagrant with Chef** - [read more](resources/docs/Vagrant.md).

# Conventions

- **No annotations**. They're designed to document code, not run it. Using
annotations to augment the code also adds a layer of coupling.

# Disclaimer

Please note that this is a very opinionated flavor of Symfony 2 project. Some of the
changes I've made are based on nothing more than just my personal preference (which mostly
was thought through nevertheless). Your mileage may vary.
