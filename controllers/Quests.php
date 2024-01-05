<?php namespace JaxWilko\Game\Controllers;

use BackendMenu;
use Backend\Classes\Controller;

/**
 * Quests Backend Controller
 */
class Quests extends Controller
{
    /**
     * @var array Behaviors that are implemented by this controller.
     */
    public $implement = [
        \Backend\Behaviors\FormController::class,
        \Backend\Behaviors\ListController::class,
    ];

    public function __construct()
    {
        parent::__construct();

        BackendMenu::setContext('JaxWilko.Game', 'game', 'quests');
    }
}
