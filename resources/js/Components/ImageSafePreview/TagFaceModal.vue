<!--
  TagFaceModal.vue — "Tag person" (the real app's TagFaceModal), for trying
  tagging in a preview. Pick a demo record, add a new person or mark the face
  unknown; the page holds the tags in memory and nothing is saved. Esc and
  the backdrop close it; focus moves in, stays in, and returns to whatever
  opened it.
-->
<script setup>
import { computed, nextTick, onBeforeUnmount, reactive, ref, watch } from 'vue'
import { appReview } from './appReview.js'

const props = defineProps({
  open: { type: Boolean, default: false },
  faceLabel: { type: String, default: 'This face' },
  current: { type: Object, default: null }, // the person the face is tagged as
  people: { type: Array, default: () => [] },
  left: { type: Number, default: 0 }, // demo tags the visitor has left
  returnTo: { type: null, default: null }, // the element that opened it
})
const emit = defineEmits(['assign', 'create', 'close'])

const search = ref('')
const creating = ref(false)
const form = reactive({ name: '', permission: 'granted' })
const filtered = computed(() => {
  const q = search.value.trim().toLowerCase()
  return props.people.filter((p) => !q || p.name.toLowerCase().includes(q))
})
// The app's permission pill for a record, as its own tag pop-up shows it.
const pill = (p) => appReview.value.permissionMeta({ known_person: { name: p.name, permission_status: p.permission } })
const initials = (name) => name.split(/\s+/).filter(Boolean).slice(0, 2).map((w) => w[0].toUpperCase()).join('')

function create() {
  const name = form.name.trim()
  if (!name) return
  emit('create', { name, permission: form.permission })
}

const panel = ref(null)
const searchField = ref(null)
let opener = null

watch(() => props.open, async (o, was) => {
  if (o && !was) {
    search.value = ''
    creating.value = false
    form.name = ''
    form.permission = 'granted'
    opener = props.returnTo || document.activeElement
    document.addEventListener('keydown', onKey)
    await nextTick()
    searchField.value?.focus()
  } else if (!o && was) {
    document.removeEventListener('keydown', onKey)
    opener?.focus?.()
    opener = null
  }
})
onBeforeUnmount(() => document.removeEventListener('keydown', onKey))

function onKey(e) {
  if (e.key === 'Escape') { e.preventDefault(); emit('close'); return }
  if (e.key !== 'Tab' || !panel.value) return
  const els = [...panel.value.querySelectorAll('a[href], button, input, select')].filter((el) => !el.disabled)
  if (!els.length) return
  const first = els[0]
  const last = els[els.length - 1]
  if (e.shiftKey && document.activeElement === first) { e.preventDefault(); last.focus() }
  else if (!e.shiftKey && document.activeElement === last) { e.preventDefault(); first.focus() }
  else if (!panel.value.contains(document.activeElement)) { e.preventDefault(); first.focus() }
}
</script>

<template>
  <Teleport to="body">
    <Transition enter-active-class="ease-out duration-200" enter-from-class="opacity-0" leave-active-class="ease-in duration-150" leave-to-class="opacity-0">
      <div v-if="open" class="isp fixed inset-0 z-50 flex items-center justify-center !bg-transparent px-4 py-6" data-testid="tag-modal">
        <div class="absolute inset-0 bg-navy/[.42]" @click="emit('close')"></div>
        <div ref="panel" role="dialog" aria-modal="true" aria-labelledby="isp-tag-title"
             class="relative flex max-h-[86vh] w-full max-w-md flex-col overflow-hidden rounded-[14px] bg-white shadow-isp-modal">
          <div class="flex items-center justify-between border-b border-shell px-5 py-5">
            <div>
              <div id="isp-tag-title" class="text-[15px] font-bold leading-[22px] text-navy">Tag person</div>
              <div class="mt-px text-[12px] leading-[18px] text-stone">{{ faceLabel }} · assign a person from records</div>
            </div>
            <button type="button" aria-label="Close" class="h-[30px] w-[30px] flex-none cursor-pointer rounded-[7px] border-0 bg-shell text-[14px] text-slate hover:bg-sand" @click="emit('close')">✕</button>
          </div>

          <div class="border-b border-shell bg-copper-wash px-5 py-2.5 text-[12px] leading-[18px] text-copper-dark" data-testid="tag-demo-note">
            A demo with made-up records. Tags change this preview only and aren't saved.
            <b class="font-semibold">{{ left }} {{ left === 1 ? 'tag' : 'tags' }} left.</b>
          </div>

          <div v-if="current" class="flex items-center justify-between gap-3 border-b border-shell bg-paper px-5 py-2.5 text-[12px] leading-[18px]">
            <span class="min-w-0 truncate text-slate">Tagged as <b class="text-navy">{{ current.name }}</b></span>
          </div>

          <div class="px-5 pb-2 pt-4">
            <div class="relative">
              <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-stone" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.9" aria-hidden="true"><circle cx="8.5" cy="8.5" r="5.75" /><path d="M13 13l4.25 4.25" stroke-linecap="round" /></svg>
              <input ref="searchField" v-model="search" type="text" placeholder="Search name…" aria-label="Search name"
                     class="block w-full rounded-[7px] border border-line bg-white py-2 pl-9 pr-3 text-[14px] leading-5 text-navy outline-none placeholder:text-stone focus:border-copper">
            </div>
          </div>

          <div class="min-h-[120px] flex-1 overflow-y-auto px-3 pb-3 pt-1">
            <button v-for="p in filtered" :key="p.id" type="button"
                    class="flex w-full cursor-pointer items-center gap-2.5 rounded-[7px] border-0 px-2 py-2.5 text-left hover:bg-copper-wash"
                    :class="p.id === current?.id ? 'bg-copper-wash' : 'bg-transparent'"
                    data-testid="tag-person"
                    @click="emit('assign', p)">
              <div class="flex h-[34px] w-[34px] flex-none items-center justify-center rounded-full border border-edge bg-shell text-[11px] font-bold text-slate">{{ initials(p.name) }}</div>
              <div class="min-w-0 flex-1">
                <div class="truncate text-[13px] font-semibold leading-5 text-navy">{{ p.name }}</div>
                <div class="text-[11px] leading-4 text-stone">{{ [p.id === current?.id ? 'Currently tagged' : null, p.detail].filter(Boolean).join(' · ') }}</div>
              </div>
              <span class="flex-none whitespace-nowrap rounded-[10px] px-2 py-0.5 text-[10px] font-semibold leading-[14px]" :style="{ background: pill(p).bg, color: pill(p).fg }">{{ pill(p).label }}</span>
            </button>
            <p v-if="!filtered.length" class="m-0 px-2 py-4 text-center text-[13px] text-stone">No people match "{{ search }}".</p>
          </div>

          <div class="border-t border-shell px-5 py-3">
            <button v-if="!creating" type="button" class="cursor-pointer border-0 bg-transparent p-0 text-[12px] font-semibold text-copper-dark hover:underline" @click="creating = true">
              + Add a new person from this face
            </button>
            <form v-else class="flex flex-col gap-2" @submit.prevent="create">
              <div class="flex gap-2">
                <input v-model="form.name" type="text" placeholder="Person name" aria-label="Person name" required
                       class="block min-w-0 flex-1 rounded-[7px] border border-line bg-white px-3 py-2 text-[14px] leading-5 text-navy outline-none placeholder:text-stone focus:border-copper">
                <select v-model="form.permission" aria-label="Permission"
                        class="block w-[150px] rounded-[7px] border border-line bg-white px-3 py-2 text-[14px] leading-5 text-navy outline-none focus:border-copper">
                  <option value="granted">Approved</option>
                  <option value="pending">No data yet</option>
                  <option value="denied">Refused</option>
                </select>
              </div>
              <div class="flex items-center justify-end gap-2">
                <button type="button" class="cursor-pointer rounded-[7px] border border-line bg-white px-3 py-1.5 text-[12px] font-semibold text-slate hover:bg-shell" @click="creating = false">Back</button>
                <button type="submit" class="cursor-pointer rounded-[7px] border-0 bg-navy px-3 py-1.5 text-[12px] font-semibold text-white hover:bg-navy-light">Add person</button>
              </div>
            </form>
          </div>

          <div class="flex justify-between gap-2.5 border-t border-shell px-5 py-3.5">
            <button type="button" class="cursor-pointer rounded-[7px] border border-line bg-white px-3.5 py-2 text-[13px] font-semibold text-plum hover:bg-shell" @click="emit('assign', null)">
              {{ current ? 'Remove tag' : 'Mark as unknown person' }}
            </button>
            <button type="button" class="cursor-pointer rounded-[7px] border border-line bg-white px-4 py-2 text-[13px] font-semibold text-slate hover:bg-shell" @click="emit('close')">Cancel</button>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>
