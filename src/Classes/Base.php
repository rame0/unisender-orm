<?php
/**
 * This file is part of UnisenderORM.
 *
 * 2019 (c) Ramil Aliyakberov (RAMe0) <r@me0.biz>
 *
 * For the full copyright and license information, please view the
 * LICENSE file that was distributed with this source code.
 */

namespace rame0\UniORM\Classes;


use JsonSerializable;
use rame0\UniORM\Classes\Exceptions\ORMException;
use rame0\UniORM\ORM;

class Base implements JsonSerializable
{
    /**
     * @var bool Is this is new instance or loaded from server
     */
    protected $isNew = true;

    /**
     * Implement save method to objects!
     * @throws ORMException
     */
    public function save()
    {
        throw new ORMException('Save method not implemented');
    }

    /**
     * @param bool $throw_on_warn
     * @throws ORMException
     */
    protected function checkLogsAndThrow($throw_on_warn = true)
    {
        if (!empty(ORM::getRequestErrorLog())) {
            throw new ORMException('Action failed. Check ORM::getRequestErrorLog() for more info.');
        }
        if ($throw_on_warn && !empty(ORM::getRequestWarnLog())) {
            throw new ORMException('Action ended with warning. Check ORM::getRequestWarnLog() for more info.');
        }
    }

    /**
     * Implement asArray method to objects!
     * @return array
     * @throws ORMException
     */
    public function asArray(): array
    {
        return (array)$this;
    }

    /**
     * Specify data which should be serialized to JSON
     * @link https://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     * @throws ORMException
     */
    public function jsonSerialize()
    {
        return $this->asArray();
    }
}