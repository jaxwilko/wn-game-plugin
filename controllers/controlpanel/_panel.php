<div class="stretch w-full">
    <div class="flex-layout-item stretch-constrain relative pt-1">
        <dl class="mt-5 grid grid-cols-1 gap-5 sm:grid-cols-3">
            <div class="overflow-hidden rounded-lg bg-white px-4 py-5 shadow sm:p-6">
                <dt class="truncate text-xl font-medium text-gray-500">Server Status</dt>
                <dd class="mt-1 text-5xl font-semibold tracking-tight">
                    <span class="<?= $running ? 'text-green-600' : 'text-red-600' ?>"><?= $running ? 'RUNNING' : 'OFFLINE' ?></span>
                </dd>
            </div>
            <div class="overflow-hidden rounded-lg bg-white px-4 py-5 shadow sm:p-6">
                <dt class="truncate text-xl font-medium text-gray-500">Peak virtual memory size</dt>
                <dd class="mt-1 text-5xl font-semibold tracking-tight text-gray-700"><?= $running ? $info['VmPeak'] : 0 ?></dd>
            </div>
            <div class="overflow-hidden rounded-lg bg-white px-4 py-5 shadow sm:p-6">
                <dt class="truncate text-xl font-medium text-gray-500">Virtual memory size</dt>
                <dd class="mt-1 text-5xl font-semibold tracking-tight text-gray-700"><?= $running ? $info['VmSize'] : 0 ?></dd>
            </div>
            <div class="overflow-hidden rounded-lg bg-white px-4 py-5 shadow sm:p-6">
                <dt class="truncate text-xl font-medium text-gray-500">Virtual memory data size</dt>
                <dd class="mt-1 text-5xl font-semibold tracking-tight text-gray-700"><?= $running ? $info['VmData'] : 0 ?></dd>
            </div>
            <div class="overflow-hidden rounded-lg bg-white px-4 py-5 shadow sm:p-6">
                <dt class="truncate text-xl font-medium text-gray-500">Virtual memory stack size</dt>
                <dd class="mt-1 text-5xl font-semibold tracking-tight text-gray-700"><?= $running ? $info['VmStk'] : 0 ?></dd>
            </div>
            <div class="overflow-hidden rounded-lg bg-white px-4 py-5 shadow sm:p-6">
                <dt class="truncate text-xl font-medium text-gray-500">Virtual memory text segments size</dt>
                <dd class="mt-1 text-5xl font-semibold tracking-tight text-gray-700"><?= $running ? $info['VmExe'] : 0 ?></dd>
            </div>
        </dl>
    </div>
    <?php if ($running): ?>
        <div class="flex flex-col bg-gray-900 border border-solid border-gray-500 shadow-lg rounded-lg text-white p-6 max-h-[850px] overflow-y-scroll sexy-scroll mono-font">
            <?= str_replace('bg-gray-800', 'flex flex-row flex-nowrap whitespace-nowrap', $log) ?>
        </div>
    <?php endif; ?>
</div>
