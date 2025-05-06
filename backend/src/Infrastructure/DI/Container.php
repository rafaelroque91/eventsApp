<?php

declare(strict_types=1);

namespace App\Infrastructure\DI;

use App\Infrastructure\DI\Exceptions\ContainerException;
use App\Infrastructure\DI\Exceptions\NotFoundException;
use ReflectionClass;
use ReflectionParameter;
use ReflectionNamedType;
use Closure;

class Container
{
    private array $bindings = [];

    private array $instances = [];

    private array $singletons = [];

    /**
     * bind interface / class
     * @param string $id
     * @param callable|string $concrete
     * @return void
     */
    public function bind(string $id, callable|string $concrete): void
    {
        $this->bindings[$id] = $concrete;
        unset($this->singletons[$id]);
    }

    /**
     * register the singleton classes
     * @param string $id
     * @param callable|string $concrete
     * @return void
     */
    public function singleton(string $id, callable|string $concrete): void
    {
        $this->bindings[$id] = $concrete;
        $this->singletons[$id] = true;
    }

    /**
     * @param string $id
     * @param object $instance
     * @return void
     */
    public function instance(string $id, object $instance): void
    {
        $this->instances[$id] = $instance;
        $this->singletons[$id] = true;
    }

    /** get class from container
     * @param string $id
     * @return mixed
     * @throws ContainerException
     * @throws NotFoundException
     */
    public function get(string $id): mixed
    {
        if (isset($this->instances[$id])) {
            return $this->instances[$id];
        }

        if (isset($this->bindings[$id])) {
            $concrete = $this->bindings[$id];

            if ($concrete instanceof Closure) {
                $instance = $concrete($this);
            }
            elseif (is_string($concrete) && class_exists($concrete)) {
                if ($concrete !== $id) {
                    return $this->get($concrete);
                }
                $instance = $this->resolve($id);

            } else {
                throw new ContainerException("Binding for '{$id}' is not a callable or resolvable class name.");
            }

        } else {
            if (!class_exists($id)) {
                throw new NotFoundException("No binding or class found for '{$id}'.");
            }
            $instance = $this->resolve($id);
        }

        if (isset($this->singletons[$id])) {
            $this->instances[$id] = $instance;
        }

        return $instance;
    }

    /**
     * @param string $id
     * @return bool
     */
    public function has(string $id): bool
    {
        return isset($this->bindings[$id]) || isset($this->instances[$id]) || class_exists($id);
    }

    /**
     * @param string $className
     * @return object|mixed|string|null
     * @throws ContainerException
     * @throws \ReflectionException
     */
    private function resolve(string $className): object
    {
        try {
            $reflector = new ReflectionClass($className);
        } catch (\ReflectionException $e) {
            throw new ContainerException("Failed to reflect class '{$className}': " . $e->getMessage(), 0, $e);
        }

        // Check if class is instantiable
        if (!$reflector->isInstantiable()) {
            throw new ContainerException("Class '{$className}' is not instantiable.");
        }

        // Get the constructor
        $constructor = $reflector->getConstructor();

        // If no constructor, just instantiate
        if ($constructor === null) {
            return new $className();
        }

        // Get constructor parameters
        $parameters = $constructor->getParameters();
        $dependencies = [];

        foreach ($parameters as $parameter) {
            $dependency = $this->resolveParameter($parameter, $className);
            $dependencies[] = $dependency;
        }

        // Instantiate the class with resolved dependencies
        return $reflector->newInstanceArgs($dependencies);
    }

    /**
     * @param ReflectionParameter $parameter
     * @param string $consumerClassName
     * @return mixed
     * @throws ContainerException
     */
    private function resolveParameter(ReflectionParameter $parameter, string $consumerClassName): mixed
    {
        $type = $parameter->getType();

        // Check if parameter has a type hint
        if ($type instanceof ReflectionNamedType && !$type->isBuiltin()) {
            $typeName = $type->getName();
            // Prevent self-resolution loop for non-singleton concrete classes
            // (Though usually you'd bind interfaces or abstract classes)
            if ($typeName === $consumerClassName && !isset($this->singletons[$typeName])) {
                throw new ContainerException("Circular dependency detected for class '{$typeName}'.");
            }
            // Recursively resolve the dependency using the container's get method
            try {
                return $this->get($typeName);
            } catch (NotFoundException $e) {
                // If dependency not found and parameter has default value, use it
                if ($parameter->isDefaultValueAvailable()) {
                    return $parameter->getDefaultValue();
                }
                throw new ContainerException("Unresolvable dependency '{$typeName}' required by '{$consumerClassName}'.", 0, $e);
            }

        } elseif ($parameter->isDefaultValueAvailable()) {
            // If no type hint or built-in type, but has a default value
            return $parameter->getDefaultValue();
        } else {
            // No type hint, no default value - cannot resolve
            $paramName = $parameter->getName();
            throw new ContainerException("Cannot resolve parameter '{$paramName}' without type hint or default value in constructor of '{$consumerClassName}'.");
        }
    }
}