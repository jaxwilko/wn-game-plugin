<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:ital@0;1&display=swap" rel="stylesheet">


<div id="jax-tailwind" class="h-full">
    <div class="stretch w-full">
        <div class="my-6">
            <?php if ($running): ?>
                <div class="flex">
                    <span id="stop-btn" class="py-3 px-6 text-4xl bg-red-600 hover:bg-red-700 focus:ring-red-500 focus:ring-offset-red-200 text-white transition ease-in duration-200 text-center shadow-md focus:outline-none focus:ring-2 focus:ring-offset-2 rounded-lg select-none cursor-pointer mr-6">
                        STOP
                    </span>
                    <span id="refresh-btn" class="py-3 px-6 text-4xl bg-white hover:bg-gray-200 border-gray-600 border border-solid focus:ring-gray-500 focus:ring-offset-red-200 text-gray-800 transition ease-in duration-200 text-center shadow-md focus:outline-none focus:ring-2 focus:ring-offset-2 rounded-lg select-none cursor-pointer">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                        </svg>
                    </span>
                </div>
            <?php else: ?>
                <div class="flex">
                    <span id="start-btn" class="py-3 px-6 text-4xl bg-green-600 hover:bg-green-700 focus:ring-green-500 focus:ring-offset-green-200 text-white transition ease-in duration-200 text-center shadow-md focus:outline-none focus:ring-2 focus:ring-offset-2 rounded-lg select-none cursor-pointer mr-6">
                        START
                    </span>
                    <select id="level-select" class="py-3 px-6 text-4xl text-gray-600 bg-white shadow-md rounded-lg select-none cursor-pointer">
                        <option disabled selected value>Please select...</option>
                        <?php foreach ($levels as $level): ?>
                            <option value="<?= $level ?>"><?= $level ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <div id="panel-container">
        <?= $running ? '' : $this->makePartial('panel') ?>
    </div>
    <script>
        $('#start-btn').on('click', function () {
            const level = $('#level-select').val();
            if (!level) {
                return;
            }

            $.request('onServerStart', {
                data: {
                    level
                },
                success: () => {
                    window.location.reload();
                }
            });
        });
        $('#stop-btn').on('click', function () {
            $.request('onServerStop', {
                success: () => {
                    window.location.reload();
                }
            });
        });
        <?php if ($running): ?>
            $("#refresh-btn").on('click', function () {
                const svg = $(this).find('svg');
                svg.addClass('animate-spin');
                $.request('onRenderPanel', {
                    success: (response) => {
                        svg.removeClass('animate-spin');

                        if (!response.status) {
                            window.location.reload();
                        }

                        $('#panel-container').html(response.partial);
                    },
                    error: (response) => {
                        svg.removeClass('animate-spin');
                        console.error(response);
                    }
                })
            });

            const refreshPanel = () => {
                $("#refresh-btn").click();
                setTimeout(refreshPanel, 10000);
            };

            refreshPanel();
        <?php endif; ?>
    </script>
</div>
