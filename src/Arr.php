<?php
namespace Narrowspark\Arr;

use BadMethodCallException;
use ReflectionClass;
use ReflectionMethod;
use RuntimeException;

class Arr
{
    /**
     * A mapping of method names to the numbers of arguments it accepts. Each
     * should be two more than the equivalent method.
     *
     * @var string[]
     */
     protected $classes = [
        Access::class,
        Enumerator::class,
        Transform::class,
        Traverse::class
    ];

    protected $methodArgs = null;

    /**
     * Invokes the given method with the rest of the passed arguments.
     * The result is not cast, so the return value may be of type Arr, array,
     * integer, boolean, etc.
     *
     * @param string  $name
     * @param mixed[] $args
     *
     * @throws \BadMethodCallException
     * @throws \RuntimeException
     *
     * @return mixed
     */
    public function __call($name, $args)
    {
        $this->getMethodArgs($name);

        if (!isset($this->methodArgs[$name])) {
            throw new BadMethodCallException(sprintf('%s is not a valid method.', $name));
        }

        if (count($args) !== $this->methodArgs[$name]) {
            throw new RuntimeException(
                sprintf(
                    '%s counted arguments dont match needed arguments %s for function %s.',
                    count($args),
                    count($this->methodArgs[$name]),
                    $name
                )
            );
        }

        foreach ($this->classes as $class) {
            if (method_exists($class, $name)) {
                $this->getFunction(new $class(), $name, $args);
            }
        }
    }

    /**
     * Get all methods arguments.
     *
     * @param string $name
     */
    protected function getMethodArgs($name)
    {
        if (!$this->methodArgs) {
            foreach ($this->classes as $classInterface) {
                $class = new ReflectionClass($classInterface);
                $methods = $class->getMethods(ReflectionMethod::IS_PUBLIC);

                foreach ($methods as $method) {
                    $params = $method->getNumberOfParameters();
                    $this->methodArgs[$method->name] = $params;
                }
            }
        }
    }

    /**
     * Get function from the correct object.
     *
     * @param object $instance
     * @param string $method
     * @param array  $args
     *
     * @return mixed
     */
    protected function getFunction($instance, $method, $args)
    {
        switch (count($args)) {
            case 2:
                return $instance->$method($args[0], $args[1]);

            case 3:
                return $instance->$method($args[0], $args[1], $args[2]);

            case 4:
                return $instance->$method($args[0], $args[1], $args[2], $args[3]);
        }
    }
}
