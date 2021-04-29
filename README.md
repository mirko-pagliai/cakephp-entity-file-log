# cakephp-entity-file-log

[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.txt)
[![Build Status](https://travis-ci.org/mirko-pagliai/cakephp-entity-file-log.svg?branch=master)](https://travis-ci.org/mirko-pagliai/cakephp-entity-file-log)
[![Build status](https://ci.appveyor.com/api/projects/status/rxadqjs0blb906jq?svg=true)](https://ci.appveyor.com/project/mirko-pagliai/cakephp-entity-file-log)
[![codecov](https://codecov.io/gh/mirko-pagliai/cakephp-entity-file-log/branch/master/graph/badge.svg)](https://codecov.io/gh/mirko-pagliai/cakephp-entity-file-log)
[![CodeFactor](https://www.codefactor.io/repository/github/mirko-pagliai/cakephp-entity-file-log/badge)](https://www.codefactor.io/repository/github/mirko-pagliai/cakephp-entity-file-log)

*cakephp-entity-file-log* is a CakePHP plugin that provides a log adapter that
writes log (as entities) files.

Did you like this plugin? Its development requires a lot of time for me.
Please consider the possibility of making [a donation](//paypal.me/mirkopagliai):
even a coffee is enough! Thank you.

[![Make a donation](https://www.paypalobjects.com/webstatic/mktg/logo-center/logo_paypal_carte.jpg)](//paypal.me/mirkopagliai)

## Installation
You can install the plugin via composer:
```bash
$ composer require --prefer-dist mirko-pagliai/cakephp-entity-file-log
```

Then you have to load the plugin. For more information on how to load the plugin,
please refer to the [Cookbook](//book.cakephp.org/4.0/en/plugins.html#loading-a-plugin).

Simply, you can execute the shell command to enable the plugin:
```bash
bin/cake plugin load EntityFileLog
```
This would update your application's bootstrap method.

### Installation on older CakePHP and PHP versions
Recent packages and the master branch require at least CakePHP 4.0 and PHP 7.2.
Instead, the [cakephp3](//github.com/mirko-pagliai/cakephp-entity-file-log/tree/cakephp3) branch
requires at least PHP 5.6.

In this case, you can install the package as well:
```bash
$ composer require --prefer-dist mirko-pagliai/cakephp-entity-file-log:dev-cakephp3
```

Note that the `cakephp3` branch will no longer be updated as of April 29, 2021,
except for security patches, and it matches the
[1.1.3](//github.com/mirko-pagliai/cakephp-entity-file-log/releases/tag/1.1.3) version.

## How to use
Simply, you have to use the `EntityFileLog\Log\Engine\EntityFileLog` class as a log adapter.

For more information on how to configure logs, please refer to the
[Cookbook](http://book.cakephp.org/4.0/en/plugins.html#loading-a-plugin).

Example:
```php
Log::setConfig('error', [
    'className' => 'EntityFileLog\Log\Engine\EntityFileLog',
    'path' => LOGS,
    'file' => 'error',
    'levels' => ['warning', 'error', 'critical', 'alert', 'emergency'],
]);
```

## Versioning
For transparency and insight into our release cycle and to maintain backward compatibility,
*cakephp-entity-file-log* will be maintained under the [Semantic Versioning guidelines](http://semver.org).
