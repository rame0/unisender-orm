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


use ArrayAccess;
use Countable;
use Iterator;
use JsonSerializable;
use rame0\UniORM\Classes\Exceptions\ORMException;

class Collection implements Iterator, ArrayAccess, Countable, JsonSerializable
{

    /**
     * Collection of objects
     * @var array
     */
    protected $_collection;

    /**
     * Pointer
     * @var int
     */
    private $_position = 0;

    /**
     * Collection constructor.
     * @param array|null $collection
     */
    public function __construct(array $collection = null)
    {
        if (empty($collection)) {
            $this->_collection = [];
        } else {
            $this->_collection = $collection;
        }
    }

    // implementation of Iterator, Countable
    public function rewind()
    {
        reset($this->_collection);
        $this->_position = key($this->_collection);
    }

    /**
     * @return mixed
     */
    public function current()
    {
        return $this->_collection[$this->_position];
    }

    /**
     * @return int|mixed
     */
    public function key()
    {
        return $this->_position;
    }

    public function next()
    {
        next($this->_collection);
        $this->_position = key($this->_collection);
    }

    /**
     * @return bool
     */
    public function valid()
    {
        return isset($this->_collection[$this->_position]);
    }

    /**
     * @return int
     */
    public function count()
    {
        return count($this->_collection);
    }


    /**
     * @param mixed $offset
     * @return bool
     */
    public function offsetExists($offset)
    {
        return isset($this->_collection[$offset]);
    }

    /**
     * @param mixed $offset
     * @return mixed|null
     */
    public function offsetGet($offset)
    {
        return isset($this->_collection[$offset]) ? $this->_collection[$offset] : null;
    }

    /**
     * @param mixed $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value)
    {
        if (empty($offset)) {
            $this->add($value);
        } else {
            $this->add($offset, $value);
        }
    }

    /**
     * @param mixed $offset
     */
    public function offsetUnset($offset)
    {
        unset($this->_collection[$offset]);
        $this->rewind();
    }

    /**
     * @return array
     */
    public function getCollection()
    {
        return $this->_collection;
    }

    /**
     * @param mixed $key_or_val if second param is NULL, this one will be added like value
     * else, this param will be used as key
     * @param null $value value to add to collection OR NULL if value is given in first parameter
     */
    public function add($key_or_val, $value = null)
    {
        if (is_null($value)) {
            $this->_collection[] = $key_or_val;
        } else {
            $this->_collection[$key_or_val] = $value;
        }
    }

    /**
     * @param $offset
     * @param bool $remote_delete also delete from remote. Item in collection have to implement delete() method.
     * @throws ORMException
     */
    public function delete($offset, $remote_delete = false)
    {
        $deleted = true;
        if (!$this->offsetExists($offset)) {
            throw new ORMException("Offset '$offset' does not exists");
        }
        if ($remote_delete) {
            if (!method_exists($this->_collection[$offset], 'delete')) {
                throw new ORMException('Object does not implements delete() method');
            }
            $deleted = $this->_collection[$offset]->delete();
        }

        if ($deleted) {
            $this->offsetUnset($offset);
        }
    }


    /**
     * @return bool
     */
    public function isEmpty()
    {
        return empty($this->_collection);
    }

    /**
     * @return bool
     */
    public function notEmpty()
    {
        return !empty($this->_collection);
    }

    /**
     * @return array
     */
    public function getKeys(): array
    {
        return array_keys($this->_collection);
    }

    /**
     * @return array
     */
    public function getValues(): array
    {
        return array_values($this->_collection);
    }

    /**
     * @param string $column_name
     * @param string|null $index_key
     * @return array
     */
    public function getColumn(string $column_name, string $index_key = null)
    {
        return array_column($this->_collection, $column_name, $index_key);
    }

    /**
     * @throws ORMException
     */
    public function save(): self
    {
        /** @var Base $item */
        foreach ($this->_collection as $item) {
            $item->save();
        }
        return $this;
    }

    /**
     * @param string $field
     * @return Collection
     * @throws ORMException
     */
    public function sortByFields(string $field): self
    {
        if (!empty($this->_collection) && isset($this->_collection[0]->$field)) {
            usort($this->_collection, function ($a, $b) use ($field) {
                return $a->$field <=> $b->$field;
            });
        } else {
            throw new ORMException('Unknown field ' . $field . ' provided for sorting');
        }
        return $this;
    }

    /**
     * @return array|mixed|null
     */
    public function jsonSerialize()
    {
        if (empty($this->_collection)) {
            return null;
        }
        $result = [];
        foreach ($this->_collection as $key => $item) {
            $result[$key] = $item->jsonSerialize();
        }
        return $result;
    }

    /**
     * @return array
     */
    public function asArray()
    {
        if (empty($this->_collection)) {
            return [];
        }
        $result = [];
        foreach ($this->_collection as $key => $item) {
            $result[$key] = $item->asArray();
        }
        return $result;
    }
}