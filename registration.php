<?php
\Magento\Framework\Component\ComponentRegistrar::register(
    \Magento\Framework\Component\ComponentRegistrar::MODULE,
    'Monogo_Mobilpay',
    __DIR__
);

/**
 * @deprecated
 * Remove classmap autoload after converting library to Name-spaced Classes
 */
spl_autoload_register(function ($class) {
    static $map;
    if (!$map) {
        $map = include __DIR__ . '/classmap.php';
    }

    if (!isset($map[$class])) {
        return false;
    }
    return include $map[$class];
});
