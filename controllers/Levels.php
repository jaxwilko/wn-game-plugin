<?php namespace JaxWilko\Game\Controllers;

use BackendMenu;
use Backend\Classes\Controller;

/**
 * Levels Backend Controller
 */
class Levels extends Controller
{
    /**
     * @var array Behaviors that are implemented by this controller.
     */
    public $implement = [
        \Backend\Behaviors\FormController::class,
        \Backend\Behaviors\ListController::class,
    ];

    public $bodyClass = 'compact-container';

    public function __construct()
    {
        parent::__construct();

        BackendMenu::setContext('JaxWilko.Game', 'game', 'levels');
    }
}
