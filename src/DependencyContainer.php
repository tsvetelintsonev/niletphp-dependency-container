<?php
/**
 * NiletPHP - Simple and lightweight web MVC framework
 * (c) Tsvetelin Tsonev <github.tsonev@yahoo.com>
 * For copyright and license information of this source code, please view the LICENSE file.
 */

namespace Nilet\Components\Container;

/**
 * @author Tsvetelin Tsonev <github.tsonev@yahoo.com>
 */
class DependencyContainer implements DependencyContainerInterface {

    /**
     * Shared concretes.
     * @var array
     */
    private $shared = [];

    /**
     * Interface - Implementation bindings.
     * @var array
     */
    private $bindings = [];

    /**
     * Shared Interface - Implementation bindings.
     * @var array
     */
    private $sharedBindings = [];

    /**
     * All registered and resolved shared instances identifiers. 
     * @var array
     */
    private $resolved = [];

    /**
     * All registered and resolved shared instances.
     * @var array
     */
    private $instances = [];

    /**
     * The currently resolved stack.
     * @var array
     */
    private $stack = [];

    /**
     * {@inheritdoc} 
     */
    public function create($concrete) {
        return $this->build($this->normalize($concrete));
    }

    /**
     * {@inheritdoc} 
     */
    public function bind($interface, $concrete) {
        $_concrete = $this->normalize($concrete);
        $_interface = $this->normalize($interface);
        $this->bindings[$_interface] = $_concrete;
    }

    /**
     * {@inheritdoc} 
     */
    public function share($concrete, \Closure $closure = null) {
        $_concrete = $this->normalize($concrete);
        $this->shared[$_concrete] = $closure ? $closure : $_concrete;
    }

    /**
     * {@inheritdoc} 
     */
    public function bindShared($interface, $concrete) {
        $_concrete = $this->normalize($concrete);
        $_interface = $this->normalize($interface);
        $this->sharedBindings[$_interface] = $_concrete;
    }

    /**
     * {@inheritdoc} 
     */
    public function instance($concrete, $instance) {
        $_concrete = $this->normalize($concrete);
        if ($instance instanceof $_concrete) {
            $this->instances[$_concrete] = $instance;
            $this->markAsResolved($_concrete);
        } else if ($instance instanceof \Closure) {
            // Add the \Closure instance to shared, so it can be lazy resolved afterwards.
            $this->shared[$_concrete] = $instance;
        } else {
            throw new InstanceOfException("Instance parameter should either be a Closure or instance of " . $concrete);
        }
    }

    /**
     * {@inheritdoc} 
     */
    public function get($concrete) {
        $_concrete = $this->normalize($concrete);
        if ($this->isResolved($_concrete)) {
            return $this->instances[$_concrete];
        } else if ($this->isShared($_concrete) || $this->isBoundShared($_concrete)) {
            $instance = $this->build($_concrete);
            $this->markAsResolved($_concrete);
            return $this->instances[$_concrete] = $instance;
        }
        throw new MissingResolvedDependencyException("Could not find resolved dependencies for " . $concrete);
    }

    /**
     * Resolves  all dependencies of a given concrete.
     * @param mixed $concrete
     * @return object New concrete instance
     */
    private function build($concrete) {
        $this->addToStack($concrete);

        if ($this->isBound($concrete)) {
            $concrete = $this->bindings[$concrete];
        } else if ($this->isBoundShared($concrete)) {
            $concrete = $this->sharedBindings[$concrete];
        } else if($this->isShared($concrete)) {
            $concrete = $this->shared[$concrete];
        }

        if ($concrete instanceof \Closure) {
            return $concrete($this);
        }
        
        $reflection = new \ReflectionClass($concrete);

        $constructor = $reflection->getConstructor();

        if ($constructor == null) {
            return new $concrete;
        }

        $parameters = $constructor->getParameters();
        $dependencies = $this->getDependencies($parameters);
        return $reflection->newInstanceArgs($dependencies);
    }

    /**
     * Resolves all dependencies from reflection parameters.
     * @param  array  $parameters
     * @return array
     */
    private function getDependencies(array $parameters): array {
        $dependencies = [];
        foreach ($parameters as $parameter) { /* @var $parameter \ReflectionParameter */
            $dependency = $parameter->getClass();

            if ($dependency == null) {
                $dependencies[] = $this->resolveNonClass($parameter);
            } else {
                $dependencies[] = $this->resolveClass($parameter);
            }
        }
        return $dependencies;
    }

    /**
     * Resolves a non class hinted dependency.
     * @param \ReflectionParameter $parameter
     * @return mixed
     * @throws \Exception
     */
    private function resolveNonClass(\ReflectionParameter $parameter) {
        if ($parameter->isOptional()) {
            return $parameter->getDefaultValue();
        }
        throw new \Exception("Unresolvable dependency resolving $parameter");
    }

    /**
     * Resolve a class hinted dependency
     * @param \ReflectionParameter $parameter
     * @return object The resolved object.
     * @throws \Exception
     */
    private function resolveClass(\ReflectionParameter $parameter) {
        // Class or Interface
        $concrete = $this->normalize($parameter->getClass()->name);
        return $this->build($concrete);
    }

    /**
     * Normalizes concrete name.
     * @param type $concrete Resolve name
     * @return string
     */
    private function normalize($concrete) {
        if (is_string($concrete)) {
            return trim($concrete, "\\");
        }
        return $concrete;
    }

    /**
     * Adds concrete name to the stack.
     * @param type $concrete Resolver name.
     */
    private function addToStack($concrete) {
        $this->stack[] = $concrete;
    }

    /**
     * Marks a given concrete as resolved.
     * @param string $concrete Concrete name.
     * @returrn void
     */
    private function markAsResolved(string $concrete) {
        $this->resolved[$concrete] = true;
    }

    /**
     * {@inheritdoc} 
     */
    public function isResolved($concrete): bool {
        return !empty($this->resolved[$concrete]);
    }

    /**
     * {@inheritdoc} 
     */
    public function isShared($concrete): bool {
        return !empty($this->shared[$concrete]);
    }

    /**
     * {@inheritdoc} 
     */
    public function isBound($interface): bool {
        return !empty($this->bindings[$interface]);
    }

    /**
     * {@inheritdoc} 
     */
    public function isBoundShared($interface): bool {
        return !empty($this->sharedBindings[$interface]);
    }

    /**
     * {@inheritdoc} 
     */
    public function removeAll() {
        $this->resolved = [];
    }
}
