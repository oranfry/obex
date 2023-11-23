<?php

namespace obex;

class ObjectArray
{
    protected array $objects;

    public function __construct(array $objects = [])
    {
        $this->objects = $objects;
    }

    public function filter(string $property, string $cmp = 'exists', $value = null, $value_is_expression = false): self
    {
        return new static(Obex::filter($this->objects, $property, $cmp, $value, $value_is_expression));
    }

    public function find(string $property, string $cmp = 'exists', $value = null, $value_is_expression = false): ?object
    {
        return Obex::find($this->objects, $property, $cmp, $value, $value_is_expression);
    }

    public function findAll(string $property, string $cmp = 'exists', $values = [], $value_is_expression = false): self
    {
        return new static(Obex::findAll($this->objects, $property, $cmp, $values, $value_is_expression));
    }

    public function index(string $property, string $cmp = 'exists', $value = null, $value_is_expression = false)
    {
        return Obex::index($this->objects, $property, $cmp, $value, $value_is_expression);
    }

    public function indicies(string $property, string $cmp = 'exists', $values = [], $value_is_expression = false): array
    {
        return Obex::indicies($this->objects, $property, $cmp, $values, $value_is_expression);
    }

    public function map(string $property): self
    {
        return new static(Obex::map($this->objects, $property));
    }

    public function remove(string $property, string $cmp = 'exists', $value = null, $value_is_expression = false): self
    {
        $copy = $this->objects;

        Obex::remove($copy, $property, $cmp, $value, $value_is_expression);

        return new static($copy);
    }

    public function removeAll(string $property, string $cmp = 'exists', $value = null, $value_is_expression = false): self
    {
        $copy = $this->objects;

        Obex::removeAll($copy, $property, $cmp, $value, $value_is_expression);

        return new static($copy);
    }

    public function resolve(): array
    {
        return $this->objects;
    }
}