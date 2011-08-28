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

use Doctrine\Common\Comparable;

/**
 * Immutable DateTime class.
 *
 * Instead of modifying the original instance the methods #add(), #sub() and
 * #modify() return new instances instead.
 *
 * @license http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @link    www.doctrine-project.org
 * @since   3.0
 * @author  Benjamin Eberlei <kontakt@beberlei.de>
 */
class DateTime extends \DateTime implements Comparable
{
    /**
     * Add interval to date by returning a new DateTime instance.
     *
     * The original DateTime instance is not modified.
     *
     * @param  string $interval
     * @return DateTime
     */
    public function add($interval)
    {
        $newDate = clone $this;
        return \date_add($newDate, $interval);
    }

    /**
     * Modify DateTime by returning a new DateTime instance.
     *
     * The original DateTime instance is not modified.
     *
     * @param  string $modify
     * @return DateTime
     */
    public function modify($modify)
    {
        $newDate = clone $this;
        return \date_modify($newDate, $modify);
    }

    /**
     * Substract interval from the date by returning a new DateTime instance.
     *
     * The original DateTime instance is not modified.
     *
     * @param  string $interval
     * @return DateTime
     */
    public function sub($interval)
    {
        $newDate = clone $this;
        return \date_sub($newDate, $interval);
    }

    /**
     * Always throws ImmutableException
     *
     * @throws ImmutableException
     * @param type $year
     * @param type $month
     * @param type $day
     */
    public function setDate($year, $month, $day)
    {
        throw new ImmutableException();
    }

    /**
     * Always throws ImmutableException
     *
     * @throws ImmutableException
     * @param type $year
     * @param type $week
     * @param type $day
     */
    public function setISODate($year, $week, $day=null)
    {
        throw new ImmutableException();
    }

    /**
     * Always throws ImmutableException
     *
     * @throws ImmutableException
     * @param type $hour
     * @param type $minute
     * @param type $second
     */
    public function setTime($hour, $minute, $second=null)
    {
        throw new ImmutableException();
    }

    /**
     * Always throws ImmutableException
     *
     * @throws ImmutableException
     * @param type $timestamp
     */
    public function setTimestamp($timestamp)
    {
        throw new ImmutableException();
    }

    /**
     * Returns a new instance of this datetime with the changed timezone.
     * 
     * @param DateTimeZone|string $timezone
     * @return DateTime
     */
    public function setTimezone($timezone)
    {
        $newDate = clone $this;
        return date_timezone_set($newDate, $timezone);
    }

    /**
     * Compare two dates which each other for equality.
     * 
     * @param DateTime $other
     * @return bool
     */
    public function equals($other)
    {
        if (!($other instanceof \DateTime)) {
            return false;
        }

        return $this->format('c e') === $other->format('c e');
    }

    /**
     * PHPs internal DateTime is missing a __toString(), which prevents it from
     * being usable as primary keys in the ORMs for example.
     *
     * @return string
     */
    public function __toString()
    {
        // This is the most precise and yet short format for a date.
        return $this->format('Y-m-d H:i:s.u e');
    }
}
