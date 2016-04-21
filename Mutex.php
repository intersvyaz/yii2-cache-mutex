<?php

namespace Intersvyaz\CacheMutex;

use Yii;
use yii\caching\Cache;

class Mutex extends \yii\mutex\Mutex
{
    /**
     * @var string|Cache
     */
    public $cache = 'cache';

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        $this->cache = is_string($this->cache) ? Yii::$app->get($this->cache, false) : $this->cache;
    }

    /**
     * @inheritdoc
     */
    protected function acquireLock($name, $timeout = 0)
    {
        if (!$this->cache instanceof Cache) {
            return false;
        }

        $waitTime = 0;
        while ($this->cache->get($this->getCacheKey($name)) !== false) {
            $waitTime++;
            if ($waitTime > $timeout) {
                return false;
            }
            sleep(1);
        }

        return $this->cache->set($this->getCacheKey($name), true);
    }

    /**
     * @inheritdoc
     */
    protected function releaseLock($name)
    {
        if (!$this->cache instanceof Cache) {
            return false;
        }

        return $this->cache->delete($this->getCacheKey($name));
    }

    /**
     * @param string $name
     * @return string
     */
    private function getCacheKey($name)
    {
        return md5($name);
    }
}
