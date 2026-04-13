<?php

namespace DodgyCode\BunnyStorage;

use DodgyCode\BunnyStorage\fs\BunnyFs;
use Craft;
use craft\events\RegisterComponentTypesEvent;
use craft\services\Fs;
use yii\base\Event;
use yii\log\FileTarget;

class Plugin extends \craft\base\Plugin
{
    public string $schemaVersion = '1.0.0';

    public function init(): void
    {
        parent::init();

        $this->registerLogTarget();

        Event::on(
            Fs::class,
            Fs::EVENT_REGISTER_FILESYSTEM_TYPES,
            function (RegisterComponentTypesEvent $event) {
                $event->types[] = BunnyFs::class;
            }
        );
    }

    private function registerLogTarget(): void
    {
        Craft::getLogger()->dispatcher->targets[] = new FileTarget([
            'logFile'    => Craft::getAlias('@storage/logs/bunny-storage.log'),
            'categories' => ['bunny-storage'],
            'logVars'    => [],
        ]);
    }
}
