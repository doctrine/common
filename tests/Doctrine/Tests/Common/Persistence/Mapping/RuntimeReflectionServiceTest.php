<?php
/*
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the MIT license. For more information, see
 * <http://www.doctrine-project.org>.
 */

declare(strict_types=1);

namespace Doctrine\Tests\Common\Persistence\Mapping;

use Doctrine\Common\Persistence\Mapping\MappingException;
use Doctrine\Common\Persistence\Mapping\RuntimeReflectionService;
use Doctrine\Common\Reflection\RuntimePublicReflectionProperty;

/**
 * @group DCOM-93
 */
class RuntimeReflectionServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var RuntimeReflectionService
     */
    private $reflectionService;

    public $unusedPublicProperty;

    public function setUp(): void
    {
        $this->reflectionService = new RuntimeReflectionService();
    }

    public function testShortname(): void
    {
        $this->assertEquals("RuntimeReflectionServiceTest", $this->reflectionService->getClassShortName(__CLASS__));
    }

    public function testClassNamespaceName(): void
    {
        $this->assertEquals('Doctrine\Tests\Common\Persistence\Mapping', $this->reflectionService->getClassNamespace(__CLASS__));
    }

    public function testGetParentClasses(): void
    {
        $classes = $this->reflectionService->getParentClasses(__CLASS__);
        $this->assertTrue(count($classes) >= 1, "The test class ".__CLASS__." should have at least one parent.");
    }

    public function testGetParentClassesForAbsentClass(): void
    {
        $this->expectException(MappingException::class);
        $this->reflectionService->getParentClasses(__NAMESPACE__ . '\AbsentClass');
    }

    public function testGetReflectionClass(): void
    {
        $class = $this->reflectionService->getClass(__CLASS__);
        $this->assertInstanceOf("ReflectionClass", $class);
    }

    public function testGetMethods(): void
    {
        $this->assertTrue($this->reflectionService->hasPublicMethod(__CLASS__, "testGetMethods"));
        $this->assertFalse($this->reflectionService->hasPublicMethod(__CLASS__, "testGetMethods2"));
    }

    public function testGetAccessibleProperty(): void
    {
        $reflProp = $this->reflectionService->getAccessibleProperty(__CLASS__, "reflectionService");
        $this->assertInstanceOf(\ReflectionProperty::class, $reflProp);
        $this->assertInstanceOf(RuntimeReflectionService::class, $reflProp->getValue($this));

        $reflProp = $this->reflectionService->getAccessibleProperty(__CLASS__, "unusedPublicProperty");
        $this->assertInstanceOf(RuntimePublicReflectionProperty::class, $reflProp);
    }
}

