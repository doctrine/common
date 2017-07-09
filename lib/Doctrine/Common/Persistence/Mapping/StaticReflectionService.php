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

namespace Doctrine\Common\Persistence\Mapping;

/**
 * PHP Runtime Reflection Service.
 *
 * @author Benjamin Eberlei <kontakt@beberlei.de>
 */
class StaticReflectionService implements ReflectionService
{
    /**
     * {@inheritDoc}
     */
    public function getParentClasses(string $class): array
    {
        return [];
    }

    /**
     * {@inheritDoc}
     */
    public function getClassShortName(string $className): string
    {
        if (strpos($className, '\\') !== false) {
            $className = substr($className, strrpos($className, "\\")+1);
        }
        return $className;
    }

    /**
     * {@inheritDoc}
     */
    public function getClassNamespace(string $className): string
    {
        $namespace = '';
        if (strpos($className, '\\') !== false) {
            $namespace = strrev(substr( strrev($className), strpos(strrev($className), '\\')+1 ));
        }
        return $namespace;
    }

    /**
     * {@inheritDoc}
     */
    public function getClass(string $class): ?\ReflectionClass
    {
        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function getAccessibleProperty(string $class, string $property): ?\ReflectionProperty
    {
        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function hasPublicMethod($class, $method): bool
    {
        return true;
    }
}
