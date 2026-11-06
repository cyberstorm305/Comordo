<script setup>
// "Want to share or keep your preview?" Sends a confirmation to the email given on the form.
// The confirmation link returns here with ?lead=&verify=, which verifies it and removes them from the address.
import { onMounted, ref } from 'vue'
import { router } from '@inertiajs/vue3'
import { requestPreviewEmail, verifyPreviewEmail } from '@/usePreview.js'

const props = defineProps({
  id: { type: String, required: true },
  done: { type: Boolean, default: false }, // the scan has finished
})

const stage = ref('hidden') // hidden | offer | sending | sent | verifying | verified | invalid
const failure = ref(null) // limited | busy
const lead = ref(null)
const email = ref('')

const storageKey = () => `isp-preview-lead:${props.id}`
const forget = () => { try { sessionStorage.removeItem(storageKey()) } catch { /* storage blocked */ } }

async function send() {
  stage.value = 'sending'
  failure.value = null
  const res = await requestPreviewEmail(props.id, lead.value)
  if (res.status === 'verified') { forget(); stage.value = 'verified'; return }
  if (res.status === 'sent') { stage.value = 'sent'; return }
  stage.value = 'offer'
  failure.value = res.error
}

onMounted(async () => {
  const params = new URLSearchParams(window.location.search)
  const verifyLead = params.get('lead')
  const token = params.get('verify')

  if (verifyLead && token) {
    stage.value = 'verifying'
    const res = await verifyPreviewEmail(props.id, verifyLead, token)
    // Take the token out of the address, once Inertia has finished writing it back on load.
    router.replace({ url: window.location.pathname, preserveScroll: true, preserveState: true })
    if (res.status === 'verified') { forget(); stage.value = 'verified' }
    else if (res.error === 'expired') stage.value = 'hidden' // the page shows its own expired message
    else stage.value = 'invalid'
    return
  }

  // Only the person who ran this scan, in this tab, has a lead: someone given the link sees no banner.
  try {
    const saved = JSON.parse(sessionStorage.getItem(storageKey()) || 'null')
    if (saved?.lead) { lead.value = saved.lead; email.value = saved.email || ''; stage.value = 'offer' }
  } catch { /* storage blocked */ }
})
</script>

<template>
  <div v-if="stage === 'verifying'" class="mb-4 rounded-[14px] border border-sand bg-white px-5 py-4 text-[14px] leading-relaxed text-slate" role="status" data-testid="keep-verifying">Confirming your email…</div>

  <div v-else-if="stage === 'verified'" class="mb-4 flex items-start justify-between gap-3 rounded-[14px] border border-sand bg-white px-5 py-4 text-[14px] leading-relaxed text-slate" role="status" data-testid="keep-verified">
    <p class="m-0 min-w-0"><b class="text-navy">Email confirmed.</b> We've emailed you a link to this preview. It works for the next 24 hours, and you can share it with colleagues.</p>
    <button type="button" aria-label="Dismiss" class="-mr-1 flex h-[26px] w-[26px] flex-none cursor-pointer items-center justify-center rounded-[7px] border-0 bg-transparent p-0 text-stone hover:bg-tint-hover hover:text-navy" data-testid="keep-dismiss" @click="stage = 'hidden'">
      <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M5 5l10 10M15 5L5 15" /></svg>
    </button>
  </div>

  <div v-else-if="stage === 'invalid'" class="mb-4 rounded-[14px] border border-sand bg-white px-5 py-4 text-[14px] leading-relaxed text-slate" role="alert" data-testid="keep-invalid">
    <b class="text-navy">That confirmation link isn't valid any more.</b> Use the newest email we sent you, or <a href="/#preview" class="font-semibold text-copper">run a new preview</a>.
  </div>

  <div v-else-if="stage === 'sent'" class="mb-4 rounded-[14px] border border-sand bg-white px-5 py-4 text-[14px] leading-relaxed text-slate" role="status" data-testid="keep-sent">
    <b class="text-navy">Check your inbox.</b> We've sent a confirmation link to <b class="text-navy">{{ email }}</b>. Click it and we'll email you the link to this preview.
  </div>

  <div v-else-if="done && (stage === 'offer' || stage === 'sending')" class="mb-4 flex flex-wrap items-center justify-between gap-3 rounded-[14px] border border-sand bg-white px-5 py-4" data-testid="keep-offer">
    <div class="min-w-0 flex-1 basis-[320px] text-[14px] leading-relaxed text-slate">
      <b class="text-navy">Want to share or keep your preview?</b> Verify your email and we'll send you a link that works for 24 hours.
      <p v-if="failure" class="m-0 mt-1 text-bad" role="alert" data-testid="keep-failure">{{ failure === 'limited' ? "We've sent a few emails already. Please try again later." : "We couldn't send that just now. Please try again shortly." }}</p>
    </div>
    <button type="button" :disabled="stage === 'sending'" class="flex-none cursor-pointer rounded-[7px] border-0 bg-copper px-4 py-[9px] text-[13px] font-semibold text-white hover:bg-copper-dark disabled:cursor-progress" data-testid="keep-send" @click="send">{{ stage === 'sending' ? 'Sending…' : 'Verify my email' }}</button>
  </div>
</template>
