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

class ContactsList extends Base
{
    private $id = 0;
    private $title = '';
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
            $this->title = (string)$title;
        }
    }

    /**
     * Get available campaign lists
     * @return ContactsList[]|Collection
     * @throws ORMException
     * @throws RequestException
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
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    /**
     * @return bool
     */
    public function isNew(): bool
    {
        return $this->isNew;
    }

    /**
     * (!!)This value can only be set. It's not available when retrieving lists from server.
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
     * (!!)This value can only be set. It's not available when retrieving lists from server.
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
     * @return ContactsList
     * @throws ORMException
     * @throws RequestException
     */
    public function save(): self
    {
        if ($this->isNew) {
            $this->id = ORM::getInstance()->createList($this->title, $this->before_subscribe_url, $this->after_subscribe_url);
            $this->isNew = false;
        } else {
            ORM::getInstance()->updateList($this->id, $this->title, $this->before_subscribe_url, $this->after_subscribe_url);
        }

        $this->checkLogsAndThrow();
        return $this;
    }

    /**
     * @return bool
     * @throws ORMException
     * @throws RequestException
     */
    public function delete(): bool
    {
        ORM::getInstance()->deleteList($this->id);

        $this->checkLogsAndThrow();

        return true;
    }
}