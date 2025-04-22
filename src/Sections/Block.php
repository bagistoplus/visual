<?php

namespace BagistoPlus\Visual\Sections;

class Block
{
    protected string $type;

    protected string $name;

    protected ?int $limit = 16;

    protected array $settings = [];

    public function __construct(string $type, string $name)
    {
        $this->type = $type;
        $this->name = $name;
    }

    public static function make(string $type, string $name): self
    {
        return new self($type, $name);
    }

    public function limit(int $limit): self
    {
        $this->limit = $limit;

        return $this;
    }

    public function settings(array $settings): self
    {
        $this->settings = $settings;

        return $this;
    }

    public function toArray(): array
    {
        return [
            'type' => $this->type,
            'name' => $this->name,
            'limit' => $this->limit,
            'settings' => array_map(fn ($setting) => $setting->toArray(), $this->settings),
        ];
    }
}
