<?php
/**
 * Veto.
 * PHP Microframework.
 *
 * @author Damien Walsh <me@damow.net>
 * @copyright Damien Walsh 2013-2014
 * @version 0.1
 * @package veto
 */
namespace Veto\HTTP;

use Veto\Collection\Bag;

/**
 * HeaderBag - a Bag of HTTP headers
 */
class HeaderBag implements \IteratorAggregate
{
    /**
     * @var mixed[]
     */
    protected $headerFields = array();


    /**
     * Initialise the object
     *
     * @param array $headerFields The initial fields to create the bag with.
     */
    public function __construct(array $headerFields = array())
    {
        foreach ($headerFields as $name => $value) {

            // Fields should always be wrapped in an array
            if (!is_array($value)) {
                $value = array($value);
            }

            $normalizedName = $this->normalizeKey($name);
            $this->headerFields[$normalizedName] = $value;
        }
    }

    /**
     * Create a new HeaderBag, derived from the provided environment bag.
     *
     * @param Bag $environment The environment variables
     * @return self
     */
    public static function createFromEnvironment(Bag $environment)
    {
        $headers = new static();

        foreach ($environment->all() as $key => $value) {
            if ('HTTP_' === substr($key, 0, 5)) {
                $normalizedKey = static::normalizeFromEnvironment($key);

                $headers->add($normalizedKey, $value);
            }
        }

        return $headers;
    }

    /**
     * Normalizes the field names from PHP's uppercase HTTP_* style to format in a standard HTTP field.
     *
     * @param string $name
     * @return string
     */
    private static function normalizeFromEnvironment($name)
    {
        return static::normalizeKey(str_replace('_', '-', substr($name, 5)));
    }

    /**
     * Normalises the field names so that they always have the correct casing.
     *
     * @param string $name
     * @return string
     */
    private function normalizeKey($name)
    {
        return implode(
            '-',
            array_map(
                function($value) {
                    return ucfirst(strtolower($value));
                },
                explode('-', $name)
            )
        );
    }

    /**
     * Add a header to the bag
     *
     * @param mixed $key
     * @param mixed $value
     * @return $this
     */
    public function add($key, $value)
    {
        $normalizedKey = $this->normalizeKey($key);

        if (!array_key_exists($normalizedKey, $this->headerFields)) {
            $this->headerFields[$normalizedKey] = array();
        }

        $this->headerFields[$normalizedKey][] = $value;

        return $this;
    }

    /**
     * Get a header from the bag by key
     *
     * @param mixed $key
     * @param array $default The default value if no header matches
     * @return array
     */
    public function get($key, $default = array())
    {
        $normalizedKey = $this->normalizeKey($key);

        return (array_key_exists($normalizedKey, $this->headerFields))
            ? $this->headerFields[$normalizedKey]
            : $default;
    }

    /**
     * Check if the bag contains a given key
     *
     * @param mixed $key
     * @return bool
     */
    public function has($key)
    {
        $normalizedKey = $this->normalizeKey($key);

        return array_key_exists($normalizedKey, $this->headerFields);
    }

    /**
     * If the bag contains the given key, return the value, then remove it from the bag.
     *
     * @param $key
     * @return mixed|null
     */
    public function remove($key)
    {
        $normalizedKey = $this->normalizeKey($key);

        $value = array();
        if (array_key_exists($normalizedKey, $this->headerFields)) {
            $value = $this->headerFields[$normalizedKey];
            unset($this->headerFields[$normalizedKey]);
        }

        return $value;
    }

    /**
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->headerFields);
    }

    /**
     * Get the underlying array of the contents of the bag.
     *
     * @return array
     */
    public function all()
    {
        return $this->headerFields;
    }
}
