<script setup>
import { ref } from 'vue'

const email = ref('')
const consent = ref(false)
const error = ref('')
const done = ref(false)
const processing = ref(false)

async function submit() {
  error.value = ''
  if (!/.+@.+\..+/.test(email.value.trim())) { error.value = 'Please enter a valid email address so we can reach you.'; return }
  if (!consent.value) { error.value = 'Please tick the box to confirm you’re happy for us to contact you.'; return }
  processing.value = true
  try {
    const res = await fetch('/subscribe.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
      body: JSON.stringify({ email: email.value.trim(), consent: consent.value }),
    })
    const data = await res.json().catch(() => ({}))
    if (res.ok && data.ok) {
      done.value = true
    } else {
      error.value = data.message || 'We couldn’t register that email right now. Please try again shortly.'
    }
  } catch {
    error.value = 'We couldn’t register that email right now. Please try again shortly.'
  } finally {
    processing.value = false
  }
}
</script>

<template>
  <div class="max-w-[560px] flex flex-col gap-3">
    <h2 class="text-[20px] font-bold tracking-[-0.02em] m-0">Stay in touch</h2>
    <p v-if="done" class="flex gap-2.5 items-baseline px-3.5 py-3 bg-[#E7F0EA] rounded-lg text-[13.5px] text-[#2F6149] m-0" role="status">Thanks — you’re on the list.</p>
    <form v-else class="flex flex-col gap-3" novalidate @submit.prevent="submit">
      <div class="flex flex-col sm:flex-row gap-3">
        <label class="flex-1 flex flex-col gap-1.5">
          <span class="sr-only">Email address</span>
          <input v-model="email" type="email" name="email" autocomplete="email" class="field" placeholder="you@yourschool.org.uk">
        </label>
        <button type="submit" class="btn-primary" :disabled="processing">Sign up</button>
      </div>
      <label class="flex gap-2.5 items-start text-[13px] text-slate leading-snug">
        <input v-model="consent" type="checkbox" name="consent" class="mt-0.5">
        <span>I agree that Comordo may store my details and contact me about its products and news, in line with the <a href="/privacy" class="text-copper">privacy policy</a>.</span>
      </label>
      <p v-if="error" class="flex gap-2.5 items-baseline px-3.5 py-3 bg-[#F4E2DC] rounded-lg text-[13.5px] text-[#9A3B2A] m-0" role="alert"><b>!</b> {{ error }}</p>
    </form>
  </div>
</template>
