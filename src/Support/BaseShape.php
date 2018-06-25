<?php

namespace BlackBits\ApiConsumer\Support;

use BlackBits\ApiConsumer\Contracts\ShapeContract;
use Illuminate\Contracts\Support\Arrayable;

abstract class BaseShape implements ShapeContract, Arrayable
{
    protected $return_shape_data_only = false;
    protected $require_shape_structure = false;

    protected $transformations = [];

    protected $fields = [];

    protected $attributes = [];

    public function toArray()
    {
        return $this->attributes;
    }

    /**
     * @param $data
     * @return BaseShape
     * @throws \Exception
     */
    static function create($data)
    {
        $shape = new static();
        foreach ($data as $key => $value) {
            $shape->set($key, $value);
        }

        $shape->validateStructure();
        return $shape;
    }

    /**
     * @return bool
     */
    public function isReturnShapeDataOnly(): bool
    {
        return $this->return_shape_data_only;
    }

    /**
     * @return bool
     */
    public function isRequireShapeStructure(): bool
    {
        return $this->require_shape_structure;
    }

    /**
     * @return array
     */
    public function getFields(): array
    {
        return $this->fields;
    }

    /**
     * @return array
     */
    public function getTransformations(): array
    {
        return $this->transformations;
    }

    /**
     * @param $key
     * @param $value
     */
    public function set($key, $value)
    {
        if (isset($this->transformations[$key])) {
            $key = $this->transformations[$key];
        }
        if ($this->return_shape_data_only && !in_array($key, $this->fields)) {
            return;
        }

        $this->attributes[$key] = $value;
    }

    /**
     * @throws \Exception
     */
    public function validateStructure()
    {
        if (!$this->require_shape_structure) {
            return;
        }
        foreach ($this->fields as $field) {
            if (!isset($this->attributes[$field]))  {
                throw new \Exception("Shape is missing data field: '{$field}'");
            }
        }
    }

     public function hasOne($endpoint, $field)
     {
         $endpoint_name = explode("\\", $endpoint);
         $endpoint_name = array_pop($endpoint_name);
         $endpoint_name = str_replace("Endpoint", "", $endpoint_name);

         $consumer_name = explode("\\", $endpoint);
         $consumer_name = array_slice($consumer_name, 0, count($consumer_name) -2);
         $consumer_name = "\\" . implode("\\", $consumer_name) . "\\" . $consumer_name[count($consumer_name) -1];

         return $consumer_name::$endpoint_name()->find($this->attributes[$field]);
     }

     public function hasMany($endpoint, $field)
     {
         $endpoint_name = explode("\\", $endpoint);
         $endpoint_name = array_pop($endpoint_name);
         $endpoint_name = str_replace("Endpoint", "", $endpoint_name);

         $consumer_name = explode("\\", $endpoint);
         $consumer_name = array_slice($consumer_name, 0, count($consumer_name) -2);
         $consumer_name = "\\" . implode("\\", $consumer_name) . "\\" . $consumer_name[count($consumer_name) -1];

         return $consumer_name::$endpoint_name()->findMany($this->attributes[$field]);
     }
}