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
    /**
     * @var Package
     */
    protected static $package;
    /**
     * @var Composer
     */
    protected static $composer;
    /**
     * @var IOInterface
     */
    protected static $io;

    protected static $outputDirectory;
    protected const KEY = "installer-paths";
    protected const PACKAGE_NAME = "pxlctzn/hexi-flexi-grid";
    protected const INPUT_DIRECTORIES = ['css', 'scss'];


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
        if(self::$package->getName() === self::PACKAGE_NAME)
            return false;

        self::$package  = $event->getComposer()->getPackage();
        self::$composer = $event->getComposer();
        self::$io       = $event->getIO();

        self::$outputDirectory = getcwd().DIRECTORY_SEPARATOR.self::getAssetInstallPath();
        self::copyAssetIntoInstallPath();

        return false;
    }


    public static function copyAssetIntoInstallPath()
    {
        $root = self::$composer->getConfig()->get('vendor-dir').DIRECTORY_SEPARATOR.self::formatPath(self::PACKAGE_NAME);

        foreach( self::INPUT_DIRECTORIES as $dir) {
            $source = $root.DIRECTORY_SEPARATOR.$dir;

            self::$io->write(">> Copying file inside '".$source."' from '".self::PACKAGE_NAME."' package into '".self::$outputDirectory."'.");
            self::xcopy($source, self::$outputDirectory.DIRECTORY_SEPARATOR.$dir);
        }
    }

    public static function getAssetInstallPath()
    {
        $path = self::getAssetInstallPathFromPackage();

        if( null === $path ) {
            $path = self::getDefaultAssetInstallPath();
        }
        self::$io->write("> Output directory : ".$path);

        return $path;
    }

    public static function getAssetInstallPathFromPackage()
    {

        $extra = self::$package->getExtra();

        if( key_exists(self::KEY, $extra) ) {
            self::$io->write(">> '".self::KEY."' found in package.");
            $extraFlip = array_flip($extra[self::KEY]);
            if ( key_exists(self::PACKAGE_NAME, $extraFlip) ) {
                self::$io->write(">> Custom '".self::KEY."' for '".self::PACKAGE_NAME."' package has been found.");

                $path = $extraFlip[self::PACKAGE_NAME];
                return self::formatPath($path);
            }
            else
            {
                self::$io->write(">> No Custom '".self::KEY."' for '".self::PACKAGE_NAME."' package has been found.");
            }
        }
        else
        {
            self::$io->write(">> No '".self::KEY."' found in package.");
        }
        return null;
    }

    public static function getDefaultAssetInstallPath()
    {
        self::$io->write(">> Using default Asset path .");

        return "public".DIRECTORY_SEPARATOR.self::formatPath(self::PACKAGE_NAME);
    }

    public static function formatPath($path)
    {
        list($vendor, $packageName) = explode('/', $path);
        return $vendor.DIRECTORY_SEPARATOR.$packageName;

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
            self::$io->write(">> Creating symlink from file '".$source."' to '".$dest."'");

            return symlink(readlink($source), $dest);
        }

        // Simple copy for a file
        if (is_file($source)) {
            self::$io->write(">> Copying file from '".$source."' into '".$dest."'");

            return copy($source, $dest);
        }

        // Make destination directory
        if (!is_dir($dest)) {
            self::$io->write(">> Creating '".$dest."' directory.");

            mkdir($dest, $permissions, true);
            self::$io->write(">> Directory '".$dest."' created.");

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

