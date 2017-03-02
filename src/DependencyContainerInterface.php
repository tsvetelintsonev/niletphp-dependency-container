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
interface DependencyContainerInterface {
    
    /**
     * Creates concrete instance.
     * @param string $concrete Concrete name.
     * @return Object Resolved $concrete instance with all its dependencies.
     */
    public function create($concrete);
    
    /**
     * Adds Interface - Implementation binding. 
     * Throws InterfaceImplementationException if the given concrete is not implementing the given interface.
     * @param string $interface Interface name.
     * @param \Closure|string $concrete Concrete name or \Closure instance.
     * @return void
     * @throws InterfaceImplementationException
     */
    public function bind($interface, $concrete);
    
    /**
     * Adds a "shared" concrete.
     * @param  string $concrete Class name.
     * @param \Closure $closure Closure instance. Its return value will be used when retrieving a shared dependency.
     * @return void
     */
    public function share($concrete, \Closure $closure = null);

    /**
     * Adds shared Interface - Implementation binding.
     * Throws InterfaceImplementationException if the given concrete is not implementing the given interface.
     * @param string $interface Interface name.
     * @param \Closure|string $concrete Concrete name or \Closure instance.
     * If $concrete is of type \Closure its return value will be used when retrieving a bound shared dependency.
     * @return void
     * @throws \InterfaceImplementationException
     */
    public function bindShared($interface, $concrete);
    
    /**
     * Adds instance.
     * @param  string $concrete Concrete name.
     * @param \Closure|object $instance Object or \Closure instance.
     * If $instance is of type \Closure its return value will be used when retrieving the concrete.
     * @throws \Exception
     */
    public function instance($concrete, $instance);
    
    /**
     * Retrieves a shared or boundShared instance.
     * @param type $concrete Concrete name.
     * @return Object Resolved $concrete instance with all its dependencies.
     * @throws MissingResolvedDependencyException
     */
    public function get($concrete);
    
    /**
     * Determines if a given concrete (singleton) has been resolved.
     * @param type $concrete Resolver name;
     * @return boolean
     */
    public function isResolved($concrete) : bool;

    /**
     * Determines if a given concrete is shared and not being resolved yet.
     * @param type $concrete Resolver name;
     * @return boolean
     */
    public function isShared($concrete) : bool;

    /**
     * Determines if a given interface has been bound and not being resolved yet.
     * @param type $concrete Resolver name;
     * @return boolean
     */
    public function isBound($interface) : bool;

    /**
     * Determines if a given interface has been bound as shared and not being resolved yet.
     * @param type $concrete Resolver name;
     * @return boolean
     */
    public function isBoundShared($interface) : bool;
    
    /**
     * Empties container.
     * @return void 
     */
    public function removeAll();
}
