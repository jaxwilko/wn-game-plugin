<?php

namespace JaxWilko\Game\Console;

use Illuminate\Console\Command;
use JaxWilko\Game\Classes\Engine\Engine;
use JaxWilko\Game\Classes\Engine\Network\GameApplication;
use JaxWilko\Game\Classes\Engine\Core\Utils\Console;
use Ratchet\Http\HttpServer;
use Ratchet\Server\IoServer;
use Ratchet\WebSocket\WsServer;
use Winter\Storm\Support\Facades\Config;
use function Termwind\render;

class GameServe extends Command
{
    /**
     * @var string|null The default command name for lazy loading.
     */
    protected static $defaultName = 'game:serve';

    /**
     * @var string The name and signature of this command.
     */
    protected $signature = 'game:serve
         {--m|map= : Map to load.}
         {--f|flush : Flush data before serving.}
         {--s|status : Get server status.}
         {--k|kill : Kill the game server.}
         {--p|pid : Get the server process id.}
         {--d|daemonize : Daemonize the game server.}
    ';

    /**
     * @var string The console command description.
     */
    protected $description = 'GameServer Serve Command';

    /**
     * Execute the console command.
     * @return int
     */
    public function handle(): int
    {
        $lock = $this->getLockFile();

        if ($this->option('kill')) {
            if (!is_file($lock) || !posix_getpgid((int) file_get_contents($lock))) {
                $this->notice('Server is not running', 'WARN');
                return 1;
            }
            posix_kill((int) file_get_contents($lock), defined('SIGTERM') ? SIGTERM : 15);
            unlink($lock);
            return 0;
        }

        if ($this->option('status')) {
            $status = $this->isRunning();
            $this->{$status ? 'info' : 'warn'}($status ? 'running' : 'stopped');
            return 0;
        }

        if ($this->option('pid')) {
            if (!$this->isRunning()) {
                return 0;
            }
            $this->info(file_get_contents($lock));
            return 0;
        }

        if ($this->option('flush')) {
            if ($this->isRunning()) {
                $this->notice('Unable to flush cache, server is running', 'ERR');
                return 1;
            }
            $this->option('daemonize') || Console::put('Flushing engine cache...');
            Console::withoutOutput(fn () => (new Engine(threads: 0))->boot()->execFlush());
        }

        if ($this->option('daemonize')) {
            if ($this->isRunning()) {
                $this->notice('Cannot start, server is running', 'ERR');
                return 1;
            }

            $pid = pcntl_fork();
            if ($pid) {
                return 0;
            }

            pcntl_signal(SIGHUP, function () {
                dump([
                    'SIGHUP' => func_get_args()
                ]);
            });

            pcntl_signal(SIGTERM, function () {
                exit(0);
            });

            // Daemonize
            $pid = pcntl_fork();
            if ($pid) {
                return 0;
            }

            $this->notice(sprintf('game server running as pid <span class="text-red-600">%d</span>', posix_getpid()));

            // Close handles
            fclose(STDIN);
            fclose(STDOUT);
            fclose(STDERR);

            // Disable STDOUT from the console
            Console::setStdOut(false);
        }

        // Register new console logging output handle
        Console::setOutputHandle(fopen(storage_path('logs/game.log'), 'wb'));

        set_error_handler(function (int $errno, string $errStr, string $errFile, int $errLine) {
            Console::put('ERROR (%d): %s. %s@%d', $errno, $errStr, $errFile, $errLine);
        });

        $fp = fopen($lock, 'w+');

        if (!flock($fp, LOCK_EX|LOCK_NB)) {
            $this->error('Unable to obtain lock');
            return 1;
        }

        fputs($fp, posix_getpid());

        $settings = [];

        if ($this->option('map')) {
            $settings[\JaxWilko\Game\Classes\Engine\Modules\World\WorldModule::class] = [
                'map' => $this->option('map')
            ];
        }

        // Handle event loop
        $app = new GameApplication($settings);

        $server = IoServer::factory(
            new HttpServer(
                new WsServer(
                    $app
                )
            ),
            Config::get('jaxwilko.game::server.port')
        );

        $server->loop->addPeriodicTimer($app->getEngine()->getTickRate(), function () use ($app) {
            $app->onTick();
        });

        // Run the game loop
        $server->run();

        return 0;
    }

    public function getLockFile(): string
    {
        return storage_path('game.lock');
    }

    public function isRunning(): bool
    {
        $lock = $this->getLockFile();

        if (!is_file($lock) || !($contents = file_get_contents($lock))) {
            return false;
        }

        return !!posix_getpgid((int) $contents);
    }

    public function notice(string $str, string $type = 'INFO')
    {
        $class = match ($type) {
            'INFO' => 'bg-green-600',
            'WARN' => 'bg-yellow-600',
            default => 'bg-red-600'
        };

        render(<<<HTML
            <div>
                <div class="px-1 $class">$type</div>
                <em class="ml-1">
                    $str
                </em>
            </div>
        HTML);
    }
}
