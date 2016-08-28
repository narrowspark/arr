<?php
namespace Narrowspark\Arr;

use BadMethodCallException;
use ReflectionClass;
use ReflectionMethod;

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
        Traverse::class,
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
     *
     * @return mixed
     */
    public function __call($name, $args)
    {
        $this->getMethodArgs();

        if (! isset($this->methodArgs[$name])) {
            throw new BadMethodCallException(sprintf('%s is not a valid method.', $name));
        }

        foreach ($this->classes as $class) {
            if (method_exists($class, $name)) {
                return call_user_func_array([new $class(), $name], $args);
            }
        }
    }

    /**
     * Get all methods arguments.
     */
    protected function getMethodArgs()
    {
        if (! $this->methodArgs) {
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
}
