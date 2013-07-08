<?php

namespace SxGeo;

use Composer\Script\Event;

/**
 * Class Composer
 *
 * Example
 * <code>
 *   "scripts": {
 *     "post-install-cmd": [
 *     "SxGeo\\Composer::installDatabases",
 *   },
 *   "extra": {
 *     "sxgeo-databases": [
 *        "SxGeo",
 *        "SxGeoCity"
 *     ]
 *   }
 * </code>
 */
class Composer
{
    public static function installDatabases(Event $event)
    {
        $extra = $event->getComposer()->getPackage()->getExtra();

        if (empty($extra['sxgeo-databases'])) {
            $event->getIO()->write('<warning>No databases to install</warning>');
        }

        $databases = array(
            'SxGeo' => 'https://github.com/Koc/Sypexgeo-databases/releases/2013.04.22/1925/SxGeo.dat',
            'SxGeoCity' => 'https://github.com/Koc/Sypexgeo-databases/releases/2013.04.22/1929/SxGeoCity.dat',
            'SxGeo_GL' => 'https://github.com/Koc/Sypexgeo-databases/releases/2013.04.22/1926/SxGeo_GL.dat',
            'SxGeo_GLCity' => 'https://github.com/Koc/Sypexgeo-databases/releases/2013.04.22/1927/SxGeo_GLCity.dat',
            'SxGeo_IGB' => 'https://github.com/Koc/Sypexgeo-databases/releases/2013.04.22/1928/SxGeo_IGB.dat',
        );

        $targetDir = dirname(__DIR__);

        foreach ($extra['sxgeo-databases'] as $database) {
            if (!isset($databases[$database])) {
                $event->getIO()->write(sprintf('<error>Unknown database "%s"</error>', $database));
            }


            $basename = basename($databases[$database]);
            $tmpFile = $targetDir . '/' . $basename . '.tmp';
            copy($databases[$database], $tmpFile);
            rename($tmpFile, $targetDir . '/' . $basename);
            $event->getIO()->write(sprintf('Installed "%s" database', $database));
        }
    }
}
