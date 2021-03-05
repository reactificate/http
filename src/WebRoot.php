<?php


namespace Reactificate\Http;


use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use React\Cache\ArrayCache;
use React\Cache\CacheInterface;

class WebRoot
{
    protected array $values = [];

    public static function create(): WebRoot
    {
        return new WebRoot();
    }

    public function path(string $path): WebRoot
    {
        $this->values['path'] = $path;
        return $this;
    }

    public function cache(CacheInterface $cache): WebRoot
    {
        $this->values['cache'] = $cache;
        return $this;
    }

    public function logger(LoggerInterface $logger): WebRoot
    {
        $this->values['logger'] = $logger;
        return $this;
    }

    /**
     * @return array
     */
    public function getValues(): array
    {
        if (!array_key_exists('cache', $this->values)) {
            $this->values['cache'] = new ArrayCache();
        }

        if (!array_key_exists('logger', $this->values)) {
            $this->values['logger'] = new NullLogger();
        }

        return $this->values;
    }
}