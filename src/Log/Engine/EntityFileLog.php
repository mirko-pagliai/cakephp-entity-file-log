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
namespace EntityFileLog\Log\Engine;

use Cake\Log\Engine\FileLog;
use Cake\ORM\Entity;
use Tools\FileArray;

/**
 * EntityFileLog class
 */
class EntityFileLog extends FileLog
{
    /**
     * Gets the log as an object. It splits the log information from the
     *  message, using regex
     * @param string $level The severity level of the message being written.
     *  See Cake\Log\Log::$_levels for list of possible levels
     * @param string $message The message you want to log
     * @return \Cake\ORM\Entity
     */
    protected function getLogAsObject($level, $message)
    {
        $log = new Entity();
        $log->set('level', $level);
        $log->set('datetime', date('Y-m-d H:i:s'));

        //Sets exception type and message
        if (preg_match('/^(\[([^\]]+)\]\s)?(.+)/', $message, $matches)) {
            if (!empty($matches[2])) {
                $log->set('exception', $matches[2]);
            }

            $log->set('message', $matches[3]);
        }

        //Sets the exception attributes
        if (preg_match(
            '/Exception Attributes:\s((.(?!Request URL|Referer URL|Client IP|Stack Trace|Trace))+)/is',
            $message,
            $matches
        )) {
            $log->set('attributes', $matches[1]);
        }

        //Sets the request URL
        if (preg_match('/^Request URL:\s(.+)$/mi', $message, $matches)) {
            $log->set('request', $matches[1]);
        }

        //Sets the referer URL
        if (preg_match('/^Referer URL:\s(.+)$/mi', $message, $matches)) {
            $log->set('referer', $matches[1]);
        }

        //Sets the client IP
        if (preg_match('/^Client IP:\s(.+)$/mi', $message, $matches)) {
            $log->set('ip', $matches[1]);
        }

        //Sets the trace
        if (preg_match('/(Stack )?Trace:\n(.+)$/is', $message, $matches)) {
            $log->set('trace', trim($matches[2]));
        }

        //Adds the full log
        $log->set('full', trim(sprintf('%s %s: %s', date('Y-m-d H:i:s'), ucfirst($level), $message)));

        return $log;
    }

    /**
     * Internal method to check the permission mask.
     * Useful only for obtaining a stub method
     * @param bool $selfError Self error
     * @param bool $exists If the log exists
     * @param string $pathname Path
     * @param int $mask Mask
     * @return bool
     */
    protected function checkPermissionMask($selfError, $exists, $pathname, $mask)
    {
        return !(!$selfError && !$exists && !chmod($pathname, (int)$mask));
    }

    /**
     * Implements writing to log files.
     *
     * Each time that is called, it writes the normal log (using the
     *  `FileLog::log` method) and a serialized copy of the log.
     * For example, if the log is `error.log`, the serialized log will be
     *  `error_serialized.log`.
     * @param string $level The severity level of the message being written.
     *  See Cake\Log\Log::$_levels for list of possible levels.
     * @param string $message The message you want to log.
     * @param array $context Additional information about the logged message
     * @return bool success of write
     * @uses checkPermissionMask()
     * @uses getLogAsObject()
     */
    public function log($level, $message, array $context = [])
    {
        //First of all, it normally writes log
        $parent = parent::log($level, $message, $context);

        /*
         * Now, it writes the serialized log
         */
        $message = $this->_format($message, $context);
        $filename = $this->_getFilename($level);

        //It sets a new filename, adding the `_serialized` suffix.
        //For example, if the log is `error.log`, the serialized log will be
        //  `error_serialized.log`
        $filename = sprintf('%s_serialized.log', pathinfo($filename, PATHINFO_FILENAME));

        if (!empty($this->_size)) {
            $this->_rotateFile($filename);
        }

        $pathname = $this->_path . $filename;
        $mask = $this->_config['mask'];

        $data = $this->getLogAsObject($level, $message);
        $FileArray = (new FileArray($pathname))->prepend($data);

        if (empty($mask)) {
            return $parent && $FileArray->write();
        }

        $exists = file_exists($pathname);
        $result = $FileArray->write();
        static $selfError = false;

        if (!$this->checkPermissionMask($selfError, $exists, $pathname, $mask)) {
            $selfError = true;
            trigger_error(vsprintf(
                'Could not apply permission mask "%s" on log file "%s"',
                [$mask, $pathname]
            ), E_USER_WARNING);
            $selfError = false;
        }

        return $parent && $result;
    }
}
