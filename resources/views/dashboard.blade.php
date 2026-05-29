<x-layouts::app :title="__('Dashboard')">
    <div class="flex h-full w-full flex-1 flex-col gap-6 rounded-xl p-2">
        {{-- Welcome + friend code --}}
        <div
            class="rounded-xl border border-neutral-200 bg-white p-5 dark:border-neutral-700 dark:bg-zinc-900"
            x-data="{
                code: @js(auth()->user()->friend_code),
                copied: false,
                copy() {
                    navigator.clipboard.writeText(this.code);
                    this.copied = true;
                    setTimeout(() => this.copied = false, 1500);
                },
            }"
        >
            <flux:heading size="lg">
                {{ __('Welcome back,') }} {{ auth()->user()->name }} 👋
            </flux:heading>
            <flux:text class="mt-1 text-zinc-500">
                {{ __('Your friend code:') }}
            </flux:text>
            <div class="mt-2 flex flex-wrap items-center gap-2">
                <div
                    class="rounded-lg bg-zinc-100 px-3 py-1.5 font-mono text-sm tracking-widest dark:bg-zinc-800"
                    data-test="dashboard-friend-code"
                >{{ auth()->user()->friend_code }}</div>
                <flux:button size="sm" type="button" variant="ghost" icon="clipboard" x-on:click="copy()">
                    <span x-text="copied ? @js(__('Copied!')) : @js(__('Copy'))"></span>
                </flux:button>
            </div>
        </div>

        {{-- Stats placeholder --}}
        <div class="grid auto-rows-min gap-4 md:grid-cols-3">
            <div class="relative aspect-video overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
                <x-placeholder-pattern class="absolute inset-0 size-full stroke-gray-900/20 dark:stroke-neutral-100/20" />
            </div>
            <div class="relative aspect-video overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
                <x-placeholder-pattern class="absolute inset-0 size-full stroke-gray-900/20 dark:stroke-neutral-100/20" />
            </div>
            <div class="relative aspect-video overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
                <x-placeholder-pattern class="absolute inset-0 size-full stroke-gray-900/20 dark:stroke-neutral-100/20" />
            </div>
        </div>

        <div class="relative h-full flex-1 overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
            <x-placeholder-pattern class="absolute inset-0 size-full stroke-gray-900/20 dark:stroke-neutral-100/20" />
        </div>
    </div>
</x-layouts::app>
