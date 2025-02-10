<?php

declare(strict_types=1);

namespace Application\Cache;

use Doctrine\Common\Cache\Cache;

class DoctrineArrayCache implements Cache
{
    private array $data = [];
    private array $stats = ['hits' => 0, 'misses' => 0, 'uptime' => 0, 'memory_usage' => 0, 'memory_available' => 0];

    public function fetch($id)
    {
        if ($this->contains($id)) {
            $this->stats['hits']++;
            return $this->data[$id];
        }
        $this->stats['misses']++;
        return false;
    }

    public function contains($id)
    {
        return isset($this->data[$id]);
    }

    public function save($id, $data, $lifeTime = 0)
    {
        $this->data[$id] = $data;
        return true;
    }

    public function delete($id)
    {
        unset($this->data[$id]);
        return true;
    }

    public function getStats()
    {
        return $this->stats;
    }

    public function flushAll()
    {
        $this->data = [];
        return true;
    }
} 