<div id="jax-tailwind" class="h-full">
    <div class="flex-layout-row flex-layout-item stretch w-full">
        <div class="flex-layout-item stretch-constrain relative">
            <div id="level-editor">
                <level-editor id="<?= $this->getId('textarea') ?>" name="<?= $name ?>" :value="<?= e($value) ?>"></level-editor>
            </div>
        </div>
    </div>
</div>
