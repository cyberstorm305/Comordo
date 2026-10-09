<script setup>
import SiteNav from '../Components/SiteNav.vue'
import SiteFooter from '../Components/SiteFooter.vue'

// Structured template copy — have this reviewed by your DPO / legal adviser before launch.
const sections = [
  { t: 'Who we are', p: ['Comordo Ltd (company no. 1729746, England and Wales) provides digital assurance products for UK schools and multi-academy trusts. This policy explains how we handle personal data across our website, ImageSafe and, at launch, Compli.'] },
  { t: 'What we collect', p: ['Account data: names, work email addresses, organisation details and login credentials for the staff who use Comordo.', 'Service data: the content our products process on your behalf — website content and documents (Compli), published imagery, detected faces and the authorised reference records your organisation manages (ImageSafe), plus review notes, statuses and audit history.', 'Billing data: subscription and invoicing records, processed by Stripe. We never store full card details.'] },
  { t: 'Biometric and children\u2019s data', p: ['ImageSafe can compare detected faces against reference records your organisation creates and controls. This may constitute biometric data relating to children. Your organisation is the data controller for this processing and is responsible for its lawful basis and any required assessments; Comordo processes it only on your documented instructions, as a processor.'] },
  { t: 'How we use data', p: ['To provide and secure the services, surface observations for staff review, maintain audit histories, manage subscriptions and respond to support requests. Automation surfaces information; decisions are always made by your staff.'] },
  { t: 'Sharing', p: ['We share data only with the sub-processors needed to run the services (hosting, email, payments via Stripe), each under contract. We do not sell personal data.'] },
  { t: 'Retention', p: ['Service data is retained while your subscription is active and made available for export for 30 days after termination, then deleted. Audit histories follow the same schedule unless you ask us to remove them sooner.'] },
  { t: 'Your rights', p: ['Individuals have the usual UK GDPR rights — access, rectification, erasure, restriction, portability and objection. Requests about school-controlled data should go to the school or trust; we support controllers in responding.'] },
  { t: 'Security', p: ['Access to service data is role-based and logged. We review access, encryption and infrastructure controls regularly, and we\u2019re happy to walk data protection teams through the detail on a call.'] },
  { t: 'Contact', p: ['Privacy questions: hello@comordo.com. You can also complain to the ICO, though we\u2019d welcome the chance to resolve concerns first.'] },
]
</script>

<template>
  <div class="min-h-screen">
    <SiteNav />
    <section class="border-b border-sand">
      <div class="container-site max-w-[880px] py-[clamp(48px,8vw,80px)] flex flex-col gap-10">
        <div class="flex flex-col gap-4">
          <div class="eyebrow">Legal</div>
          <h1 class="text-[clamp(30px,5vw,44px)] font-bold tracking-[-0.025em] m-0">Privacy Policy</h1>
          <p class="font-mono text-xs text-mist m-0">Template copy · last updated August 2026 · review with your DPO before launch</p>
        </div>
        <div v-for="(s, i) in sections" :key="s.t" class="flex flex-col gap-3 border-t border-sand pt-8">
          <span class="font-mono text-xs text-copper">{{ String(i + 1).padStart(2, '0') }}</span>
          <h2 class="text-[clamp(20px,3vw,25px)] font-bold tracking-[-0.02em] m-0">{{ s.t }}</h2>
          <p v-for="(para, j) in s.p" :key="j" class="text-[15px] leading-relaxed text-slate m-0 text-pretty">{{ para }}</p>
        </div>
      </div>
    </section>
    <SiteFooter />
  </div>
</template>
