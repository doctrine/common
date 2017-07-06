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

namespace Doctrine\Common\Reflection;

use ReflectionException;
use ReflectionMethod;

class StaticReflectionMethod extends ReflectionMethod
{
    /**
     * The PSR-0 parser object.
     *
     * @var StaticReflectionParser
     */
    protected $staticReflectionParser;

    /**
     * The name of the method.
     *
     * @var string
     */
    protected $methodName;

    /**
     * @param StaticReflectionParser $staticReflectionParser
     * @param string                 $methodName
     */
    public function __construct(StaticReflectionParser $staticReflectionParser, string $methodName)
    {
        $this->staticReflectionParser = $staticReflectionParser;
        $this->methodName = $methodName;
    }

    /**
     * {@inheritDoc}
     */
    public function getName(): string
    {
        return $this->methodName;
    }

    /**
     * @return StaticReflectionParser
     */
    protected function getStaticReflectionParser(): StaticReflectionParser
    {
        return $this->staticReflectionParser->getStaticReflectionParserForDeclaringClass('method', $this->methodName);
    }

    /**
     * {@inheritDoc}
     */
    public function getDeclaringClass(): \ReflectionClass
    {
        return $this->getStaticReflectionParser()->getReflectionClass();
    }

    /**
     * {@inheritDoc}
     */
    public function getNamespaceName(): string
    {
        return $this->getStaticReflectionParser()->getNamespaceName();
    }

    /**
     * {@inheritDoc}
     */
    public function getDocComment(): string
    {
        return $this->getStaticReflectionParser()->getDocComment('method', $this->methodName);
    }

    /**
     * @return array
     */
    public function getUseStatements(): array
    {
        return $this->getStaticReflectionParser()->getUseStatements();
    }

    /**
     * {@inheritDoc}
     */
    public static function export($class, $name, $return = false): void
    {
        throw new ReflectionException('Method not implemented');
    }

    /**
     * {@inheritDoc}
     */
    public function getClosure($object): void
    {
        throw new ReflectionException('Method not implemented');
    }

    /**
     * {@inheritDoc}
     */
    public function getModifiers(): void
    {
        throw new ReflectionException('Method not implemented');
    }

    /**
     * {@inheritDoc}
     */
    public function getPrototype(): void
    {
        throw new ReflectionException('Method not implemented');
    }

    /**
     * {@inheritDoc}
     */
    public function invoke($object, $parameter = null): void
    {
        throw new ReflectionException('Method not implemented');
    }

    /**
     * {@inheritDoc}
     */
    public function invokeArgs($object, array $args): void
    {
        throw new ReflectionException('Method not implemented');
    }

    /**
     * {@inheritDoc}
     */
    public function isAbstract(): void
    {
        throw new ReflectionException('Method not implemented');
    }

    /**
     * {@inheritDoc}
     */
    public function isConstructor(): void
    {
        throw new ReflectionException('Method not implemented');
    }

    /**
     * {@inheritDoc}
     */
    public function isDestructor(): void
    {
        throw new ReflectionException('Method not implemented');
    }

    /**
     * {@inheritDoc}
     */
    public function isFinal(): void
    {
        throw new ReflectionException('Method not implemented');
    }

    /**
     * {@inheritDoc}
     */
    public function isPrivate(): void
    {
        throw new ReflectionException('Method not implemented');
    }

    /**
     * {@inheritDoc}
     */
    public function isProtected(): void
    {
        throw new ReflectionException('Method not implemented');
    }

    /**
     * {@inheritDoc}
     */
    public function isPublic(): void
    {
        throw new ReflectionException('Method not implemented');
    }

    /**
     * {@inheritDoc}
     */
    public function isStatic(): void
    {
        throw new ReflectionException('Method not implemented');
    }

    /**
     * {@inheritDoc}
     */
    public function setAccessible($accessible): void
    {
        throw new ReflectionException('Method not implemented');
    }

    /**
     * {@inheritDoc}
     */
    public function __toString(): void
    {
        throw new ReflectionException('Method not implemented');
    }

    /**
     * {@inheritDoc}
     */
    public function getClosureThis(): void
    {
        throw new ReflectionException('Method not implemented');
    }

    /**
     * {@inheritDoc}
     */
    public function getEndLine(): void
    {
        throw new ReflectionException('Method not implemented');
    }

    /**
     * {@inheritDoc}
     */
    public function getExtension(): void
    {
        throw new ReflectionException('Method not implemented');
    }

    /**
     * {@inheritDoc}
     */
    public function getExtensionName(): void
    {
        throw new ReflectionException('Method not implemented');
    }

    /**
     * {@inheritDoc}
     */
    public function getFileName(): void
    {
        throw new ReflectionException('Method not implemented');
    }

    /**
     * {@inheritDoc}
     */
    public function getNumberOfParameters(): void
    {
        throw new ReflectionException('Method not implemented');
    }

    /**
     * {@inheritDoc}
     */
    public function getNumberOfRequiredParameters(): void
    {
        throw new ReflectionException('Method not implemented');
    }

    /**
     * {@inheritDoc}
     */
    public function getParameters(): void
    {
        throw new ReflectionException('Method not implemented');
    }

    /**
     * {@inheritDoc}
     */
    public function getShortName(): void
    {
        throw new ReflectionException('Method not implemented');
    }

    /**
     * {@inheritDoc}
     */
    public function getStartLine(): void
    {
        throw new ReflectionException('Method not implemented');
    }

    /**
     * {@inheritDoc}
     */
    public function getStaticVariables(): void
    {
        throw new ReflectionException('Method not implemented');
    }

    /**
     * {@inheritDoc}
     */
    public function inNamespace(): void
    {
        throw new ReflectionException('Method not implemented');
    }

    /**
     * {@inheritDoc}
     */
    public function isClosure(): void
    {
        throw new ReflectionException('Method not implemented');
    }

    /**
     * {@inheritDoc}
     */
    public function isDeprecated(): void
    {
        throw new ReflectionException('Method not implemented');
    }

    /**
     * {@inheritDoc}
     */
    public function isInternal(): void
    {
        throw new ReflectionException('Method not implemented');
    }

    /**
     * {@inheritDoc}
     */
    public function isUserDefined(): void
    {
        throw new ReflectionException('Method not implemented');
    }

    /**
     * {@inheritDoc}
     */
    public function returnsReference(): void
    {
        throw new ReflectionException('Method not implemented');
    }
}
