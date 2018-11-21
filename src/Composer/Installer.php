<?php
namespace PxlCtzn\HexiFlexiGrid\Composer;

use Composer\Plugin\PluginInterface;
use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\Script\ScriptEvents;
use Composer\Script\Event;
use Composer\Package\Package;

final class Installer implements PluginInterface, EventSubscriberInterface
{
    protected static $package;
    protected static $composer;
    protected static $io;
    protected static $outputDirectory;

    protected const KEY = "installer-paths";


    /**
     * {@inheritDoc}
     */

    public function activate(Composer $composer, IOInterface $io)
    {
    }

    /**
     * {@inheritDoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            ScriptEvents::POST_INSTALL_CMD => 'copyAsset',
            ScriptEvents::POST_UPDATE_CMD  => 'copyAsset',
        ];
    }


    /**
     * Copy asset into the right directory
     *
     * @param Event $event
     */
    public static function copyAsset(Event $event)
    {
        self::$package  = $event->getComposer()->getPackage();
        self::$composer = $event->getComposer();
        self::$io       = $event->getIO();

        self::$outputDirectory = ".".DIRECTORY_SEPARATOR.self::getAssetInstallPath();
        var_dump(realpath(getcwd()));
        die();
        self::copyAssetIntoInstallPath();

        die();
    }


    public static function copyAssetIntoInstallPath()
    {

    }

    public static function getAssetInstallPath()
    {
        $path = self::getAssetInstallPathFromPackage();

        if( null === $path )
            $path = self::getDefaultAssetPath();

        self::$io->write("> Output directory : ".$path);

        return $path;
    }

    public static function getAssetInstallPathFromPackage()
    {

        $extra = self::$package->getExtra();
        $key   = self::KEY;

        if( key_exists($key, $extra) ) {
            self::$io->write(">> 'installed-path' found in package.");
            $extraFlip = array_flip($extra[$key]);
            if ( key_exists(self::$package->getName(), $extraFlip) ) {
                self::$io->write(">> Custom 'installed-path' for '".self::$package->getName()."' package has been found.");

                return $extraFlip[self::$package->getName()];
            }
        }
        self::$io->write(">> No 'installed-path' found in package.");

        return null;
    }

    public static function getDefaultAssetInstallPath()
    {
        self::$io->write(">> Using default Asset path .");

        list($vendor, $packageName) = explode('/', self::$package->getName());

        if ( null !== $vendor && null !== $packageName )
        {
            $path = "public".DIRECTORY_SEPARATOR.$vendor.DIRECTORY_SEPARATOR.$packageName;
        }
        else
        {
            $path = "public".DIRECTORY_SEPARATOR.self::$package->getName();
        }

        return $path;
    }

    /**
     * Copy a file, or recursively copy a folder and its contents
     * @author      Aidan Lister <aidan@php.net>
     * @version     1.0.1
     * @link        http://aidanlister.com/2004/04/recursively-copying-directories-in-php/
     * @param       string   $source    Source path
     * @param       string   $dest      Destination path
     * @param       int      $permissions New folder creation permissions
     * @return      bool     Returns true on success, false on failure
     */
    public static function xcopy($source, $dest, $permissions = 0755)
    {
        // Check for symlinks
        if (is_link($source)) {
            return symlink(readlink($source), $dest);
        }

        // Simple copy for a file
        if (is_file($source)) {
            return copy($source, $dest);
        }

        // Make destination directory
        if (!is_dir($dest)) {
            mkdir($dest, $permissions, true);
        }

        // Loop through the folder
        $dir = dir($source);
        while (false !== $entry = $dir->read()) {
            // Skip pointers
            if ($entry == '.' || $entry == '..') {
                continue;
            }

            // Deep copy directories
            self::xcopy("$source/$entry", "$dest/$entry", $permissions);
        }

        // Clean up
        $dir->close();
        return true;
    }
}

