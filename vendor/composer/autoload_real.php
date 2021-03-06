<?php

// autoload_real.php @generated by Composer

class ComposerAutoloaderInit59accfb3f7fbc4891870bbde060af9d0
{
    private static $loader;

    public static function loadClassLoader($class)
    {
        if ('Composer\Autoload\ClassLoader' === $class) {
            require __DIR__ . '/ClassLoader.php';
        }
    }

    /**
     * @return \Composer\Autoload\ClassLoader
     */
    public static function getLoader()
    {
        if (null !== self::$loader) {
            return self::$loader;
        }

        spl_autoload_register(array('ComposerAutoloaderInit59accfb3f7fbc4891870bbde060af9d0', 'loadClassLoader'), true, true);
        self::$loader = $loader = new \Composer\Autoload\ClassLoader(\dirname(__DIR__));
        spl_autoload_unregister(array('ComposerAutoloaderInit59accfb3f7fbc4891870bbde060af9d0', 'loadClassLoader'));

        require __DIR__ . '/autoload_static.php';
        \Composer\Autoload\ComposerStaticInit59accfb3f7fbc4891870bbde060af9d0::getInitializer($loader)();

        $loader->register(true);

        return $loader;
    }
}
