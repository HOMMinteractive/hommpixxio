<?php

namespace homm\hommpixxio\models;

use craft\base\Model;
use Craft;

class PixxioValue extends Model
{
    private array $data = [];

    public function __construct(array $data = [], array $config = [])
    {
        $this->data = $data;
        parent::__construct($config);
    }

    public function __get($name)
    {
        $method = 'get' . ucfirst($name);

        if (method_exists($this, $method)) {
            return $this->$method();
        }

        return parent::__get($name);
    }

    // ---------------------------------------------------------
    //  Accessible properties
    // ---------------------------------------------------------

    public function toArray(array $fields = [], array $expand = [], $recursive = true)
    {
        if (empty($fields)) {
            return $this->data;
        }

        $data = [];
        foreach ($fields as $field) {
            $data[$field] = $this->data[$field] ?? null;
        }

        return $data;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function getId(): ?string
    {
        return $this->data['id'] ?? null;
    }

    public function getName(): ?string
    {
        return $this->data['name'] ?? null;
    }

    public function getDirectory(): ?string
    {
        return $this->data['directory'] ?? null;
    }

    public function getUrl(): ?string
    {
        if (empty($this->data['id'])) {
            return null;
        }

        $base = Craft::$app->sites->currentSite->getBaseUrl();

        return rtrim($base, '/') . '/hommpixxio/files/' . $this->data['id'];
    }
}
