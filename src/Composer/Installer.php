<?php
namespace PxlCtzn\HexiFlexiGrid\Scripts;

use Composer\Plugin\PluginInterface;
use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\Script\ScriptEvents;

final class Installer implements PluginInterface, EventSubscriberInterface
{
    /**
     * {@inheritDoc}
     */

    public function activate(Composer $composer, IOInterface $io)
    {
        // Nothing to do here, as all features are provided through event listener
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


    public static function copyAsset($event)
    {
        var_dump($event);
        die();
    }
}

