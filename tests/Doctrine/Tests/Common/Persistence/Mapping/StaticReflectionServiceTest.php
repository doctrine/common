<?php
namespace Doctrine\Tests\Common\Persistence\Mapping;

use Doctrine\Common\Persistence\Mapping\StaticReflectionService;

/**
 * @group DCOM-93
 */
class StaticReflectionServiceTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var StaticReflectionService
     */
    private $reflectionService;

    public function setUp()
    {
        $this->reflectionService = new StaticReflectionService();
    }

    public function testShortname()
    {
        self::assertEquals("StaticReflectionServiceTest", $this->reflectionService->getClassShortName(__CLASS__));
    }

    public function testClassNamespaceName()
    {
        self::assertEquals('', $this->reflectionService->getClassNamespace(\stdClass::class));
        self::assertEquals(__NAMESPACE__, $this->reflectionService->getClassNamespace(__CLASS__));
    }

    public function testGetParentClasses()
    {
        $classes = $this->reflectionService->getParentClasses(__CLASS__);
        self::assertTrue(count($classes) == 0, "The test class " . __CLASS__ . " should have no parents according to static reflection.");
    }

    public function testGetReflectionClass()
    {
        $class = $this->reflectionService->getClass(__CLASS__);
        self::assertNull($class);
    }

    public function testGetMethods()
    {
        self::assertTrue($this->reflectionService->hasPublicMethod(__CLASS__, "testGetMethods"));
        self::assertTrue($this->reflectionService->hasPublicMethod(__CLASS__, "testGetMethods2"));
    }

    public function testGetAccessibleProperty()
    {
        $reflProp = $this->reflectionService->getAccessibleProperty(__CLASS__, "reflectionService");
        self::assertNull($reflProp);
    }
}
