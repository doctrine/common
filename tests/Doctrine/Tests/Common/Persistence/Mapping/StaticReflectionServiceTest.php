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

use Doctrine\Common\Persistence\Mapping\StaticReflectionService;

/**
 * @group DCOM-93
 */
class StaticReflectionServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var StaticReflectionService
     */
    private $reflectionService;

    public function setUp(): void
    {
        $this->reflectionService = new StaticReflectionService();
    }

    public function testShortname(): void
    {
        $this->assertEquals("StaticReflectionServiceTest", $this->reflectionService->getClassShortName(__CLASS__));
    }

    public function testClassNamespaceName(): void
    {
        $this->assertEquals('', $this->reflectionService->getClassNamespace(\stdClass::class));
        $this->assertEquals(__NAMESPACE__, $this->reflectionService->getClassNamespace(__CLASS__));
    }

    public function testGetParentClasses(): void
    {
        $classes = $this->reflectionService->getParentClasses(__CLASS__);
        $this->assertTrue(count($classes) == 0, "The test class ".__CLASS__." should have no parents according to static reflection.");
    }

    public function testGetReflectionClass(): void
    {
        $class = $this->reflectionService->getClass(__CLASS__);
        $this->assertNull($class);
    }

    public function testGetMethods(): void
    {
        $this->assertTrue($this->reflectionService->hasPublicMethod(__CLASS__, "testGetMethods"));
        $this->assertTrue($this->reflectionService->hasPublicMethod(__CLASS__, "testGetMethods2"));
    }

    public function testGetAccessibleProperty(): void
    {
        $reflProp = $this->reflectionService->getAccessibleProperty(__CLASS__, "reflectionService");
        $this->assertNull($reflProp);
    }
}

