<?php

namespace JaxWilko\Game\Console;

use Illuminate\Console\Command;
use System\Classes\MediaLibrary;

class GamePublish extends Command
{
    public const GAME_ASSETS_PATH = 'jaxwilko/game/classes/engine/assets';

    /**
     * @var string The console command name.
     */
    protected $name = 'game:publish';

    /**
     * @var string The console command description.
     */
    protected $description = 'Publish game assets to media';

    /**
     * Execute the console command.
     * @return int
     */
    public function handle(): int
    {
        $files = \File::allFiles(plugins_path(static::GAME_ASSETS_PATH));
        $media = MediaLibrary::instance();
        foreach ($files as $file) {
            $media->getStorageDisk()->put(
                $media->getMediaPath('/game/' . $file->getRelativePathname()),
                $file->getContents()
            );
        }

        return 0;
    }
}
