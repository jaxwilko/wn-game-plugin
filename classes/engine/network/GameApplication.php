<?php

namespace JaxWilko\Game\Classes\Engine\Network;

use Auth;
use JaxWilko\Game\Classes\Engine\Core\Utils\Console;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use JaxWilko\Game\Classes\Engine\Engine;
use SplObjectStorage;

class GameApplication implements MessageComponentInterface
{
    /**
     * @var SplObjectStorage $clients
     */
    protected SplObjectStorage $clients;

    /**
     * @var Engine the engine instance running inside the game application
     */
    protected Engine $engine;

    /**
     * @var int max seconds before sending a client update
     */
    protected int $timeoutUpdate = 10;

    /**
     * @var array map of previous message hashes and clients
     */
    protected array $clientPreviousMessage = [];

    /**
     * @var array map of when each client was last sent an update
     */
    protected array $clientLastUpdate = [];

    /**
     * Inits new client list & engine and passes settings array to engine
     *
     * @param array $settings
     * @throws \Weird\Exceptions\ProcessSpawnFailed
     */
    public function __construct(array $settings = [])
    {
        $this->clients = new \SplObjectStorage();
        $this->engine = (new Engine())->boot($settings);
    }

    /**
     * Handles new connections to the application.
     *
     * @param ConnectionInterface $connection
     * @return void
     */
    public function onOpen(ConnectionInterface $connection): void
    {
        $cookies = $this->getCookiesFromConnection($connection);

        if (!$this->clients->contains($connection)) {
            $this->clients->attach($connection);

            $id = $this->clients->getHash($connection);

            try {
                // @TODO: fix all this
                $decoded = \App::make('encrypter')->decrypt(urldecode($cookies['user_auth']), false);
                $parts = explode('|', $decoded);
                $data = json_decode($parts[1]);
                $userId = $data[0];
                $user = Auth::findUserById($userId);

                if (!$user) {
                    throw new \Exception('user missing');
                }
            } catch (\Throwable $e) {
                dd($e->getMessage());
            }

            // create the player
            $this->engine->execAddPlayer($id, $cookies['user_auth'] ?? null);

            // set the player name
            $this->engine->execPlayerSettings($id, [
                'name' => $user->name
            ]);
        }
    }

    /**
     * Handles client disconnects.
     *
     * @param ConnectionInterface $connection
     * @return void
     */
    public function onClose(ConnectionInterface $connection): void
    {
        $id = $this->clients->getHash($connection);

        if ($this->engine->getHasPlayer($id)) {
            $this->engine->execRemovePlayer($id);
        }

        $this->clients->detach($connection);
    }

    /**
     * Handles incoming data/requests.
     * If valid action is given the according method will be called.
     *
     * @param ConnectionInterface $client
     * @param string $data
     * @return void
     */
    public function onMessage(ConnectionInterface $connection, $data): void
    {
        $id = $this->clients->getHash($connection);

        if (!$this->engine->getHasPlayer($id)) {
            Console::put('Encountered message without player, skipping');
            return;
        }

        try {
            $decodedData = $this->decodeData($data);

            switch ($decodedData['action']) {
                case 'settings':
                    $this->engine->execPlayerSettings($id, $decodedData['data']);
                    break;
                case 'controls':
                    $this->engine->execControlPlayer($id, $decodedData['data']);
                    break;
                case 'message':
                    $this->engine->execPlayerMessage($id, $decodedData['data']);
                    break;
                case 'itemUse':
                    $this->engine->execPlayerUseItem($id, $decodedData['data']);
                    break;
                case 'itemDrop':
                    $this->engine->execPlayerDropItem($id, $decodedData['data']);
                    break;
                case 'questAction':
                    $this->engine->execPlayerQuestAction($id, $decodedData['data']);
                    break;
                default:
                    break;
            }
        } catch (\RuntimeException $e) {
            Console::put('Error caught by user message: ' . $e->getMessage());
            Console::dump($e);
        }
    }

    /**
     * Tick event called by the React EventLoop. Triggers the engine tick event and updates players with the latest
     * world data if required.
     *
     * @return void
     */
    public function onTick(): void
    {
        $this->engine
            ->execTick()
            ->execStore()
            ->execWithLayerCache(function () {
                foreach ($this->clients as $client) {
                    $id = $this->clients->getHash($client);
                    $message = $this->encodeData(
                        'state',
                        $this->engine->getPlayerData($this->clients->getHash($client))
                    );

                    $hash = md5($message);

                    if (
                        ($this->clientPreviousMessage[$id] ?? null) !== $hash
                        || $this->clientLastUpdate[$id] < time() - $this->timeoutUpdate
                    ) {
                        $this->clientPreviousMessage[$id] = $hash;
                        $this->clientLastUpdate[$id] = time();

                        $client->send($message);
                    }
                }
            });
    }

    /**
     * On error event handler
     *
     * @param ConnectionInterface $conn
     * @param \Exception $e
     * @return void
     */
    public function onError(ConnectionInterface $conn, \Exception $e): void
    {
        Console::put('Error caught by GameApplication');
        Console::dump($e);
        $conn->close();
    }

    /**
     * Getter to access the engine instance
     *
     * @return Engine
     */
    public function getEngine(): Engine
    {
        return $this->engine;
    }

    /**
     * Helper method to decode user input
     *
     * @param string $data
     * @return mixed
     */
    protected function decodeData(string $data): mixed
    {
        return json_decode($data, JSON_OBJECT_AS_ARRAY);
    }

    /**
     * Helper method to encode key value pairs for users
     *
     * @param string $key
     * @param mixed $data
     * @return string
     */
    protected function encodeData(string $key, mixed $data): string
    {
        return json_encode([$key => $data]);
    }

    /**
     * Helper to access cookies from a connection
     *
     * @param ConnectionInterface $connection
     * @return array
     */
    protected function getCookiesFromConnection(ConnectionInterface $connection): array
    {
        $cookies = array_map(
            fn ($cookie) => explode('=', $cookie),
            explode('; ', $connection->httpRequest->getHeader('cookie')[0])
        );

        $result = [];

        foreach ($cookies as $cookie) {
            $result[$cookie[0]] = $cookie[1];
        }

        return $result;
    }
}
