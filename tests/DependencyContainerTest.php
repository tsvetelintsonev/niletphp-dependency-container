<?php

namespace Nilet\Components\Container;

use PHPUnit\Framework\TestCase;

class DependencyContainerTest extends TestCase {

    /**
     * @var \Nilet\Components\Container\DependencyContainer
     */
    protected $container;

    protected function setUp() {
        $this->container = new DependencyContainer;
    }

    protected function tearDown() {
        
    }

    public function testCreate() {
        $stubOne = $this->container->create(StubOne::class);
        $this->assertInstanceOf(StubOne::class, $stubOne);
        
        $stubTwo = $stubOne->getStubTwo();
        $this->assertInstanceOf(StubTwo::class, $stubTwo);

        $stubThree = $stubTwo->getStubThree();
        $this->assertInstanceOf(StubThree::class, $stubThree);
    }
    
    public function testCreateReturnsAllwaysNewInstance() {
        $this->assertFalse($this->container->create(StubOne::class) === $this->container->create(StubOne::class));
    }
    
    public function testCreateBinding() {
        $this->container->bind("Nilet\Components\Container\IStubOne", StubOne::class);
        $stubOne = $this->container->create("Nilet\Components\Container\IStubOne");
        $this->assertInstanceOf(StubOne::class, $stubOne);
        
        $stubTwo = $stubOne->getStubTwo();
        $this->assertInstanceOf(StubTwo::class, $stubTwo);

        $stubThree = $stubTwo->getStubThree();
        $this->assertInstanceOf(StubThree::class, $stubThree);
    }
    
    public function testCreateBindingReturnsAlwaysNewInstance() {
        $this->container->bind("Nilet\Components\Container\IStubOne", StubOne::class);
        $this->assertEquals(false, $this->container->create("Nilet\Components\Container\IStubOne") === $this->container->create("Nilet\Components\Container\IStubOne"));
    }
    
    public function testNestedBindings() {
        $this->container->bind("Nilet\Components\Container\INestedBindingStubOne", NestedBindingStubOne::class);
        $this->container->bind("Nilet\Components\Container\INestedBindingStubTwo", NestedBindingStubTwo::class);
        $this->container->bind("Nilet\Components\Container\INestedBindingStubThree", NestedBindingStubThree::class);
        
        $stubOne = $this->container->create("Nilet\Components\Container\INestedBindingStubOne");
        $this->assertInstanceOf("Nilet\Components\Container\INestedBindingStubOne", $stubOne);
        
        $stubTwo = $stubOne->getStubTwo();
        $this->assertInstanceOf("Nilet\Components\Container\INestedBindingStubTwo", $stubTwo);

        $stubThree = $stubTwo->getStubThree();
        $this->assertInstanceOf("Nilet\Components\Container\INestedBindingStubThree", $stubThree);
    }
    
    /**
     * @expectedException \Nilet\Components\Container\InstanceOfException
     */
    public function testInstanceThrowsInstanceOfException() {
        $this->container->instance(StubOne::class, new StubThree());
    }
    
    public function testGetShared() {
        $this->container->share(StubOne::class);
        
        $stubOne = $this->container->get(StubOne::class);
        $this->assertInstanceOf(StubOne::class, $stubOne);

        $stubTwo = $stubOne->getStubTwo();
        $this->assertInstanceOf(StubTwo::class, $stubTwo);

        $stubThree = $stubTwo->getStubThree();
        $this->assertInstanceOf(StubThree::class, $stubThree);
    }

    public function testGetSharedReturnsAlwaysTheSameInsrance() {
        $this->container->share(StubOne::class);
        $this->assertTrue($this->container->get(StubOne::class) === $this->container->get(StubOne::class));
    }
    
    public function testGetSharedBinding() {
        $this->container->bindShared("Nilet\Components\Container\IStubOne", StubOne::class);
        
        $stubOne = $this->container->get("Nilet\Components\Container\IStubOne");
        $this->assertInstanceOf(StubOne::class, $stubOne);
    }
    
    public function testGetSharedBindingReturnsAlwaysTheSameInsrance() {
        $this->container->bindShared("Nilet\Components\Container\IStubOne", StubOne::class);
        $this->assertTrue($this->container->get("Nilet\Components\Container\IStubOne") === $this->container->get("Nilet\Components\Container\IStubOne"));
    }
    
    public function testGetInstanceReturnsAlwaysTheSameInstance() {
        $stubOne = new StubOne(new StubTwo(new StubThree()));
        $this->container->instance(StubOne::class, $stubOne);
        $this->assertTrue($this->container->get(StubOne::class) === $this->container->get(StubOne::class));
    }
    
    /**
     * @expectedException \Nilet\Components\Container\MissingResolvedDependencyException
     */
    public function testGetThrowsMissingResolvedDependencyException() {
        $this->container->get(StubOne::class);
    }
    
    // Closures tests
    public function testBindClosure() {
        $this->container->bind("Nilet\Components\Container\IStubOne", function () {
            return new StubOne(new StubTwo(new StubThree()));
        });
        $this->assertInstanceOf(StubOne::class, $this->container->create("Nilet\Components\Container\IStubOne"));
    }
    
    public function testShareClosure() {
        $this->container->share("Nilet\Components\Container\StubOne", function () {
            return new StubOne(new StubTwo(new StubThree()));
        });
        $this->assertTrue($this->container->get("Nilet\Components\Container\StubOne") === $this->container->get("Nilet\Components\Container\StubOne"));
    }
    
    public function testBindSharedClosure() {
        $this->container->bindShared("Nilet\Components\Container\IStubOne", function () {
            return new StubOne(new StubTwo(new StubThree()));
        });
        $stubOne = $this->container->get("Nilet\Components\Container\IStubOne");
        $this->assertTrue($stubOne === $this->container->get("Nilet\Components\Container\IStubOne"));
    }
    
    public function testInstanceClosure() {
        $this->container->instance("Nilet\Components\Container\StubOne", function ($container) {
            return $container->create(StubOne::class);
        });
        $stubOne = $this->container->get("Nilet\Components\Container\StubOne");
        $this->assertTrue($this->container->get("Nilet\Components\Container\StubOne") === $this->container->get("Nilet\Components\Container\StubOne"));
    }

    public function testIsResolved() {
        $this->container->share(StubOne::class);
        $this->container->get(StubOne::class);
        $this->container->create(StubTwo::class);

        $this->assertTrue($this->container->isResolved(StubOne::class));
        $this->assertFalse($this->container->isResolved(StubTwo::class));
    }

    public function testIsShared() {
        $this->container->share(StubOne::class);
        $this->assertTrue($this->container->isShared(StubOne::class));

        $this->container->create(StubTwo::class);
        $this->assertFalse($this->container->isShared(StubTwo::class));
    }

    public function testIsBound() {
        $this->container->bind("Nilet\Components\Container\IStubOne", StubOne::class);
        $this->assertTrue($this->container->isBound("Nilet\Components\Container\IStubOne"));
        $this->assertFalse($this->container->isBound("Nilet\Components\Container\IINestedBindingStubOne"));
    }

    public function testIsSharedBound() {
        $this->container->bindShared("Nilet\Components\Container\IStubOne", StubOne::class);
        $this->assertTrue($this->container->isBoundShared("Nilet\Components\Container\IStubOne"));
        $this->assertFalse($this->container->isBoundShared("Nilet\Components\Container\IINestedBindingStubOne"));
    }
}

interface IStubOne {
    
    public function getStubTwo() : StubTwo;
    
}

class StubOne implements IStubOne {
    
    private $stubTwo;
    
    public function __construct(StubTwo $stubTwo) {
        $this->stubTwo = $stubTwo;
    }

    public function getStubTwo(): StubTwo {
        return $this->stubTwo;
    }
}

class StubTwo {
    
    private $thirdStub;
    
    public function __construct(StubThree $thirdStub) {
        $this->thirdStub = $thirdStub;
    }

    public function getStubThree() : StubThree {
        return $this->thirdStub;
    }

}

class StubThree {}

// Nested bind dependencies
interface INestedBindingStubOne {
    
    public function getStubTwo() : INestedBindingStubTwo;
    
}

class NestedBindingStubOne implements INestedBindingStubOne {
    
    private $stubTwo;
    
    public function __construct(INestedBindingStubTwo $stubTwo) {
        $this->stubTwo = $stubTwo;
    }

    public function getStubTwo(): INestedBindingStubTwo {
        return $this->stubTwo;
    }
}

interface INestedBindingStubTwo {
    
    public function getStubThree() : INestedBindingStubThree;
    
}

class NestedBindingStubTwo implements INestedBindingStubTwo {
    
    private $stubThree;
    
    public function __construct(INestedBindingStubThree $stubThree) {
        $this->stubThree = $stubThree;
    }

    public function getStubThree(): INestedBindingStubThree {
        return $this->stubThree;
    }
}

interface INestedBindingStubThree {}

class NestedBindingStubThree implements INestedBindingStubThree {}
