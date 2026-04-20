<template>
  <footer
    :class="[
      'border-t border-outline-variant/10 bg-surface-container-lowest px-4 py-12 transition-all duration-300 sm:px-6 lg:px-8',
      collapsed ? 'lg:ml-20' : 'lg:ml-64'
    ]"
  >
    <div class="mx-auto max-w-7xl">
      <div class="grid grid-cols-1 gap-10 sm:gap-12 md:grid-cols-2 xl:grid-cols-4">
        <div class="md:col-span-2 xl:col-span-1">
          <span
            class="bg-gradient-to-br from-fuchsia-500 to-purple-700 bg-clip-text font-headline text-xl font-black tracking-tighter text-transparent sm:text-2xl"
          >
            HISOKA NOIR
          </span>

          <p
            class="mt-5 max-w-sm text-xs font-bold uppercase leading-relaxed tracking-wider text-on-surface-variant sm:mt-6"
          >
            Revolutionizing digital connection through premium live entertainment and high-fidelity interaction.
          </p>
        </div>

        <div>
          <h5 class="mb-5 text-[10px] font-black uppercase tracking-[0.2em] text-primary sm:mb-6">
            Platform
          </h5>

          <ul class="space-y-3 sm:space-y-4">
            <li>
              <a href="#" class="text-sm text-on-surface-variant transition-colors hover:text-on-surface">
                Streaming Guide
              </a>
            </li>
            <li>
              <a href="#" class="text-sm text-on-surface-variant transition-colors hover:text-on-surface">
                Partnership Program
              </a>
            </li>
            <li>
              <a href="#" class="text-sm text-on-surface-variant transition-colors hover:text-on-surface">
                Brand Assets
              </a>
            </li>
            <li>
              <a href="#" class="text-sm text-on-surface-variant transition-colors hover:text-on-surface">
                Developer API
              </a>
            </li>
          </ul>
        </div>

        <div>
          <h5 class="mb-5 text-[10px] font-black uppercase tracking-[0.2em] text-primary sm:mb-6">
            Legal
          </h5>

          <ul class="space-y-3 sm:space-y-4">
            <li>
              <a href="#" class="text-sm text-on-surface-variant transition-colors hover:text-on-surface">
                Privacy Policy
              </a>
            </li>
            <li>
              <a href="#" class="text-sm text-on-surface-variant transition-colors hover:text-on-surface">
                Terms of Service
              </a>
            </li>
            <li>
              <a href="#" class="text-sm text-on-surface-variant transition-colors hover:text-on-surface">
                Cookie Policy
              </a>
            </li>
            <li>
              <a href="#" class="text-sm text-on-surface-variant transition-colors hover:text-on-surface">
                Compliance
              </a>
            </li>
          </ul>
        </div>

        <div>
          <h5 class="mb-5 text-[10px] font-black uppercase tracking-[0.2em] text-primary sm:mb-6">
            Connect
          </h5>

          <div class="flex flex-wrap gap-3 sm:gap-4">
            <a
              href="#"
              class="flex h-10 w-10 items-center justify-center rounded-full bg-surface-container text-on-surface transition-colors hover:bg-primary/20"
            >
              <span class="material-symbols-outlined text-sm">public</span>
            </a>

            <a
              href="#"
              class="flex h-10 w-10 items-center justify-center rounded-full bg-surface-container text-on-surface transition-colors hover:bg-primary/20"
            >
              <span class="material-symbols-outlined text-sm">alternate_email</span>
            </a>

            <a
              href="#"
              class="flex h-10 w-10 items-center justify-center rounded-full bg-surface-container text-on-surface transition-colors hover:bg-primary/20"
            >
              <span class="material-symbols-outlined text-sm">share</span>
            </a>
          </div>
        </div>
      </div>

      <div
        class="mt-10 flex flex-col gap-4 border-t border-outline-variant/10 pt-6 sm:mt-14 sm:pt-8 lg:flex-row lg:items-center lg:justify-between"
      >
        <p class="text-[10px] font-bold uppercase tracking-widest text-zinc-500">
          © 2026 HISOKA NOIR ENTERTAINMENT. ALL RIGHTS RESERVED. YouCode
        </p>

        <div class="flex flex-col gap-2 sm:flex-row sm:flex-wrap sm:gap-6 lg:gap-8">
          <span class="text-[10px] font-bold uppercase tracking-widest text-zinc-600">
            Global Ops: Tokyo / London / NYC
          </span>
          <span class="text-[10px] font-bold uppercase tracking-widest text-zinc-600">
            Status: Operational
          </span>
        </div>
      </div>
    </div>
  </footer>
</template>

<script setup>
defineProps({
  collapsed: {
    type: Boolean,
    default: false,
  },
})
</script>

<style scoped>
.material-symbols-outlined {
  font-variation-settings:
    'FILL' 0,
    'wght' 400,
    'GRAD' 0,
    'opsz' 24;
}
</style>