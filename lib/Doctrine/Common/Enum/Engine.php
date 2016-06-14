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
 * Engine
 */
interface Engine
{
    /**
     * Checks if the Enum class is supported by the engine.
     *
     * @param string $enumName
     *
     * @return bool
     */
    public function supports($enumName);

    /**
     * Returns a list of accepted values for the enum.
     *
     * @param string $enumName
     * @return array
     *
     * @throws \InvalidArgumentException if a unsupported enum is passed.
     */
    public function getValues($enumName);

    /**
     * Returns the current value of the enum.
     *
     * @param mixed $enum
     * @return array
     *
     * @throws \InvalidArgumentException if a unsupported enum is passed.
     */
    public function getValue($enum);

    /**
     * Creates a enum instance by specifying the enum name and the value.
     *
     * @param string $enumName
     * @param mixed  $key
     *
     * @return mixed
     *
     * @throws \InvalidArgumentException if a unsupported enumName is passed.
     * @throws \UnexpectedValueException if key is not valid for the enum.
     */
    public function createByKey($enumName, $key);

    /**
     * Creates a enum instance by specifying the enum name and the value.
     *
     * @param string $enumName
     * @param mixed  $value
     *
     * @return mixed
     *
     * @throws \InvalidArgumentException if a unsupported enumName is passed.
     * @throws \UnexpectedValueException if value is not valid for the enum.
     */
    public function createByValue($enumName, $value);
}