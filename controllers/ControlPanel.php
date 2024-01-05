<?php

namespace JaxWilko\Game\Controllers;

use BackendMenu;
use Backend\Classes\Controller;
use Illuminate\Support\Facades\Artisan;
use JaxWilko\Game\Classes\Engine\Modules\World\Level;

/**
 * Control Panel Backend Controller
 */
class ControlPanel extends Controller
{
    public function __construct()
    {
        parent::__construct();

        BackendMenu::setContext('JaxWilko.Game', 'game', 'controlPanel');
    }

    public function index()
    {
        $this->pageTitle = 'Control Panel';

        $this->addCss('plugins/jaxwilko/game/formwidgets/leveleditor/assets/dist/css/leveleditor.css', 'JaxWilko.Game');

        $this->prepareVars();
    }

    protected function prepareVars(): void
    {
        $this->vars['running'] = $this->artisan('game:serve -s') === 'running';
        $this->vars['pid'] = $this->artisan('game:serve -p');
        $this->vars['info'] = $this->vars['running']
            ? $this->getStats($this->vars['pid'])
            : '';
        $this->vars['levels'] = !$this->vars['running'] ? Level::getAvailableLevels() : [];

        $this->vars['log'] = file_exists(storage_path('logs/game.log')) ? file_get_contents(storage_path('logs/game.log')) : '';
    }

    protected function getStats(int $pid): array
    {
        $data = file('/proc/' . $pid  . '/status');
        $info = [];
        foreach ($data as $line) {
            $parts = explode(':', $line, 2);
            $info[$parts[0]] = trim($parts[1] ?? null);
        }
        return $info;
    }

    protected function artisan(string $command): string
    {
        Artisan::call($command);
        return trim(Artisan::output());
    }

    public function onServerStart()
    {
        $level = request()->input('level');

        if (!$level) {
            abort(400);
        }

        shell_exec(
            sprintf(
                '%s game:serve -f -d -m %s > %s 2>&1 &',
                base_path('artisan'),
                escapeshellarg($level),
                storage_path('logs/game-start.log')
            )
        );

        sleep(1);

        return [
            'status' => $this->artisan('game:serve -s') === 'running' ? 'running' : 'stopped'
        ];
    }

    public function onServerStop()
    {
        $this->artisan('game:serve -k');

        usleep(500);

        return [
            'status' => $this->artisan('game:serve -s') === 'running' ? 'running' : 'stopped'
        ];
    }

    public function onRenderPanel()
    {
        $this->prepareVars();
        return [
            'status' => $this->vars['running'],
            'partial' => $this->makePartial('panel')
        ];
    }
}
