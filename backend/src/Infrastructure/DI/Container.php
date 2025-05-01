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

    public function bind(string $id, callable|string $concrete): void
    {
        $this->bindings[$id] = $concrete;
        unset($this->singletons[$id]);
    }

    public function singleton(string $id, callable|string $concrete): void
    {
        $this->bindings[$id] = $concrete;
        $this->singletons[$id] = true;
    }

    public function instance(string $id, object $instance): void
    {
        $this->instances[$id] = $instance;
        $this->singletons[$id] = true;
    }

    public function get(string $id): mixed
    {
        //Return existing singleton instance if available
        if (isset($this->instances[$id])) {
            return $this->instances[$id];
        }

        //Check if an explicit binding exists
        if (isset($this->bindings[$id])) {
            $concrete = $this->bindings[$id];

            // If the binding is a factory closure, call it
            if ($concrete instanceof Closure) {
                $instance = $concrete($this);
            }
            // If the binding is a class name, resolve it (might be the same as $id)
            elseif (is_string($concrete) && class_exists($concrete)) {
                // If the binding points to a different class, resolve that one instead
                // Otherwise (if $concrete === $id), we fall through to auto-wiring below
                if ($concrete !== $id) {
                    return $this->get($concrete); // Resolve the target class
                }
                // If $concrete === $id, let auto-wiring handle it below
                $instance = $this->resolve($id);

            } else {
                // If it's neither a closure nor a class name, maybe it's a primitive value?
                // Or throw an exception if only objects/factories are expected.
                // For simplicity, we assume bindings are callables or class names for now.
                throw new ContainerException("Binding for '{$id}' is not a callable or resolvable class name.");
            }

        } else {
            //No explicit binding, attempt auto-wiring via Reflection
            if (!class_exists($id)) {
                throw new NotFoundException("No binding or class found for '{$id}'.");
            }
            $instance = $this->resolve($id);
        }


        //Store instance if it's meant to be a singleton
        if (isset($this->singletons[$id])) {
            $this->instances[$id] = $instance;
        }

        return $instance;
    }

    public function has(string $id): bool
    {
        return isset($this->bindings[$id]) || isset($this->instances[$id]) || class_exists($id);
    }

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