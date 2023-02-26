<?php

namespace obex;

class Obex
{
    public static function createFilter(string $property, string $cmp = 'exists', $value = null, bool $value_is_expression = false)
    {
        return function (object $o) use ($property, $cmp, $value, $value_is_expression) {
            return static::propertyExpressionValue($o, $property, $cmp, $value, $value_is_expression);
        };
    }

    public static function createIntCmp(string $property, bool $desc = false)
    {
        return function (object $a, object $b) use ($property, $desc) {
            return ($desc ? -1 : 1) * ((int) static::propertyExpressionValue($a, $property) <=> (int) static::propertyExpressionValue($b, $property));
        };
    }

    public static function createStringCmp(string $property, bool $desc = false)
    {
        return function (object $a, object $b) use ($property, $desc) {
            return ($desc ? -1 : 1) * ((string) static::propertyExpressionValue($a, $property) <=> (string) static::propertyExpressionValue($b, $property));
        };
    }

    public static function filter($objectArray, $property, $cmp = 'exists', $value = null, $value_is_expression = false)
    {
        return array_values(array_filter($objectArray, function ($o) use ($property, $cmp, $value, $value_is_expression) {
            if (!is_object($o)) {
                error_response(__METHOD__ . ': encountered non-object');
            }

            if ($value_is_expression) {
                $value = static::propertyExpressionExists($o, $value) ? static::propertyExpressionValue($o, $value) : null;
            }

            if (!static::propertyExpressionExists($o, $property)) {
                if ($cmp == 'notexists') {
                    return true;
                }

                if ($cmp == 'exists') {
                    return false;
                }

                if ($cmp == 'is') {
                    return !$value;
                }

                if ($cmp == 'not') {
                    return (bool) $value;
                }

                if ($cmp == 'in') {
                    return in_array('', $value) || in_array(null, $value);
                }

                if ($cmp == 'notin') {
                    return !in_array('', $value) || in_array(null, $value);
                }

                if ($cmp == 'null') {
                    return true;
                }

                if ($cmp == 'notnull') {
                    return false;
                }

                if ($cmp == 'matches') {
                    return preg_match($value, '');
                }

                if ($cmp == 'truthy') {
                    return false;
                }

                if ($cmp == 'falsy') {
                    return true;
                }

                if ($cmp == 'lt') {
                    return strcmp('', $value) < 0;
                }

                if ($cmp == 'gt') {
                    return strcmp('', $value) > 0;
                }

                if ($cmp == 'lte') {
                    return strcmp('', $value) <= 0;
                }

                if ($cmp == 'gte') {
                    return strcmp('', $value) >= 0;
                }

                error_response(__METHOD__ . ': unsupported comparison');
            }

            if ($cmp == 'exists') {
                return true;
            }

            if ($cmp == 'notexists') {
                return false;
            }

            $resolved = static::propertyExpressionValue($o, $property);

            if ($cmp == 'is') {
                return $resolved == $value;
            }

            if ($cmp == 'not') {
                return $resolved != $value;
            }

            if ($cmp == 'in') {
                return in_array($resolved, $value);
            }

            if ($cmp == 'notin') {
                return !in_array($resolved, $value);
            }

            if ($cmp == 'null') {
                return is_null($resolved);
            }

            if ($cmp == 'notnull') {
                return !is_null($resolved);
            }

            if ($cmp == 'matches') {
                return preg_match($value, $resolved);
            }

            if ($cmp == 'truthy') {
                return (bool) $resolved;
            }

            if ($cmp == 'falsy') {
                return !(bool) $resolved;
            }

            if ($cmp == 'lt') {
                return strcmp($resolved, $value) < 0;
            }

            if ($cmp == 'gt') {
                return strcmp($resolved, $value) > 0;
            }

            if ($cmp == 'lte') {
                return strcmp($resolved, $value) <= 0;
            }

            if ($cmp == 'gte') {
                return strcmp($resolved, $value) >= 0;
            }

            error_response(__METHOD__ . ': unsupported comparison');
        }));
    }

    public static function find($objectArray, $property, $cmp = 'exists', $value = null)
    {
        $found = static::filter($objectArray, $property, $cmp, $value);

        return reset($found);
    }

    public static function findAll($objectArray, $property, $cmp = 'exists', $values = [])
    {
        return array_map(function($value) use ($objectArray, $property, $cmp) {
            return static::find($objectArray, $property, $cmp, $value);
        }, $values);
    }

    public static function index($objectArray, $property, $cmp = 'exists', $value = null)
    {
        foreach ($objectArray as $index => $object) {
            if (count(static::filter([$object], $property, $cmp, $value))) {
                return $index;
            }
        }
    }

    public static function indicies($objectArray, $property, $cmp = 'exists', $values = [])
    {
        return array_map(function($value) use ($objectArray, $property, $cmp) {
            return static::index($objectArray, $property, $cmp = 'exists', $value);
        }, $values);
    }

    public static function map($objectArray, $property)
    {
        $callback = function ($o) use ($property) {
            return static::propertyExpressionValue($o, $property);
        };

        return array_map($callback, $objectArray);
    }

    private static function parsePropertySubexpression($property)
    {
        if (strpos($property, '->') !== 0 && strpos($property, '[') !== 0) {
            $property = '->' . $property;
        }

        $parts = [];
        $matches = null;

        while (preg_match('/^(->)(@)?([^[>]+)/', $property, $matches) || preg_match('/^(\[)(@)?([^[]+)\]/', $property, $matches)) {
            if (substr($property, strlen($matches[0]) - 1, 2) == '->') {
                $matches[0] = substr($matches[0], 0, strlen($matches[0]) - 1);
                $matches[3] = substr($matches[3], 0, strlen($matches[3]) - 1);
            }

            $parts[] = (object) [
                'type' => $matches[1] == '->' ? 'object' : 'array',
                'prop' => $matches[3],
                'safely' => $matches[2] == '@',
            ];

            $property = substr($property, strlen($matches[0]));
        }

        if ($property) {
            error_response(__METHOD__ . ': invalid property expression');
        }

        return $parts;
    }

    private static function propertyExpressionExists(object $o, string $expression)
    {
        foreach (static::propertyExpressionSubs($expression) as $property) {
            if (!static::propertySubexpressionValue($o, $property, true)) {
                return false;
            }
        }

        return true;
    }

    private static function propertyExpressionSubs($expression)
    {
        return array_map('trim', explode('.', $expression));
    }

    private static function propertyExpressionValue(object $o, string $expression, bool $existence_check = false)
    {
        $result = null;

        foreach (static::propertyExpressionSubs($expression) as $property) {
            $value = static::propertySubexpressionValue($o, $property, $existence_check);

            if ($result === null) {
                $result = $value;
            } else {
                $result .= $value;
            }
        }

        return $result;
    }

    private static function propertySubexpressionExists(object $o, string $property)
    {
        return static::propertySubexpressionValue($o, $property, true);
    }

    private static function propertySubexpressionValue(object $o, string $property, bool $existence_check = false)
    {
        $return = &$o;

        foreach (static::parsePropertySubexpression($property) as $part) {
            if ($part->type == 'object') {
                if (!is_object($return)) {
                    error_response(__METHOD__ . ': not an object');
                }

                if (!in_array($part->prop, array_keys(get_object_vars($return)))) {
                    if ($existence_check) {
                        return false;
                    }

                    if ($part->safely) {
                        return null;
                    }

                    error_response(__METHOD__ . ': object property does not exist: ' . $part->prop);
                }

                if (is_array($return->{$part->prop})) {
                    $return = &$return->{$part->prop};
                } else {
                    $return = $return->{$part->prop};
                }
            } else {
                if (!is_array($return)) {
                    error_response(__METHOD__ . ': not an array');
                }

                if (!array_key_exists($part->prop, $return)) {
                    if ($existence_check) {
                        return false;
                    }

                    if ($part->safely) {
                        return null;
                    }

                    error_response(__METHOD__ . ': array key does not exist: ' . $part->prop);
                }

                if (is_array($return[$part->prop])) {
                    $return = &$return[$part->prop];
                } else {
                    $return = $return[$part->prop];
                }
            }
        }

        if ($existence_check) {
            return true;
        }

        return $return;
    }

    public static function remove(&$objectArray, $property, $cmp = 'exists', $value = null)
    {
        foreach ($objectArray as $key => $object) {
            if ($removed = static::find([$object], $property, $cmp, $value)) {
                unset($objectArray[$key]);

                return $removed;
            }
        }

        return false;
    }

    public static function removeAll(&$objectArray, $property, $cmp = 'exists', $value = null)
    {
        $removed = [];

        foreach ($objectArray as $key => $object) {
            if ($removedOne = static::find([$object], $property, $cmp, $value)) {
                unset($objectArray[$key]);

                $removed[] = $removedOne;
            }
        }

        return $removed;
    }
}
