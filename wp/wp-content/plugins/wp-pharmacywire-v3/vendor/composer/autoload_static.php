<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit86a1ca733e987699dd9769d76a09f91a
{
    public static $files = array (
        'da253f61703e9c22a5a34f228526f05a' => __DIR__ . '/..' . '/wixel/gump/gump.class.php',
    );

    public static $prefixLengthsPsr4 = array (
        'G' => 
        array (
            'GUMP\\' => 5,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'GUMP\\' => 
        array (
            0 => __DIR__ . '/..' . '/wixel/gump/src',
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit86a1ca733e987699dd9769d76a09f91a::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit86a1ca733e987699dd9769d76a09f91a::$prefixDirsPsr4;

        }, null, ClassLoader::class);
    }
}