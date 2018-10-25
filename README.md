# cakephp-entity-file-log

[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.txt)
[![Build Status](https://travis-ci.org/mirko-pagliai/cakephp-entity-file-log.svg?branch=master)](https://travis-ci.org/mirko-pagliai/cakephp-entity-file-log)
[![Build status](https://ci.appveyor.com/api/projects/status/rxadqjs0blb906jq?svg=true)](https://ci.appveyor.com/project/mirko-pagliai/cakephp-entity-file-log)
[![codecov](https://codecov.io/gh/mirko-pagliai/cakephp-entity-file-log/branch/master/graph/badge.svg)](https://codecov.io/gh/mirko-pagliai/cakephp-entity-file-log)

*cakephp-entity-file-log* is a CakePHP plugin that provides a log adapter that writes log (as entities) files.

Did you like this plugin? Its development requires a lot of time for me.  
Please consider the possibility of making [a donation](//paypal.me/mirkopagliai): even a coffee is enough! Thank you.

[![Make a donation](https://www.paypalobjects.com/webstatic/mktg/logo-center/logo_paypal_carte.jpg)](//paypal.me/mirkopagliai)

## Installation
You can install the plugin via composer:

    $ composer require --prefer-dist mirko-pagliai/cakephp-entity-file-log

**NOTE: the latest version available requires at least CakePHP 3.4**.

After installation, you have to edit `APP/config/bootstrap.php` to load the plugin:

    Plugin::load('EntityFileLog', ['bootstrap' => true]);

For more information on how to load the plugin, please refer to the 
[Cookbook](https://book.cakephp.org/3.0/en/core-libraries/logging.html#logging-configuration).

## How to use
Simply, you have to use the `EntityFileLog\Log\Engine\EntityFileLog` class as a log adapter.

For more information on how to configure logs, please refer to the 
[Cookbook](http://book.cakephp.org/3.0/en/plugins.html#loading-a-plugin).

Example:

    Log::setConfig('error', [
        'className' => 'EntityFileLog\Log\Engine\EntityFileLog',
        'path' => LOGS,
        'file' => 'error',
        'levels' => ['warning', 'error', 'critical', 'alert', 'emergency'],
    ]);

## Versioning
For transparency and insight into our release cycle and to maintain backward compatibility, 
MeTools will be maintained under the [Semantic Versioning guidelines](http://semver.org).
