Vagrant
=======

The project comes with a `Vagrantfile` in the root folder. It boots, configures and runs
a virtual machine for development purposes. It uses
[Chef Solo](https://docs.vagrantup.com/v2/provisioning/chef_solo.html) as the provisioning
method.

Please visit [Vagrant website](https://www.vagrantup.com/) for detailed information and
how to install and use it.

# Usage

To start the server do:

    $ vagrant up

When you run it for the first time it will create a virtual machine, configure it and
install all required software (this is called "provisioning").

It will also make your Symfony project accessible under a domain `www.symfonyapp.dev`.
**Note: you should change this on line 1 in the `Vagrantfile`**.

To SSH to the server do:

    $ vagrant ssh

When you're done working, run:

    $ vagrant suspend

This will suspend the virtual machine so it doesn't take up your computer's resources.

# Configuration

By default, the VM (virtual machine) is provisioned with the following software:

- Ubuntu 14.04
- PHP 5.6 with xdebug, gd2, imagick, pear, memcached, intl, curl, mcrypt, disabled opcache
- Apache2.4 with PHP (mpm_prefork), php.ini shared with CLI
- Memcache server
- MongoDB server
- MySQL server
- Redis server
- NodeJS
- Global [Composer](https://getcomposer.org/)

It will also mount the project directory under `/var/www/[app_name]` and create
appropriate Apache2 VirtualHost file for it.

Feel free to adjust the `Vagrantfile` to your needs or disable some of the cookbooks if you
do not need some of the software.
