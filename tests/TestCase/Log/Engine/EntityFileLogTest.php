<?php
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

use Cake\Core\Configure;
use Cake\Core\Plugin;
use Cake\Http\BaseApplication;
use Cake\Log\Log;
use Cake\TestSuite\TestCase;
use EntityFileLog\Log\Engine\EntityFileLog;
use Tools\ReflectionTrait;
use Tools\TestSuite\TestCaseTrait;

/**
 * EntityFileLogTest class
 */
class EntityFileLogTest extends TestCase
{
    use ReflectionTrait;
    use TestCaseTrait;

    /**
     * Internal method to write some logs
     */
    protected function writeSomeLogs()
    {
        Log::error('This is an error message');
        Log::critical('This is a critical message');
    }

    /**
     * Setup the test case, backup the static object values so they can be
     * restored. Specifically backs up the contents of Configure and paths in
     *  App if they have not already been backed up
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        if (version_compare(Configure::version(), '3.6', '>=')) {
            $app = $this->getMockForAbstractClass(BaseApplication::class, ['']);
            $app->addPlugin('EntityFileLog')->pluginBootstrap();
        } else {
            Plugin::load('EntityFileLog', ['bootstrap' => false, 'path' => ROOT]);
        }
    }

    /**
     * Teardown any static object changes and restore them
     * @return void
     */
    public function tearDown()
    {
        parent::tearDown();

        @unlink_recursive(LOGS);
    }

    /**
     * Test for `getLogAsObject()` method
     * @test
     */
    public function testGetLogAsObject()
    {
        $getLogAsObjectMethod = function () {
            $object = new EntityFileLog;

            return $this->invokeMethod($object, 'getLogAsObject', func_get_args());
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
        $this->assertEquals('error', $result->level);
        $this->assertRegExp('/^\d{4}\-\d{2}\-\d{2} \d{2}:\d{2}:\d{2}$/', $result->datetime);
        $this->assertEquals('example of message', $result->message);

        $result = $getLogAsObjectMethod('error', file_get_contents(TESTS . 'examples' . DS . 'stacktrace1'));
        $this->assertTrue($result->has(['level', 'datetime', 'exception', 'message', 'request', 'ip', 'trace', 'full']));
        $this->assertEquals('error', $result->level);
        $this->assertRegExp('/^\d{4}\-\d{2}\-\d{2} \d{2}:\d{2}:\d{2}$/', $result->datetime);
        $this->assertEquals('Cake\Routing\Exception\MissingControllerException', $result->exception);
        $this->assertEquals('Controller class NoExistingRoute could not be found.', $result->message);
        $this->assertEquals('/noExistingRoute', $result->request);
        $this->assertEquals('1.1.1.1', $result->ip);
        $this->assertEquals($expectedTrace, $result->trace);

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
        $this->assertEquals('error', $result->level);
        $this->assertRegExp('/^\d{4}\-\d{2}\-\d{2} \d{2}:\d{2}:\d{2}$/', $result->datetime);
        $this->assertEquals('Cake\Routing\Exception\MissingControllerException', $result->exception);
        $this->assertEquals('Controller class NoExistingRoute could not be found.', $result->message);
        $this->assertEquals($expectedAttributes, $result->attributes);
        $this->assertEquals('/noExistingRoute', $result->request);
        $this->assertEquals('/noExistingReferer', $result->referer);
        $this->assertEquals('1.1.1.1', $result->ip);
        $this->assertEquals($expectedTrace, $result->trace);
    }

    /**
     * Test for `log()` method
     * @test
     */
    public function testLog()
    {
        //Writes some logs
        $this->writeSomeLogs();

        $this->assertContains('Error: This is an error message', file_get_contents(LOGS . 'error.log'));
        $this->assertContains('Critical: This is a critical message', file_get_contents(LOGS . 'error.log'));

        $logs = @unserialize(file_get_contents(LOGS . 'error_serialized.log'));
        $this->assertNotEmpty($logs);

        foreach ($logs as $log) {
            $this->assertInstanceOf('Cake\ORM\Entity', $log);
            $this->assertContains($log->level, ['critical', 'error']);
            $this->assertRegExp('/^\d{4}\-\d{2}\-\d{2} \d{2}:\d{2}:\d{2}$/', $log->datetime);
            $this->assertRegExp('/^This is (a critical|an error) message$/', $log->message);
            $this->assertRegExp('/^[\d-:\s]{19} (Critical|Error)/', $log->full);
        }

        if (IS_WIN) {
            $this->markTestSkipped();
        }

        $this->assertFilePerms(LOGS . 'error.log', ['0644', '0664']);
        $this->assertFilePerms(LOGS . 'error_serialized.log', ['0644', '0664']);
    }

    /**
     * Test for `log()` method, with a different `mask` value
     * @test
     */
    public function testLogWithDifferentMask()
    {
        //Drops and reconfigure adding `mask` option
        $oldConfig = Log::config('error');
        Log::drop('error');
        Log::config('error', $oldConfig + ['mask' => 0777]);

        //Writes some logs
        $this->writeSomeLogs();

        Log::drop('error');
        Log::config('error', $oldConfig);

        if (IS_WIN) {
            $this->markTestSkipped();
        }

        $this->assertFilePerms(LOGS . 'error.log', '0777');
        $this->assertFilePerms(LOGS . 'error_serialized.log', '0777');
    }

    /**
     * Test for `log()` method on failure
     * @expectedException PHPUnit\Framework\Error\Warning
     * @test
     */
    public function testLogOnFailure()
    {
        $SerializedLog = $this->getMockBuilder(EntityFileLog::class)
            ->setConstructorArgs([['mask' => 0777, 'path' => LOGS]])
            ->setMethods(['checkPermissionMask'])
            ->getMock();

        $SerializedLog->method('checkPermissionMask')
            ->will($this->returnValue(false));

        $SerializedLog->log('error', 'a message');
    }
}
