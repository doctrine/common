<?php
/*
 *  $Id$
 *
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

namespace Doctrine\Common\Cache;

use \Memcached;

/**
 * A Memcache driver that uses libmemcached
 *
 * @license http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @link    www.doctrine-project.org
 * @since   2.1
 * @version $Id$
 * @author Christian Soronellas <christian@sistemes-cayman.es>
 */
class MemcachedCache extends AbstractCache
{
    /**
     * The memcached internal instance
     * 
     * @var Memcached
     */
    private $_memcached;
    
    /**
     * Set the memcached instance
     * 
     * @param Memcached $memcached
     */
    public function setMemcached(Memcached $memcached)
    {
        $this->_memcached = $memcached;
    }
    
    /**
     * Gets the memcached internal instance
     * 
     * @return Memcached
     */
    public function getMemcached()
    {
        return $this->_memcached;
    }
    
    /**
     * {@inheritdoc}
     */
    protected function _doFetch($id)
    {
        return $this->_memcached->get($id);
    }
    
    /**
     * {@inheritdoc}
     */
    protected function _doContains($id)
    {
        return (false !== $this->_memcached->get($id));
    }
    
    /**
     * {@inheritdoc}
     */
    protected function _doSave($id, $data, $lifeTime = false)
    {
        if ('_keys' == $id) {
            return false;
        }
        
        $result = $this->_memcached->set($id, $data, (int) $lifeTime);
        
        if (true === $result) {
            if ($this->_doContains('_keys')) {
                $this->_memcached->append('_keys', "#{$id}");
            } else {
                $this->_memcached->set('_keys', $id);
            }
            
            return true;
        }
        
        return false;
    }
    
    /**
     * {@inheritdoc}
     */
    protected function _doDelete($id)
    {
        if ('_keys' == $id) {
            return false;
        }
        
        $result = $this->_memcached->delete($id);
        
        if (true === $result) {
            $this->_memcached->set('_keys', str_replace("#{$id}", '', $this->_memcached->get('_keys')));
            return true;
        }
        
        return false;
    }
    
    /**
     * {@inheritdoc}
     */
    public function getIds()
    {
        $keys = $this->_memcached->get('_keys');
        
        if (strlen($keys) > 0) {
            return array_walk(explode('#', $keys), function(&$element, $index) {
                $element = trim($element);
            });
        }
        
        return array();
    }
}