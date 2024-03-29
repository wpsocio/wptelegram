<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit829c22532970541126c2f5ab2f5e7dbf
{
    public static $files = array (
        '0d252e6134999215031cdb0e94a79cd5' => __DIR__ . '/..' . '/wpsocio/wptelegram-bot-api/init.php',
    );

    public static $prefixLengthsPsr4 = array (
        'W' => 
        array (
            'WPTelegram\\BotAPI\\' => 18,
            'WPSocio\\WPUtils\\' => 16,
            'WPSocio\\TelegramFormatText\\' => 27,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'WPTelegram\\BotAPI\\' => 
        array (
            0 => __DIR__ . '/..' . '/wpsocio/wptelegram-bot-api/src',
        ),
        'WPSocio\\WPUtils\\' => 
        array (
            0 => __DIR__ . '/..' . '/wpsocio/wp-utils/src',
        ),
        'WPSocio\\TelegramFormatText\\' => 
        array (
            0 => __DIR__ . '/..' . '/wpsocio/telegram-format-text/src',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit829c22532970541126c2f5ab2f5e7dbf::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit829c22532970541126c2f5ab2f5e7dbf::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit829c22532970541126c2f5ab2f5e7dbf::$classMap;

        }, null, ClassLoader::class);
    }
}
