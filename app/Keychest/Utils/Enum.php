<?php
/**
 * Created by PhpStorm.
 * User: dusanklinec
 * Date: 21.09.17
 * Time: 21:41
 */

namespace App\Keychest\Utils;
use Exception;
use ReflectionClass;


/**
 * @author Torge Kummerow
 */
class Enum {

    /**
     * Holds the values for each type of Enum
     */
    static private $list = array();

    /**
     * Initializes the enum values by replacing the number with an instance of itself
     * using reflection
     */
    static public function initialize() {
        $className = get_called_class();
        $class = new ReflectionClass($className);
        $staticProperties = $class->getStaticProperties();

        self::$list[$className] = array();

        foreach ($staticProperties as $propertyName => &$value) {
            if ($propertyName == 'list')
                continue;

            $enum = new $className($propertyName, $value);
            $class->setStaticPropertyValue($propertyName, $enum);
            self::$list[$className][$propertyName] = $enum;
        } unset($value);
    }


    /**
     * Gets the enum for the given value
     *
     * @param integer $value
     * @throws Exception
     *
     * @return Enum
     */
    static public function getByValue($value) {
        $className = get_called_class();
        foreach (self::$list[$className] as $propertyName=>&$enum) {
            /* @var $enum Enum */
            if ($enum->value == $value)
                return $enum;
        } unset($enum);

        throw new Exception("No such enum with value=$value of type ".get_called_class());
    }

    /**
     * Gets the enum for the given name
     *
     * @param string $name
     * @throws Exception
     *
     * @return Enum
     */
    static public function getByName($name) {
        $className = get_called_class();
        if (array_key_exists($name, static::$list[$className]))
            return self::$list[$className][$name];

        throw new Exception("No such enum ".get_called_class()."::\$$name");
    }


    /**
     * Returns the list of all enum variants
     * @return Array of Enum
     */
    static public function getList() {
        $className = get_called_class();
        return self::$list[$className];
    }


    private $name;
    private $value;

    public function __construct($name, $value) {
        $this->name = $name;
        $this->value = $value;
    }

    public function __toString() {
        return $this->name;
    }

    public function getValue() {
        return $this->value;
    }

    public function getName() {
        return $this->name;
    }

}
