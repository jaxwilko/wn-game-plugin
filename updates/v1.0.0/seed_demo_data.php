<?php

use Winter\Storm\Database\Updates\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $data = $this->getDemoData();

        // Create demo levels
        foreach ($data as $class => $items) {
            foreach ($items as $item) {
                $class::create($item);
            }
        }
    }

    protected function getDemoData(): array
    {
        return [
            \JaxWilko\Game\Models\Level::class => [
                [
                    'name' => 'Demo',
                    'code' => 'demo',
                    'is_active' => true,
                    'data' => '{"background":"#4b692f","void":"#abc9f7","level":{"size":[[0,0],["1000","600"]]},"layers":[[{"settings":{"colour":"#5ac71f"},"class":"JaxWilko\\\\Game\\\\Classes\\\\Objects\\\\Backgrounds\\\\Grass","vector":{"x":31,"y":216},"size":{"x":"64","y":"64"}},{"settings":{"colour":"#5ac71f"},"class":"JaxWilko\\\\Game\\\\Classes\\\\Objects\\\\Backgrounds\\\\Grass","vector":{"x":345,"y":248},"size":{"x":"64","y":"64"}},{"settings":{"colour":"#5ac71f"},"class":"JaxWilko\\\\Game\\\\Classes\\\\Objects\\\\Backgrounds\\\\Grass","vector":{"x":122,"y":416},"size":{"x":"64","y":"64"}},{"settings":{"colour":"#5ac71f"},"class":"JaxWilko\\\\Game\\\\Classes\\\\Objects\\\\Backgrounds\\\\Grass","vector":{"x":492,"y":482},"size":{"x":"64","y":"64"}},{"settings":{"colour":"#5ac71f"},"class":"JaxWilko\\\\Game\\\\Classes\\\\Objects\\\\Backgrounds\\\\Grass","vector":{"x":219,"y":14},"size":{"x":"64","y":"64"}},{"settings":{"colour":"#5ac71f"},"class":"JaxWilko\\\\Game\\\\Classes\\\\Objects\\\\Backgrounds\\\\Grass","vector":{"x":845,"y":290},"size":{"x":"64","y":"64"}},{"settings":{"colour":"#5ac71f"},"class":"JaxWilko\\\\Game\\\\Classes\\\\Objects\\\\Backgrounds\\\\Grass","vector":{"x":722,"y":102},"size":{"x":"64","y":"64"}},{"settings":{"colour":"#1FC0C8","spriteMap":{"idle":{"sheet":"/storage/app/media/game/puddle.png","align":["64","64"],"delay":20}},"animationRandomDelay":true},"class":"JaxWilko\\\\Game\\\\Classes\\\\Engine\\\\Core\\\\Objects\\\\Generic\\\\GenericStaticWorldObject","vector":{"x":51,"y":320},"size":{"x":"64","y":"64"}},{"settings":{"colour":"#1FC0C8","spriteMap":{"idle":{"sheet":"/storage/app/media/game/puddle.png","align":["64","64"],"delay":20}},"animationRandomDelay":true},"class":"JaxWilko\\\\Game\\\\Classes\\\\Engine\\\\Core\\\\Objects\\\\Generic\\\\GenericStaticWorldObject","vector":{"x":715,"y":229},"size":{"x":"64","y":"64"}},{"settings":{"colour":"#1FC0C8","spriteMap":{"idle":{"sheet":"/storage/app/media/game/puddle.png","align":["64","64"],"delay":20}},"animationRandomDelay":true},"class":"JaxWilko\\\\Game\\\\Classes\\\\Engine\\\\Core\\\\Objects\\\\Generic\\\\GenericStaticWorldObject","vector":{"x":644,"y":501},"size":{"x":"64","y":"64"}},{"settings":{"colour":"#727474","spriteMap":{"idle":{"sheet":"/storage/app/media/game/exit-up.png","align":["64",32],"delay":20}}},"class":"JaxWilko\\\\Game\\\\Classes\\\\Engine\\\\Core\\\\Objects\\\\Generic\\\\GenericStaticWorldObject","vector":{"x":827,"y":1},"size":{"x":"64","y":32}}],[{"settings":{"colour":"#1FC0C8","spriteMap":{"idle":{"sheet":"/storage/app/media/game/wood.png","align":["64","64"],"delay":20}}},"class":"JaxWilko\\\\Game\\\\Classes\\\\Engine\\\\Core\\\\Objects\\\\Generic\\\\GenericStaticWorldObject","vector":{"x":"172","y":"100"},"size":{"x":"64","y":"64"}},{"settings":{"colour":"#1FC0C8","spriteMap":{"idle":{"sheet":"/storage/app/media/game/wood.png","align":["64","64"],"delay":20}}},"class":"JaxWilko\\\\Game\\\\Classes\\\\Engine\\\\Core\\\\Objects\\\\Generic\\\\GenericStaticWorldObject","vector":{"x":60,"y":"100"},"size":{"x":"64","y":"64"}},{"settings":{"colour":"#ffffff","spriteMap":{"idle":{"sheet":"/storage/app/media/game/window.png","align":["32","32"],"delay":20}}},"class":"JaxWilko\\\\Game\\\\Classes\\\\Engine\\\\Core\\\\Objects\\\\Generic\\\\GenericStaticWorldObject","vector":{"x":"77","y":"116"},"size":{"x":"32","y":"32"}},{"settings":{"colour":"#ffffff","spriteMap":{"idle":{"sheet":"/storage/app/media/game/window.png","align":["32","32"],"delay":20}}},"class":"JaxWilko\\\\Game\\\\Classes\\\\Engine\\\\Core\\\\Objects\\\\Generic\\\\GenericStaticWorldObject","vector":{"x":189,"y":"116"},"size":{"x":"32","y":"32"}},{"settings":{"colour":"#d35d1d","spriteMap":{"idle":{"sheet":"/storage/app/media/game/roof.png","align":["176","64"],"delay":20}}},"class":"JaxWilko\\\\Game\\\\Classes\\\\Engine\\\\Core\\\\Objects\\\\Generic\\\\GenericStaticWorldObject","vector":{"x":"60","y":"36"},"size":{"x":"176","y":"64"}},{"settings":{"colour":"#abc9f7"},"class":"JaxWilko\\\\Game\\\\Classes\\\\Engine\\\\Core\\\\Objects\\\\Generic\\\\GenericStaticWorldObject","vector":{"x":362,"y":427},"size":{"x":"64","y":"190"}},{"settings":{"colour":"#abc9f7"},"class":"JaxWilko\\\\Game\\\\Classes\\\\Engine\\\\Core\\\\Objects\\\\Generic\\\\GenericStaticWorldObject","vector":{"x":"742","y":433},"size":{"x":"300","y":"190"}},{"settings":{"colour":"#abc9f7"},"class":"JaxWilko\\\\Game\\\\Classes\\\\Engine\\\\Core\\\\Objects\\\\Generic\\\\GenericStaticWorldObject","vector":{"x":"463","y":"0"},"size":{"x":"180","y":"313"}},{"settings":{"colour":"#abc9f7"},"class":"JaxWilko\\\\Game\\\\Classes\\\\Engine\\\\Core\\\\Objects\\\\Generic\\\\GenericStaticWorldObject","vector":{"x":426,"y":573},"size":{"x":"332","y":"30"}},{"settings":{"colour":"#1FC0C8","spriteMap":{"idle":{"sheet":"/storage/app/media/game/house.png","align":["176","128"],"delay":20}}},"class":"JaxWilko\\\\Game\\\\Classes\\\\Engine\\\\Core\\\\Objects\\\\Generic\\\\GenericStaticWorldObject","vector":{"x":"170","y":"385"},"size":{"x":"176","y":"128"}},{"settings":{"colour":"#85898a","spriteMap":{"idle":{"sheet":"/storage/app/media/game/fence-left.png","align":["64","64"],"delay":20}}},"class":"JaxWilko\\\\Game\\\\Classes\\\\Engine\\\\Core\\\\Objects\\\\Generic\\\\GenericStaticWorldObject","vector":{"x":757,"y":"0"},"size":{"x":"64","y":"64"}},{"settings":{"colour":"#85898a","spriteMap":{"idle":{"sheet":"/storage/app/media/game/fence-right.png","align":["64","64"],"delay":20}}},"class":"JaxWilko\\\\Game\\\\Classes\\\\Engine\\\\Core\\\\Objects\\\\Generic\\\\GenericStaticWorldObject","vector":{"x":898,"y":0},"size":{"x":"64","y":"64"}}],null,[{"settings":{"colour":"#915546","spriteMap":{"idle":{"sheet":"/storage/app/media/game/door.png","align":["48","64"],"delay":20}},"level":"demo-house","target":"150,200"},"class":"JaxWilko\\\\Game\\\\Classes\\\\Objects\\\\Triggers\\\\Teleport","vector":{"x":"124","y":"100"},"size":{"x":"48","y":"64"}},{"settings":{"colour":"rgba(0, 0, 0, 0)"},"class":"JaxWilko\\\\Game\\\\Classes\\\\Objects\\\\Triggers\\\\Spawn","vector":{"x":232,"y":275},"size":{"x":32,"y":32}},{"settings":{"colour":"rgba(0, 0, 0, 0)","target":"280,300","spriteMap":{"idle":{"sheet":"/storage/app/media/game/transparent.png","align":[32,32],"delay":20}},"level":"demo-graveyard","playersOnly":true},"class":"JaxWilko\\\\Game\\\\Classes\\\\Objects\\\\Triggers\\\\Teleport","vector":{"x":822,"y":1},"size":{"x":"76","y":32}}],null,[{"settings":{"colour":"#1FC0C8","name":"Steve","invulnerable":true,"quests":["demoA","demoB"]},"class":"JaxWilko\\\\Game\\\\Classes\\\\Objects\\\\Entities\\\\Npc","vector":{"x":69,"y":495},"size":{"x":"32","y":"64"}}]]}'
                ],
                [
                    'name' => 'Demo House',
                    'code' => 'demo-house',
                    'is_active' => true,
                    'data' => '{"background":"#9d8c4d","void":"#869c96","level":{"size":[[0,0],["300","300"]]},"layers":[[{"settings":{"colour":"#1FC0C8","spriteMap":{"idle":{"sheet":"/storage/app/media/game/wood-panel.png","align":["64","64"],"delay":20}}},"class":"JaxWilko\\\\Game\\\\Classes\\\\Engine\\\\Core\\\\Objects\\\\Generic\\\\GenericStaticWorldObject","vector":{"x":33,"y":34},"size":{"x":"64","y":"64"}},{"settings":{"colour":"#1FC0C8","spriteMap":{"idle":{"sheet":"/storage/app/media/game/wood-panel.png","align":["64","64"],"delay":20}}},"class":"JaxWilko\\\\Game\\\\Classes\\\\Engine\\\\Core\\\\Objects\\\\Generic\\\\GenericStaticWorldObject","vector":{"x":193,"y":91},"size":{"x":"64","y":"64"}},{"settings":{"colour":"#1FC0C8","spriteMap":{"idle":{"sheet":"/storage/app/media/game/wood-panel.png","align":["64","64"],"delay":20}}},"class":"JaxWilko\\\\Game\\\\Classes\\\\Engine\\\\Core\\\\Objects\\\\Generic\\\\GenericStaticWorldObject","vector":{"x":74,"y":173},"size":{"x":"64","y":"64"}}],null,null,[{"settings":{"colour":"#a61c3f","spriteMap":{"idle":{"sheet":"/storage/app/media/game/carpet.png","align":["48",32],"delay":20}},"level":"demo","target":"135,165"},"class":"JaxWilko\\\\Game\\\\Classes\\\\Objects\\\\Triggers\\\\Teleport","vector":{"x":131,"y":268},"size":{"x":"48","y":32}},{"settings":{"colour":"#d9d320","containerName":"Chest","spriteMap":{"idle":{"sheet":"/storage/app/media/game/chest/chest.png","align":[32,32],"delay":20},"open":{"sheet":"/storage/app/media/game/chest/chest-open.png","align":[32,32],"delay":20}}},"class":"JaxWilko\\\\Game\\\\Classes\\\\Objects\\\\Triggers\\\\Inventory","vector":{"x":"50","y":"75"},"size":{"x":32,"y":32}},{"settings":{"colour":"#d3d320","containerName":"Chest","spriteMap":{"idle":{"sheet":"/storage/app/media/game/chest/chest.png","align":[32,32],"delay":20},"open":{"sheet":"/storage/app/media/game/chest/chest-open.png","align":[32,32],"delay":20}}},"class":"JaxWilko\\\\Game\\\\Classes\\\\Objects\\\\Triggers\\\\Inventory","vector":{"x":"218","y":"75"},"size":{"x":32,"y":32}}]]}'
                ],
                [
                    'name' => 'Demo Graveyard',
                    'code' => 'demo-graveyard',
                    'is_active' => true,
                    'data' => '{"background":"#514f4d","void":"#402b2b","level":{"size":[[0,0],["600","400"]]},"layers":[[{"settings":{"colour":"#1FC0C8","spriteMap":{"idle":{"sheet":"/storage/app/media/game/stones.png","align":["64","48"],"delay":20}}},"class":"JaxWilko\\\\Game\\\\Classes\\\\Engine\\\\Core\\\\Objects\\\\Generic\\\\GenericStaticWorldObject","vector":{"x":73,"y":278},"size":{"x":"64","y":"48"}},{"settings":{"colour":"#1FC0C8","spriteMap":{"idle":{"sheet":"/storage/app/media/game/stones.png","align":["64","48"],"delay":20}}},"class":"JaxWilko\\\\Game\\\\Classes\\\\Engine\\\\Core\\\\Objects\\\\Generic\\\\GenericStaticWorldObject","vector":{"x":431,"y":133},"size":{"x":"64","y":"48"}},{"settings":{"colour":"#1FC0C8","spriteMap":{"idle":{"sheet":"/storage/app/media/game/stones.png","align":["64","48"],"delay":20}}},"class":"JaxWilko\\\\Game\\\\Classes\\\\Engine\\\\Core\\\\Objects\\\\Generic\\\\GenericStaticWorldObject","vector":{"x":144,"y":15},"size":{"x":"64","y":"48"}},{"settings":{"colour":"#1FC0C8","spriteMap":{"idle":{"sheet":"/storage/app/media/game/stones.png","align":["64","48"],"delay":20}}},"class":"JaxWilko\\\\Game\\\\Classes\\\\Engine\\\\Core\\\\Objects\\\\Generic\\\\GenericStaticWorldObject","vector":{"x":493,"y":313},"size":{"x":"64","y":"48"}}],null,null,[{"settings":{"colour":"#1FC0C8"},"class":"JaxWilko\\\\Game\\\\Classes\\\\Objects\\\\Triggers\\\\Spawner","vector":{"x":61,"y":47},"size":{"x":32,"y":32}},{"settings":{"colour":"#1FC0C8"},"class":"JaxWilko\\\\Game\\\\Classes\\\\Objects\\\\Triggers\\\\Spawner","vector":{"x":492,"y":42},"size":{"x":32,"y":32}},{"settings":{"colour":"#818d8d","spriteMap":{"idle":{"sheet":"/storage/app/media/game/exit.png","align":["64",32],"delay":20}},"level":"demo","target":"845,64"},"class":"JaxWilko\\\\Game\\\\Classes\\\\Objects\\\\Triggers\\\\Teleport","vector":{"x":261,"y":368},"size":{"x":"64","y":32}},{"settings":{"colour":"#c7491f","spriteMap":{"idle":{"sheet":"/storage/app/media/game/fire.png","align":["64","64"],"delay":20}},"script":"$entities = $this->getActorsInside($level);\n\nif ($entities) {\n    foreach ($entities as $entity) {\n        $entity->damage(1)->save();\n    }\n}\n\n$this->vector->tapX(-1);\nif ($this->vector->x() < 1) {\n    $this->vector->x(536);\n}\n$this->save();"},"class":"JaxWilko\\\\Game\\\\Classes\\\\Engine\\\\Core\\\\Objects\\\\Generic\\\\GenericStaticTriggerObject","vector":{"x":"536","y":189},"size":{"x":"64","y":"64"}}]]}'
                ],
            ],
            \JaxWilko\Game\Models\Item::class => [
                [
                    'code' => 'meat',
                    'data' => json_decode('{"label":"Meat","description":"Can be consumed to heal","size":"24,24","icon":"\/game\/meat\/icon.png","spriteMap":[{"state":"idle","sheet":"\/game\/meat\/meat.png","align":"24,24","delay":20}],"usage":"$entity->heal(30)\n    ->removeInventoryItem(\'meat\')\n    ->save();"}', JSON_OBJECT_AS_ARRAY),
                ],
                [
                    'code' => 'winter',
                    'data' => json_decode('{"label":"Snowflake of Winter","description":"Increases damage by 5","size":"24,24","icon":"\/game\/winter\/icon.png","spriteMap":[{"state":"idle","sheet":"\/game\/winter\/winter.png","align":"24,24","delay":20}],"usage":"$entity->increaseDamage(5)\n    ->removeInventoryItem(\'winter\')\n    ->save();"}', JSON_OBJECT_AS_ARRAY),
                ],
            ],
            \JaxWilko\Game\Models\LootTable::class => [
                [
                    'code' => 'human',
                    'data' => json_decode('[{"code":"meat","chance":0.6},{"code":"cheese","chance":0.12}]', JSON_OBJECT_AS_ARRAY),
                ],
                [
                    'code' => 'zombie',
                    'data' => json_decode('[{"code":"meat","chance":0.5},{"code":"gold","chance":0.3}]', JSON_OBJECT_AS_ARRAY),
                ]
            ],
            \JaxWilko\Game\Models\Quest::class => [
                [
                    'code' => 'demoA',
                    'data' => json_decode('{"title":"Sell meat","description":"Sell me some meat please","reward":[{"code":"gold","quantity":1}],"completion":" if (!$player->hasInventoryItem(\'meat\')) {\n    return false;\n}\n\n$player->removeInventoryItem(\'meat\');\nreturn true;","repeatable":"1","prerequisite":[]}', JSON_OBJECT_AS_ARRAY),
                ],
                [
                    'code' => 'demoB',
                    'data' => json_decode('{"title":"Buy the snowflake of winter","description":"Obtain this legendary item by parting with your coin (10 gold)","prerequisite":[{"quest":"demoA"}],"reward":[{"code":"winter","quantity":1}],"completion":" if (!$player->hasInventoryItem(\'gold\', 10)) {\n    return false;\n}\n\n$player->removeInventoryItem(\'gold\', 10);\nreturn true;","repeatable":"1"}', JSON_OBJECT_AS_ARRAY),
                ]
            ],
        ];
    }

    public function down(): void
    {
    }
};
