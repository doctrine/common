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

namespace Doctrine\Common\Cache;

/**
 * SQLite cache provider
 *
 * @license http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @link    www.doctrine-project.org
 * @since   2.4
 * @author Markus Bachmann <markus.bachmann@bachi.biz>
 */
class SQLiteCache extends CacheProvider
{
    /**
     * @var \SQLite3
     */
    private $db;

    /**
     * Constructor
     *
     * @param string $dsn
     */
    public function __construct($dsn)
    {
        if (':memory:' === $dsn) {
            $this->db = new \SQLite3($dsn);
        } else {
            $this->db = new \SQLite3($dsn, SQLITE3_OPEN_READWRITE | SQLITE3_OPEN_CREATE);
        }

        $this->initDb();
    }

    /**
     * {@inhertiDoc}
     */
    protected function doContains($id)
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM doctrine_cache WHERE cache_key = :id');
        $stmt->bindParam('id', $id);

        $data = $stmt->execute()->fetchArray(SQLITE3_NUM);
        return $data[0] > 0;
    }

    /**
     * {@inheritDoc}.
     */
    protected function doDelete($id)
    {
        $stmt = $this->db->prepare('DELETE FROM doctrine_cache WHERE cache_key = :id');
        $stmt->bindParam('id', $id);
        $stmt->execute();

        return true;
    }

    /**
     * {@inheritDoc}
     */
    protected function doFetch($id)
    {
        $stmt = $this->db->prepare('SELECT cache_data FROM doctrine_cache WHERE cache_key = :id AND (cache_expired_at = 0 OR cache_expired_at >= :date)');
        $stmt->bindParam('id', $id);
        $stmt->bindValue('date', time());

        $data = $stmt->execute()->fetchArray(SQLITE3_NUM);

        return unserialize($data[0]);
    }

    /**
     * {@inheritDoc}
     */
    protected function doFlush()
    {
        return $this->db->exec('DELETE FROM doctrine_cache');
    }

    /**
     * {@inheritDoc}
     */
    protected function doSave($id, $data, $lifeTime = false)
    {
        $data = serialize($data);

        $stmt = $this->db->prepare('INSERT INTO doctrine_cache (cache_key, cache_data, cache_expired_at) VALUES(:id, :data, :lifetime)');
        $stmt->bindParam('id', $id);
        $stmt->bindParam('data', $data);
        $stmt->bindParam('lifetime', $lifeTime);

        return $stmt->execute();
    }

    /**
     * {@inheritDoc}
     */
    protected function doGetStats()
    {
        return null;
    }

    private function initDb()
    {
        $this->db->exec(<<<SQL
CREATE TABLE IF NOT EXISTS doctrine_cache (
  id INTEGER NOT NULL,
  cache_key TEXT NOT NULL,
  cache_data BLOB NOT NULL,
  cache_expired_at INTEGER NOT NULL,
  PRIMARY KEY(id)
)
SQL
        );
    }

}
