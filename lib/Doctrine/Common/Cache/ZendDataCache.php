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

/**
 * Zend Data Cache cache driver.
 *
 * @license http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @link    www.doctrine-project.org
 * @since   2.0
 * @version $Revision$
 * @author  Ralph Schindler <ralph.schindler@zend.com>
 */
class ZendDataCache extends AbstractCache
{
    /**
     * @const The key to use for storing the index data
     */
    const CACHE_IDS_INDEX_KEY = 'ids_index';
    
    /**
     * @var $ids The local cache of the ids that we've pulled from the actual cache store
     */ 
    protected $ids = false;

    public function __construct()
    {
        $this->setNamespace('doctrine::'); // zend data cache format for namespaces ends in ::
    }
    
    /**
     * {@inheritdoc}
     */
    public function getIds()
    {
        // lazy load the ids from the index key, if there are none, prepare empty array
        // save() and delete() will populate the index
        if ($this->ids === false) {
            $values = zend_shm_cache_fetch($this->_getNamespacedId(self::CACHE_IDS_INDEX_KEY));
            $this->ids = ($values === false) ? array() : $values;
        }

        return $this->ids;
    }

    /**
     * {@inheritdoc}
     */
    protected function _doFetch($id)
    {
        return zend_shm_cache_fetch($id);
    }

    /**
     * {@inheritdoc}
     */
    protected function _doContains($id)
    {
        return (zend_shm_cache_fetch($id) !== FALSE);
    }

    /**
     * {@inheritdoc}
     */
    protected function _doSave($id, $data, $lifeTime = 0)
    {
        $return = zend_shm_cache_store($id, $data, $lifeTime);

        // do some key management
        $ids = $this->getIds(); // force the load from key holder if need be

        if ($return && !in_array($id, $this->ids)) {
            array_push($this->ids, $id);
            $this->_updateCacheIdsIndex();
        }

        return $return;
    }

    /**
     * {@inheritdoc}
     */
    protected function _doDelete($id)
    {
        $return = zend_shm_cache_delete($id);

        // do some key management
        $ids = $this->getIds(); // force the load from key holder if need be

        if ($return && ($position = in_array($id, $this->ids))) {
            unset($this->ids[$position]);
            $this->_updateCacheIdsIndex();
        }

        return $return;
    }
    
    protected function _updateCacheIdsIndex()
    {
        return zend_shm_cache_store($this->_getNamespacedId(self::CACHE_IDS_INDEX_KEY), $this->ids);
    }
    
}