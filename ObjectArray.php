<?php

namespace OranFry\Obex;

use Closure;

class ObjectArray
{
    protected array $objects;

    public function __construct(array $objects = [])
    {
        foreach ($objects as $thing) {
            if (!is_object($thing)) {
                throw new Exception('Encounterd a non-object');
            }
        }

        $this->objects = $objects;
    }

    public function append(array $objects): self
    {
        foreach ($objects as $thing) {
            if (!is_object($thing)) {
                throw new Exception('Encounterd a non-object');
            }
        }

        $this->objects = array_merge($this->objects, $objects);

        return $this;
    }

    public function count(): int
    {
        return count($this->objects);
    }

    public function delete(string $property, string $cmp = 'exists', $value = null, $value_is_expression = false): self
    {
        $copy = $this->objects;

        Obex::remove($copy, $property, $cmp, $value, $value_is_expression);

        return new static($copy);
    }

    public function filter(string $property, string $cmp = 'exists', $value = null, $value_is_expression = false): self
    {
        return new static(Obex::filter($this->objects, $property, $cmp, $value, $value_is_expression));
    }

    public function filterOut(string $property, string $cmp = 'exists', $value = null, $value_is_expression = false): self
    {
        $copy = $this->objects;

        Obex::removeAll($copy, $property, $cmp, $value, $value_is_expression);

        return new static($copy);
    }

    public function find(string $property, string $cmp = 'exists', $value = null, $value_is_expression = false): ?object
    {
        return Obex::find($this->objects, $property, $cmp, $value, $value_is_expression);
    }

    public function findAll(string $property, string $cmp = 'exists', $values = [], $value_is_expression = false): self
    {
        return new static(Obex::findAll($this->objects, $property, $cmp, $values, $value_is_expression));
    }

    public function first(): ?object
    {
        return reset($this->objects);
    }

    public function index(string $property, string $cmp = 'exists', $value = null, $value_is_expression = false): string|int
    {
        return Obex::index($this->objects, $property, $cmp, $value, $value_is_expression);
    }

    public function indicies(string $property, string $cmp = 'exists', $values = [], $value_is_expression = false): array
    {
        return Obex::indicies($this->objects, $property, $cmp, $values, $value_is_expression);
    }

    public function key(string|Closure $property): self
    {
        return new static(Obex::key($this->objects, $property));
    }

    public function map(string|Closure $property): array
    {
        return Obex::map($this->objects, $property);
    }

    public function plus(self $oa): self
    {
        $this->objects = array_merge($this->objects, $oa->resolve());

        return $this;
    }

    public function push(object $object): self
    {
        $this->objects[] = $object;

        return $this;
    }

    public function remove(string $property, string $cmp = 'exists', $value = null, $value_is_expression = false): ?object
    {
        return Obex::remove($this->objects, $property, $cmp, $value, $value_is_expression);
    }

    public function removeAll(string $property, string $cmp = 'exists', $value = null, $value_is_expression = false): array
    {
        return Obex::removeAll($this->objects, $property, $cmp, $value, $value_is_expression);
    }

    public function resolve(): array
    {
        return $this->objects;
    }
}