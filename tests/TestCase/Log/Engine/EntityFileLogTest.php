<?php
declare(strict_types=1);

/**
 * This file is part of cakephp-entity-file-log.
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright   Copyright (c) Mirko Pagliai
 * @link        https://github.com/mirko-pagliai/cakephp-entity-file-log
 * @license     https://opensource.org/licenses/mit-license.php MIT License
 */
namespace EntityFileLog\Test\TestCase\Log\Engine;

use Cake\Log\Log;
use Cake\ORM\Entity;
use Cake\Routing\Exception\MissingControllerException;
use EntityFileLog\Log\Engine\EntityFileLog;
use MeTools\TestSuite\TestCase;

/**
 * EntityFileLogTest class
 */
class EntityFileLogTest extends TestCase
{
    /**
     * Internal method to write some logs
     * @return void
     */
    protected function writeSomeLogs(): void
    {
        Log::error('This is an error message');
        Log::critical('This is a critical message');
    }

    /**
     * Test for `getLogAsObject()` method
     * @test
     */
    public function testGetLogAsObject()
    {
        $getLogAsObjectMethod = function () {
            return $this->invokeMethod(new EntityFileLog(), 'getLogAsObject', func_get_args());
        };

        $expectedAttributes = <<<ATTRIBUTES
array (
  'class' => 'NoExistingRoute',
  'plugin' => false,
  'prefix' => false,
  '_ext' => false,
)
ATTRIBUTES;

        $expectedTrace = <<<TRACE
#0 /home/mirko/Server/mirkopagliai/vendor/cakephp/cakephp/src/Http/ControllerFactory.php(72): Cake\Http\ControllerFactory->missingController(Object(Cake\Network\Request))
#1 /home/mirko/Server/mirkopagliai/vendor/cakephp/cakephp/src/Http/ActionDispatcher.php(92): Cake\Http\ControllerFactory->create(Object(Cake\Network\Request), Object(Cake\Network\Response))
#2 /home/mirko/Server/mirkopagliai/vendor/cakephp/cakephp/src/Http/BaseApplication.php(83): Cake\Http\ActionDispatcher->dispatch(Object(Cake\Network\Request), Object(Cake\Network\Response))
#3 /home/mirko/Server/mirkopagliai/vendor/cakephp/cakephp/src/Http/Runner.php(65): Cake\Http\BaseApplication->__invoke(Object(Zend\Diactoros\ServerRequest), Object(Zend\Diactoros\Response), Object(Cake\Http\Runner))
#4 /home/mirko/Server/mirkopagliai/vendor/cakephp/cakephp/src/Routing/Middleware/RoutingMiddleware.php(62): Cake\Http\Runner->__invoke(Object(Zend\Diactoros\ServerRequest), Object(Zend\Diactoros\Response))
#5 /home/mirko/Server/mirkopagliai/vendor/cakephp/cakephp/src/Http/Runner.php(65): Cake\Routing\Middleware\RoutingMiddleware->__invoke(Object(Zend\Diactoros\ServerRequest), Object(Zend\Diactoros\Response), Object(Cake\Http\Runner))
#6 /home/mirko/Server/mirkopagliai/vendor/cakephp/cakephp/src/Routing/Middleware/AssetMiddleware.php(88): Cake\Http\Runner->__invoke(Object(Zend\Diactoros\ServerRequest), Object(Zend\Diactoros\Response))
#7 /home/mirko/Server/mirkopagliai/vendor/cakephp/cakephp/src/Http/Runner.php(65): Cake\Routing\Middleware\AssetMiddleware->__invoke(Object(Zend\Diactoros\ServerRequest), Object(Zend\Diactoros\Response), Object(Cake\Http\Runner))
#8 /home/mirko/Server/mirkopagliai/vendor/cakephp/cakephp/src/Error/Middleware/ErrorHandlerMiddleware.php(81): Cake\Http\Runner->__invoke(Object(Zend\Diactoros\ServerRequest), Object(Zend\Diactoros\Response))
#9 /home/mirko/Server/mirkopagliai/vendor/cakephp/cakephp/src/Http/Runner.php(65): Cake\Error\Middleware\ErrorHandlerMiddleware->__invoke(Object(Zend\Diactoros\ServerRequest), Object(Zend\Diactoros\Response), Object(Cake\Http\Runner))
#10 /home/mirko/Server/mirkopagliai/vendor/cakephp/cakephp/src/Http/Runner.php(51): Cake\Http\Runner->__invoke(Object(Zend\Diactoros\ServerRequest), Object(Zend\Diactoros\Response))
#11 /home/mirko/Server/mirkopagliai/vendor/cakephp/cakephp/src/Http/Server.php(90): Cake\Http\Runner->run(Object(Cake\Http\MiddlewareQueue), Object(Zend\Diactoros\ServerRequest), Object(Zend\Diactoros\Response))
#12 /home/mirko/Server/mirkopagliai/webroot/index.php(37): Cake\Http\Server->run()
#13 {main}
TRACE;

        $result = $getLogAsObjectMethod('error', 'example of message');
        $this->assertTrue($result->has(['level', 'datetime', 'message', 'full']));
        $this->assertEquals('error', $result->get('level'));
        $this->assertRegExp('/^\d{4}\-\d{2}\-\d{2} \d{2}:\d{2}:\d{2}$/', $result->get('datetime'));
        $this->assertEquals('example of message', $result->get('message'));

        $result = $getLogAsObjectMethod('error', file_get_contents(TESTS . 'examples' . DS . 'stacktrace1'));
        $this->assertTrue($result->has(['level', 'datetime', 'exception', 'message', 'request', 'ip', 'trace', 'full']));
        $this->assertEquals('error', $result->get('level'));
        $this->assertRegExp('/^\d{4}\-\d{2}\-\d{2} \d{2}:\d{2}:\d{2}$/', $result->get('datetime'));
        $this->assertEquals(MissingControllerException::class, $result->get('exception'));
        $this->assertEquals('Controller class NoExistingRoute could not be found.', $result->get('message'));
        $this->assertEquals('/noExistingRoute', $result->get('request'));
        $this->assertEquals('1.1.1.1', $result->get('ip'));
        $this->assertEquals($expectedTrace, $result->get('trace'));

        $result = $getLogAsObjectMethod('error', file_get_contents(TESTS . 'examples' . DS . 'stacktrace2'));
        $this->assertTrue($result->has([
            'level',
            'datetime',
            'exception',
            'message',
            'attributes',
            'request',
            'referer',
            'ip',
            'trace',
            'full',
        ]));
        $this->assertEquals('error', $result->get('level'));
        $this->assertRegExp('/^\d{4}\-\d{2}\-\d{2} \d{2}:\d{2}:\d{2}$/', $result->get('datetime'));
        $this->assertEquals(MissingControllerException::class, $result->get('exception'));
        $this->assertEquals('Controller class NoExistingRoute could not be found.', $result->get('message'));
        $this->assertEquals($expectedAttributes, $result->get('attributes'));
        $this->assertEquals('/noExistingRoute', $result->get('request'));
        $this->assertEquals('/noExistingReferer', $result->get('referer'));
        $this->assertEquals('1.1.1.1', $result->get('ip'));
        $this->assertEquals($expectedTrace, $result->get('trace'));
    }

    /**
     * Test for `log()` method
     * @test
     */
    public function testLog()
    {
        //Writes some logs
        $this->writeSomeLogs();

        $this->assertLogContains('Error: This is an error message', 'error.log');
        $this->assertLogContains('Critical: This is a critical message', 'error.log');

        $logs = @unserialize(file_get_contents(LOGS . 'error_serialized.log'));
        $this->assertNotEmpty($logs);

        foreach ($logs as $log) {
            $this->assertInstanceOf(Entity::class, $log);
            $this->assertContains($log->get('level'), ['critical', 'error']);
            $this->assertRegExp('/^\d{4}\-\d{2}\-\d{2} \d{2}:\d{2}:\d{2}$/', $log->get('datetime'));
            $this->assertRegExp('/^This is (a critical|an error) message$/', $log->get('message'));
            $this->assertRegExp('/^[\d\-:\s]{19} (Critical|Error)/', $log->get('full'));
        }

        $this->skipIf(IS_WIN);
        $this->assertFileIsReadable(LOGS . 'error.log');
        $this->assertFileIsReadable(LOGS . 'error_serialized.log');
    }

    /**
     * Test for `log()` method, with a different `mask` value
     * @test
     */
    public function testLogWithDifferentMask()
    {
        //Drops and reconfigure adding `mask` option
        $oldConfig = Log::getConfig('error');
        Log::drop('error');
        Log::setConfig('error', $oldConfig + ['mask' => 0777]);

        //Writes some logs
        $this->writeSomeLogs();

        Log::drop('error');
        Log::setConfig('error', $oldConfig);

        $this->skipIf(IS_WIN);
        $this->assertFileIsWritable(LOGS . 'error.log');
        $this->assertFileIsWritable(LOGS . 'error_serialized.log');
    }
}
