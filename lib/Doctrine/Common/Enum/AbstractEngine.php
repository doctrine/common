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

namespace Doctrine\Common\Enum;

/**
 * AbstractEngine
 */
abstract class AbstractEngine implements Engine
{
    const BASE_CLASS_NAME = null;

    /**
     * {@inheritdoc}
     */
    public function supports($enumName)
    {
        return class_exists(static::BASE_CLASS_NAME) &&
            is_subclass_of($enumName, static::BASE_CLASS_NAME);
    }

    /**
     * {@inheritdoc}
     */
    public function getValue($enum)
    {
        if (!$this->supports($enum)) {
            throw new \InvalidArgumentException(sprintf(
                "A %s is expected in getValue().",
                static::BASE_CLASS_NAME
            ));
        }

        return (string) $enum;
    }

    /**
     * {@inheritdoc}
     */
    public function createByKey($enumName, $key)
    {
        if (!$this->supports($enumName)) {
            throw new \InvalidArgumentException(sprintf(
                "A %s is expected in createByKey().",
                static::BASE_CLASS_NAME
            ));
        }

        return new $enumName($enumName::$key);
    }

    /**
     * {@inheritdoc}
     */
    public function createByValue($enumName, $value)
    {
        if (!$this->supports($enumName)) {
            throw new \InvalidArgumentException(sprintf(
                "A %s is expected in createByValue().",
                static::BASE_CLASS_NAME
            ));
        }

        return new $enumName($value);
    }
}