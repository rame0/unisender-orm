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

use rame0\UniORM\Classes\Exceptions\ORMException;
use rame0\UniORM\ORM;

class ContactList extends Base
{
    public $id = 0;
    public $title = '';
    private $before_subscribe_url = '';
    private $after_subscribe_url = '';

    /**
     * ContactList constructor.
     * @param string|array $title If is string new instance created with provided $title. If is array new instance will be filled with provided props
     * @throws ORMException
     */
    public function __construct($title)
    {
        if (is_array($title)) {
            $this->isNew = false;
            $this->id = (int)$title['id'];
            $this->title = (string)$title['title'];
        } elseif (empty($title)) {
            throw new ORMException('Title is empty.');
        } else {
            $this->title = $title;
        }
    }

    /**
     * Get available campaign lists
     * @return ContactList[]|Collection
     * @throws ORMException
     */
    public static function get(): Collection
    {
        $lists = ORM::getInstance()->getLists();

        $results = new Collection();
        foreach ($lists as $list) {
            $results->add((int)$list['id'], new static($list));
        }

        return $results;
    }

    /**
     * @return string
     */
    public function getAfterSubscribeUrl(): string
    {
        return $this->after_subscribe_url;
    }

    /**
     * @param string $after_subscribe_url
     */
    public function setAfterSubscribeUrl(string $after_subscribe_url): void
    {
        $this->after_subscribe_url = $after_subscribe_url;
    }

    /**
     * @return string
     */
    public function getBeforeSubscribeUrl(): string
    {
        return $this->before_subscribe_url;
    }

    /**
     * @param string $before_subscribe_url
     */
    public function setBeforeSubscribeUrl(string $before_subscribe_url): void
    {
        $this->before_subscribe_url = $before_subscribe_url;
    }

    /**
     * Save list
     * @return ContactList
     * @throws ORMException
     */
    public function save(): self
    {
        $params = [
            'title' => $this->title
        ];

        if (!empty($this->after_subscribe_url)) {
            $params['after_subscribe_url'] = $this->after_subscribe_url;
        }
        if (!empty($this->before_subscribe_url)) {
            $params['before_subscribe_url'] = $this->before_subscribe_url;
        }

        if ($this->isNew) {
            $result = ORM::getInstance()->createList($params);
            if (!empty($result) && !empty($result['id'])) {
                $this->id = (int)$result['id'];
            } else {
                throw new ORMException('List creation failed');
            }
        } else {
            $params['list_id'] = $this->id;
            ORM::getInstance()->updateList($params);
            $this->checkLogsAndThrow();
        }
        return $this;
    }

    /**
     * @return bool
     * @throws ORMException
     */
    public function delete(): bool
    {
        $params['list_id'] = $this->id;
        ORM::getInstance()->deleteList($params);

        $this->checkLogsAndThrow();

        return true;
    }
}