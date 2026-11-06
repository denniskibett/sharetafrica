{{-- Verification photos: ID front/back (or passport page) and three camera selfies verify the account; the signature
     is for bank grade. Photos are checked for size, light and sharpness before upload, and stored privately. --}}
<x-ui.card title="ID and selfies" subtitle="Your ID (front and back) and three selfies verify your account. We read your name, ID number and date of birth from the ID: nothing to type. All four corners in frame, no glare.">
  <x-slot:actions>
    <span :class="sharet.pill(data.level.basic?.photos?.[0] === data.level.basic?.photos?.[1] ? 'success' : 'warning')"
          x-text="(data.level.basic?.photos?.[0] ?? 0) + ' of ' + (data.level.basic?.photos?.[1] ?? 0) + ' photos'"></span>
  </x-slot:actions>

  <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
    <template x-for="d in data.documents" :key="d.type">
      <div class="flex flex-col rounded-xl border p-4" :class="d.uploaded ? 'border-emerald-200 dark:border-emerald-500/30' : 'border-gray-200 dark:border-gray-800'">
        <div class="flex items-start justify-between gap-3">
          <div>
            <p class="font-medium text-gray-900 dark:text-white" x-text="d.label"></p>
            <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400" x-text="d.hint"></p>
          </div>
          <span :class="sharet.pill(d.uploaded ? 'success' : (d.verify ? 'warning' : 'neutral'))" x-text="d.uploaded ? 'Done' : (d.verify ? 'Needed' : 'Bank grade')"></span>
        </div>

        <div class="mt-3 flex aspect-[4/3] items-center justify-center overflow-hidden rounded-lg bg-gray-50 dark:bg-white/[0.03]">
          <img x-show="d.url" :src="d.url" :alt="d.label" class="h-full w-full object-contain">
          <x-heroicon-o-photo x-show="!d.url && d.kind === 'photo'" class="h-10 w-10 text-gray-300 dark:text-gray-600" />
          <x-heroicon-o-face-smile x-show="!d.url && d.kind === 'selfie'" class="h-10 w-10 text-gray-300 dark:text-gray-600" />
          <x-heroicon-o-pencil x-show="!d.url && d.kind === 'signature'" class="h-10 w-10 text-gray-300 dark:text-gray-600" />
        </div>

        <p x-show="docError[d.type]" x-text="docError[d.type]" class="mt-2 text-xs text-red-600 dark:text-red-400"></p>
        <p x-show="d.at" class="mt-2 text-xs text-gray-500">Added <span x-text="d.at"></span></p>

        <div class="mt-auto flex flex-wrap gap-2 pt-3">
          <template x-if="d.kind === 'photo'">
            <label class="inline-flex h-10 cursor-pointer items-center gap-2 rounded-lg bg-gray-900 px-4 text-sm font-medium text-white hover:bg-gray-800 dark:bg-[#D4FF3D] dark:text-gray-900">
              <x-heroicon-o-camera class="h-5 w-5" /> <span x-text="d.uploaded ? 'Replace' : 'Take or upload'"></span>
              <input type="file" accept="image/jpeg,image/png,image/webp" capture="environment" class="sr-only" @change="uploadDoc(d.type, $event.target.files[0]); $event.target.value = ''">
            </label>
          </template>
          <template x-if="d.kind === 'selfie'">
            <x-ui.button icon="video-camera" @click="sharet.panel('selfie', d)" x-text="d.uploaded ? 'Retake' : 'Open camera'"></x-ui.button>
          </template>
          <template x-if="d.kind === 'signature'">
            <x-ui.button icon="pencil" @click="sharet.panel('signature', d)" x-text="d.uploaded ? 'Sign again' : 'Sign'"></x-ui.button>
          </template>
        </div>
      </div>
    </template>
  </div>
</x-ui.card>

{{-- Selfie camera (front camera, face guide, clarity check) --}}
<x-ui.slide-over name="selfie" title="Selfie" x-text="payload.label">
  <div x-data="kycCamera()" x-effect="if (open) { $nextTick(() => start()) } else { stop() }" class="space-y-4">
    <p class="text-sm text-gray-600 dark:text-gray-300" x-text="payload.hint"></p>
    <div class="relative mx-auto aspect-[3/4] max-w-sm overflow-hidden rounded-2xl bg-black">
      <video x-ref="video" x-show="!shot" playsinline muted class="h-full w-full -scale-x-100 object-cover"></video>
      <img x-show="shot" :src="shot" alt="Your selfie" class="h-full w-full object-cover">
      <div x-show="!shot" class="pointer-events-none absolute inset-[12%_18%] rounded-[50%] border-4 border-dashed border-[#D4FF3D]/80"></div>
      <p x-show="!shot" class="absolute inset-x-0 bottom-3 text-center text-xs font-medium text-white/90"
         x-text="payload.type === 'selfie_left' ? 'Turn your head to the left' : payload.type === 'selfie_right' ? 'Turn your head to the right' : 'Face fills the oval, eyes open'"></p>
    </div>
    <p x-show="error" x-text="error" class="rounded-lg bg-red-50 px-3 py-2 text-sm text-red-700 dark:bg-red-500/10 dark:text-red-300"></p>
    <div class="flex flex-wrap justify-center gap-2">
      <x-ui.button x-show="!shot && stream" icon="camera" @click="capture()">Take photo</x-ui.button>
      <x-ui.button x-show="shot" variant="secondary" icon="arrow-path" @click="start()">Retake</x-ui.button>
      <x-ui.button x-show="shot" icon="check" @click="uploadDoc(payload.type, shot).then(ok => ok && close())">Use this photo</x-ui.button>
      <label x-show="error" class="inline-flex h-11 cursor-pointer items-center gap-2 rounded-full bg-gray-100 px-5 text-sm font-semibold text-gray-800 hover:bg-gray-200 dark:bg-white/[0.06] dark:text-gray-200">
        <x-heroicon-o-arrow-up-tray class="h-5 w-5" /> Upload instead
        <input type="file" accept="image/jpeg,image/png" capture="user" class="sr-only" @change="uploadDoc(payload.type, $event.target.files[0], { minWidth: 640, minHeight: 480 }).then(ok => ok && close())">
      </label>
    </div>
    <p x-show="docError[payload.type]" x-text="docError[payload.type]" class="text-center text-sm text-red-600"></p>
  </div>
</x-ui.slide-over>

{{-- Signature pad --}}
<x-ui.slide-over name="signature" title="Your signature">
  <div x-data="signaturePad()" x-effect="open && $nextTick(() => { init(); clear() })" class="space-y-4">
    <p class="text-sm text-gray-600 dark:text-gray-300">Sign with your finger or mouse, the way you sign official documents.</p>
    <canvas x-ref="pad" @pointerdown="down($event)" @pointermove="move($event)" @pointerup="up()" @pointerleave="up()"
            class="h-56 w-full touch-none rounded-2xl border-2 border-dashed border-gray-300 bg-white dark:border-gray-700" aria-label="Signature pad"></canvas>
    <div class="flex justify-between gap-2">
      <x-ui.button variant="secondary" icon="arrow-path" @click="clear()">Clear</x-ui.button>
      <x-ui.button icon="check" x-bind:disabled="!signed" @click="uploadDoc('signature', image()).then(ok => ok && close())">Save signature</x-ui.button>
    </div>
    <p x-show="docError.signature" x-text="docError.signature" class="text-sm text-red-600"></p>
  </div>
</x-ui.slide-over>
