<?php

namespace JaxWilko\Game\FormWidgets;

use Backend\Classes\FormWidgetBase;
use JaxWilko\Game\Classes\Engine\Modules\World\Level;
use System\Classes\PluginManager;

class LevelEditor extends FormWidgetBase
{
    public const OBJECT_OPTION_SPRITE_MAP = 'spriteMap';
    public const OBJECT_OPTION_ANIMATION_RANDOM_DELAY = 'animationRandomDelay';
    public const OBJECT_OPTION_SCRIPT = 'script';
    public const OBJECT_OPTION_TELEPORT = 'teleport';
    public const OBJECT_OPTION_PLAYERS_ONLY = 'playersOnly';
    public const OBJECT_OPTION_ITEM = 'item';
    public const OBJECT_OPTION_NAME = 'name';
    public const OBJECT_OPTION_QUESTS = 'quests';
    public const OBJECT_OPTION_INVULNERABLE = 'invulnerable';
    public const OBJECT_OPTION_INVENTORY = 'inventory';

    protected array $defaultObjects = [
        Level::LAYER_BACKGROUND => [
            \JaxWilko\Game\Classes\Engine\Core\Objects\Generic\GenericStaticWorldObject::class => "Generic Object",
        ],
        Level::LAYER_BLOCKS => [
            \JaxWilko\Game\Classes\Engine\Core\Objects\Generic\GenericStaticWorldObject::class => "Generic Object"
        ],
        Level::LAYER_PROPS => [
            \JaxWilko\Game\Classes\Engine\Core\Objects\Generic\GenericStaticWorldObject::class => "Generic Object"
        ],
        Level::LAYER_TRIGGERS => [
            \JaxWilko\Game\Classes\Engine\Core\Objects\Generic\GenericStaticTriggerObject::class => "Generic Trigger",
            \JaxWilko\Game\Classes\Engine\Core\Objects\Generic\GenericItemObject::class => "Item",
        ],
        Level::LAYER_MARKERS => [
            \JaxWilko\Game\Classes\Engine\Core\Objects\Generic\GenericStaticWorldObject::class => "Generic Object"
        ],
        Level::LAYER_ACTORS => [
            \JaxWilko\Game\Classes\Engine\Core\Objects\Generic\GenericAiActor::class => "Generic Ai",
            \JaxWilko\Game\Classes\Engine\Core\Objects\Generic\GenericHostileAiActor::class => "Generic Hostile Ai"
        ],
        Level::LAYER_SPRITES => [
            \JaxWilko\Game\Classes\Engine\Core\Objects\Generic\GenericStaticSpriteObject::class => "Generic Sprite"
        ],
        Level::LAYER_PROPS_TOP => [
            \JaxWilko\Game\Classes\Engine\Core\Objects\Generic\GenericStaticWorldObject::class => "Generic Object"
        ]
    ];

    protected array $defaultObjectOptions = [
        \JaxWilko\Game\Classes\Engine\Core\Objects\Generic\GenericStaticWorldObject::class => [
            self::OBJECT_OPTION_SPRITE_MAP,
            self::OBJECT_OPTION_ANIMATION_RANDOM_DELAY,
        ],
        \JaxWilko\Game\Classes\Engine\Core\Objects\Generic\GenericStaticTriggerObject::class => [
            self::OBJECT_OPTION_SPRITE_MAP,
            self::OBJECT_OPTION_ANIMATION_RANDOM_DELAY,
            self::OBJECT_OPTION_SCRIPT,
        ],
        \JaxWilko\Game\Classes\Engine\Core\Objects\Generic\GenericAiActor::class => [
            self::OBJECT_OPTION_SPRITE_MAP,
            self::OBJECT_OPTION_ANIMATION_RANDOM_DELAY,
            self::OBJECT_OPTION_INVULNERABLE,
            self::OBJECT_OPTION_NAME,
            self::OBJECT_OPTION_QUESTS,
            self::OBJECT_OPTION_SCRIPT,
        ],
        \JaxWilko\Game\Classes\Engine\Core\Objects\Generic\GenericHostileAiActor::class => [
            self::OBJECT_OPTION_SPRITE_MAP,
            self::OBJECT_OPTION_ANIMATION_RANDOM_DELAY,
            self::OBJECT_OPTION_SCRIPT,
        ],
        \JaxWilko\Game\Classes\Engine\Core\Objects\Generic\GenericItemObject::class => [
            self::OBJECT_OPTION_ITEM
        ]
    ];

    /**
     * {@inheritDoc}
     */
    public function render()
    {
        $this->prepareVars();
        return $this->makePartial('body');
    }

    /**
     * Prepares the list data
     */
    public function prepareVars()
    {
        $this->vars['model'] = $this->model;
        $this->vars['field'] = $this->formField;
        $this->vars['name'] = $this->getFieldName();
        $this->vars['value'] = $this->getLoadValue();
    }

    /**
     * {@inheritDoc}
     */
    public function loadAssets(): void
    {
        $this->addJs('dist/js/leveleditor.js', 'JaxWilko.Game');
        $this->addCss('dist/css/leveleditor.css', 'JaxWilko.Game');
    }

    protected function mapIntoArray(array $array, array $items): array
    {
        foreach ($items as $item) {
            foreach ($item as $layer => $object) {
                foreach ($object as $key => $value) {
                    $array[$layer][$key] = $value;
                }
            }
        }

        return $array;
    }

    public function onRegisterObjects(): array
    {
        return [
            'objects' => $this->mapIntoArray(
                $this->defaultObjects,
                PluginManager::instance()->getRegistrationMethodValues('registerGameObjects')
            ),
            'objectOptions' => $this->mapIntoArray(
                $this->defaultObjectOptions,
                PluginManager::instance()->getRegistrationMethodValues('registerGameObjectOptions')
            )
        ];
    }
}
