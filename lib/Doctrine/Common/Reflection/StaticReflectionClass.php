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

use ReflectionClass;
use ReflectionException;

class StaticReflectionClass extends ReflectionClass
{
    /**
     * The static reflection parser object.
     *
     * @var StaticReflectionParser
     */
    private $staticReflectionParser;

    /**
     * @param StaticReflectionParser $staticReflectionParser
     */
    public function __construct(StaticReflectionParser $staticReflectionParser)
    {
        $this->staticReflectionParser = $staticReflectionParser;
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return $this->staticReflectionParser->getClassName();
    }

    /**
     * {@inheritDoc}
     */
    public function getDocComment()
    {
        return $this->staticReflectionParser->getDocComment();
    }

    /**
     * {@inheritDoc}
     */
    public function getNamespaceName()
    {
        return $this->staticReflectionParser->getNamespaceName();
    }

    /**
     * @return array
     */
    public function getUseStatements()
    {
        return $this->staticReflectionParser->getUseStatements();
    }

    /**
     * {@inheritDoc}
     */
    public function getMethod($name)
    {
        return $this->staticReflectionParser->getReflectionMethod($name);
    }

    /**
     * {@inheritDoc}
     */
    public function getProperty($name)
    {
        return $this->staticReflectionParser->getReflectionProperty($name);
    }

    /**
     * Prevents calling unsupported methods.
     *
     * __call() is used so that PHP can change the signature of these methods without breaking this class. For example,
     * https://github.com/php/php-src/pull/2363.
     *
     * @param string $method
     * @param array  $args
     *
     * @return mixed
     *
     * @throws \ReflectionException
     */
    public function __call($name, $arguments)
    {
        throw new ReflectionException('Method not implemented');
    }

    /**
     * {@inheritDoc}
     */
    public function __toString()
    {
        throw new ReflectionException('Method not implemented');
    }
}
