<?php declare(strict_types=1);

namespace Orion\Session;

use SessionHandlerInterface;

use Psr\Container\ContainerInterface;
use Psr\Cache\CacheItemPoolInterface;

use Interop\Container\ServiceProviderInterface;

use Cache\SessionHandler\Psr6SessionHandler;

use Ellipse\Session\SetSessionHandlerMiddleware;
use Ellipse\Session\StartSessionMiddleware;
use Ellipse\Session\ValidateSessionMiddleware;

class SessionServiceProvider implements ServiceProviderInterface
{
    public function getFactories()
    {
        return [
            SetSessionHandlerMiddleware::class => [$this, 'getSetSessionHandlerMiddleware'],
            StartSessionMiddleware::class => [$this, 'getStartSessionMiddleware'],
            ValidateSessionMiddleware::class => [$this, 'getValidateSessionMiddleware'],
        ];
    }

    public function getExtensions()
    {
        return [
            'app.http.session.id.prefix' => [$this, 'getSessionIdPrefix'],
            'app.http.session.ttl' => [$this, 'getSessionTtl'],
            'app.http.session.cookie' => [$this, 'getSessionCookie'],
            'app.http.session.cache' => [$this, 'getSessionCache'],
            'app.http.session.handler' => [$this, 'getSessionHandler'],
            'app.http.session.ownership.attributes' => [$this, 'getSessionOwnershipAttributes'],
            'app.http.session.ownership.signature' => [$this, 'getSessionOwnershipSignature'],
        ];
    }

    /**
     * Return a set session handler middleware.
     *
     * @param \Psr\Container\ContainerInterface $container
     * @return \Ellipse\Session\SetSessionHandlerMiddleware
     */
    public function getSetSessionHandlerMiddleware(ContainerInterface $container): SetSessionHandlerMiddleware
    {
        $handler = $container->get('app.http.session.handler');

        return new SetSessionHandlerMiddleware($handler);
    }

    /**
     * Return a start session middleware.
     *
     * @param \Psr\Container\ContainerInterface $container
     * @return \Ellipse\Session\StartSessionMiddleware
     */
    public function getStartSessionMiddleware(ContainerInterface $container): StartSessionMiddleware
    {
        $cookie = $container->get('app.http.session.cookie');

        return new StartSessionMiddleware($cookie);
    }

    /**
     * Return a validate session middleware.
     *
     * @param \Psr\Container\ContainerInterface $container
     * @return \Ellipse\Session\ValidateSessionMiddleware
     */
    public function getValidateSessionMiddleware(ContainerInterface $container): ValidateSessionMiddleware
    {
        $signature = $container->get('app.http.session.ownership.signature');

        return new ValidateSessionMiddleware($signature);
    }

    /**
     * Return 'ellipse_' as default session id prefix when none is defined.
     *
     * @param \Psr\Container\ContainerInterface $container
     * @param string                            $prefix
     * @return string
     */
    public function getSessionIdPrefix(ContainerInterface $container, string $prefix = 'ellipse_'): string
    {
        return $prefix;
    }

    /**
     * Return 7200 as default session time to live when none is defined.
     *
     * @param \Psr\Container\ContainerInterface $container
     * @param int                               $ttl
     * @return int
     */
    public function getSessionTtl(ContainerInterface $container, int $ttl = 7200): int
    {
        return $ttl;
    }

    /**
     * Return an empty array as default session cookie parameters when none
     * defined.
     *
     * @param \Psr\Container\ContainerInterface $container
     * @param array                             $cookie
     * @return array
     */
    public function getSessionCookie(ContainerInterface $container, array $cookie = []): array
    {
        return $cookie;
    }

    /**
     * Return a default cache item pool emulating default php behavior when none
     * defined.
     *
     * @param \Psr\Container\ContainerInterface $container
     * @param \Psr\Cache\CacheItemPoolInterface $cache
     * @return \Psr\Cache\CacheItemPoolInterface
     */
    public function getSessionCache(ContainerInterface $container, CacheItemPoolInterface $cache = null): CacheItemPoolInterface
    {
        if (is_null($cache)) {

            return new DefaultSessionCacheItemPool;

        }

        return $cache;
    }

    /**
     * Return a session handler based on the session cache item pool when none
     * defined.
     *
     * @param \Psr\Container\ContainerInterface $container
     * @param \SessionHandlerInterface          $handler
     * @return \SessionHandlerInterface
     */
    public function getSessionHandler(ContainerInterface $container, SessionHandlerInterface $handler = null): SessionHandlerInterface
    {
        if (is_null($handler)) {

            $prefix = $container->get('app.http.session.id.prefix');
            $ttl = $container->get('app.http.session.ttl');
            $cache = $container->get('app.http.session.cache');

            return new Psr6SessionHandler($cache, ['prefix' => $prefix, 'ttl' => $ttl]);

        }

        return $handler;
    }

    /**
     * Return an empty array as session ownership attributes when none defined.
     *
     * @param \Psr\Container\ContainerInterface $container
     * @param array                             $attributes
     * @return array
     */
    public function getSessionOwnershipAttributes(ContainerInterface $container, array $attributes = []): array
    {
        return $attributes;
    }

    /**
     * Return a default session ownership signature based on the session
     * ownership attributes when none defined.
     *
     * @param \Psr\Container\ContainerInterface $container
     * @param callable                          $signature
     * @return callable
     */
    public function getSessionOwnershipSignature(ContainerInterface $container, callable $signature = null): callable
    {
        if (is_null($signature)) {

            $attributes = $container->get('app.http.session.ownership.attributes');

            return new DefaultSessionOwnershipSignature($attributes);

        }

        return $signature;
    }
}
