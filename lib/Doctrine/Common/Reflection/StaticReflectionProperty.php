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
use ReflectionProperty;

class StaticReflectionProperty extends ReflectionProperty
{
    /**
     * The PSR-0 parser object.
     *
     * @var StaticReflectionParser
     */
    protected $staticReflectionParser;

    /**
     * The name of the property.
     *
     * @var string|null
     */
    protected $propertyName;

    /**
     * @param StaticReflectionParser $staticReflectionParser
     * @param string|null            $propertyName
     */
    public function __construct(StaticReflectionParser $staticReflectionParser, string $propertyName)
    {
        $this->staticReflectionParser = $staticReflectionParser;
        $this->propertyName = $propertyName;
    }

    /**
     * {@inheritDoc}
     */
    public function getName(): string
    {
        return $this->propertyName;
    }

    /**
     * @return StaticReflectionParser
     */
    protected function getStaticReflectionParser(): StaticReflectionParser
    {
        return $this->staticReflectionParser->getStaticReflectionParserForDeclaringClass('property', $this->propertyName);
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
    public function getDocComment(): string
    {
        return $this->getStaticReflectionParser()->getDocComment('property', $this->propertyName);
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
    public function getModifiers(): void
    {
        throw new ReflectionException('Method not implemented');
    }

    /**
     * {@inheritDoc}
     */
    public function getValue($object = null): void
    {
        throw new ReflectionException('Method not implemented');
    }

    /**
     * {@inheritDoc}
     */
    public function isDefault(): void
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
    public function setValue($object, $value = null): void
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
}
