<?php

namespace JaxWilko\Game\Components;

use Cms\Classes\ComponentBase;
use JaxWilko\Game\Classes\Engine\Core\Providers\QuestDataProvider;

class Game extends ComponentBase
{
    public function componentDetails()
    {
        return [
            'name' => 'Game Component',
            'description' => 'Add a video to the page'
        ];
    }

    public function onRun()
    {
        $this->page->addCss('/plugins/jaxwilko/game/assets/app.css');
        $this->page->addJs('/plugins/jaxwilko/game/assets/client.js');
    }

    public function onQuestDataProvider()
    {
        return [
            'quests' => (new QuestDataProvider())->register()->getQuests()
        ];
    }
}
