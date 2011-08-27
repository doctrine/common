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
 * and is licensed under the LGPL. For more information, see
 * <http://www.doctrine-project.org>.
 */

namespace Doctrine\Common\DateTime;

/**
 * DateTimeFactory that makes use of the immutable property of Doctrines DateTime instances.
 *
 * @license http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @link    www.doctrine-project.org
 * @since   3.0
 * @author  Benjamin Eberlei <kontakt@beberlei.de>
 */
class DateTimeFactory
{
    /**
     * Map of all the dates
     *
     * @todo Evaluate for WeakRef?
     * @var array
     */
    static private $dates = array();

    /**
     * @var DateTime
     */
    static private $now;

    /**
     * @return DateTime
     */
    static public function now()
    {
        if (self::$now == null) {
            self::$now = new DateTime("now");
        }
        return self::$now;
    }

    /**
     * @param DateTime $now
     */
    static public function setTestingNow(DateTime $now)
    {
        self::$now = $now;
    }

    /**
     * Create from format using the default timezone.
     *
     * This method is not overwritten on DateTime, so that you can still create instances with a non-default
     * timezone. However only the DateTimeFactory#createFromFormat() can re-use instances.
     *
     * @param string $format
     * @param string $time
     * @return DateTime
     */
    static public function createFromFormat($format, $time)
    {
        if (!isset(self::$dates[$format][$time])) {
            self::$dates[$format][$time] = DateTime::createFromFormat($format, $time);
        }
        return self::$dates[$format][$time];
    }
}