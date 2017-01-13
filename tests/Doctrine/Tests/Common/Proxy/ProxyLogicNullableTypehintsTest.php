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

namespace Doctrine\Tests\Common\Proxy;

use Doctrine\Common\Proxy\ProxyGenerator;
use Doctrine\Common\Proxy\Proxy;
use Doctrine\Common\Proxy\Exception\UnexpectedValueException;
use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use PHPUnit_Framework_TestCase;
use stdClass;

/**
 * Test that identifier getter does not cause lazy loading. These tests make assumptions about the structure of LazyLoadableObjectWithTypehints
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @author Jan Langer <jan.langer@slevomat.cz>
 */
class ProxyLogicNullableTypehintsTest extends ProxyLogicTypehintsTest
{
    /**
     * {@inheritDoc}
     */
    public function setUp()
    {
        if (PHP_VERSION_ID < 70100) {
            $this->markTestSkipped('Nullable Return type hints are only supported in PHP >= 7.1.0.');
        }

        $this->identifier = [
            'identifierFieldReturnClassOneLetterNullable' => new stdClass(),
            'identifierFieldReturnClassOneLetterNullableWithSpace' => new stdClass(),
        ];

        $this->proxyClassName = 'Doctrine\Tests\Common\ProxyProxy\__CG__\Doctrine\Tests\Common\Proxy\LazyLoadableObjectWithNullableTypehints';
        $this->lazyLoadableObjectMetadata = $this->metadata = new LazyLoadableObjectWithNullableTypehintsClassMetadata();

        $this->setUpProxy();
    }

    /**
     * @return array
     */
    public function dataNoLazyLoadingForIdentifier()
    {
        return [
            ['identifierFieldReturnClassOneLetterNullable'],
            ['identifierFieldReturnClassOneLetterNullableWithSpace'],
        ];
    }
}
