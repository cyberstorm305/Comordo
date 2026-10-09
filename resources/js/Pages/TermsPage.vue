<script setup>
import SiteNav from '../Components/SiteNav.vue'
import SiteFooter from '../Components/SiteFooter.vue'

// Structured template copy — have this reviewed by your legal adviser before launch.
const sections = [
  { t: 'Who we are', p: ['Comordo ("we", "us", "our") provides digital assurance products for UK schools and multi-academy trusts: ImageSafe, an image audit and safeguarding review workspace, and Compli, a website compliance workspace (launching soon).', 'Comordo is operated by Comordo Ltd, a company registered in England and Wales (company no. 1729746).'] },
  { t: 'The agreement', p: ['These terms govern your organisation\u2019s subscription to and use of Comordo products. By creating an account or using the services you agree to these terms on behalf of your organisation.'] },
  { t: 'Accounts and access', p: ['One Comordo account covers every product your organisation licenses. You are responsible for keeping credentials secure, for the actions taken under your users\u2019 accounts, and for ensuring access is limited to appropriate staff.'] },
  { t: 'Subscriptions, trials and billing', p: ['Licences are per school, billed monthly or yearly through Stripe. New accounts start with a 14-day free trial; no charge is made until the trial ends, and you may cancel at any time before then.', 'Schools may be added mid-term and are billed pro-rata. Volume discounts apply automatically as your school count crosses each band.'] },
  { t: 'Your data and content', p: ['Your organisation retains ownership of the data you provide, including reference records and review notes. We process it only to provide the services, as described in our Privacy Policy.', 'ImageSafe reference records are created and managed by your organisation and may include biometric data; you are responsible for having a lawful basis for that processing.'] },
  { id: 'website-scans', t: 'Website scans', p: [
    'ImageSafe scans read the publicly available pages and images of the websites you add. Scans do not log in, submit forms or change anything on your website. By starting a scan you confirm that your organisation is responsible for the website, or is authorised to have it scanned.',
    'To check how exposed your images are to AI collection, each scan also tests whether your website refuses known AI crawlers. It requests a small number of your images (currently up to three) while posing as AI crawlers, such as Common Crawl (CCBot), OpenAI (GPTBot) and Anthropic (ClaudeBot), and names ImageSafe in each request. These requests only read your images. Nothing collected this way is shared with those AI providers or used to train any AI model.',
    'Your web host, firewall or content delivery network may log these requests as AI crawler traffic, or block them, which is the behaviour the test checks for. You may want to let your web host know.',
  ] },
  { t: 'Acceptable use', p: ['The services support human review and must not be used for automated decision-making about individuals, surveillance unconnected to safeguarding assurance, or any unlawful purpose.'] },
  { t: 'Service and support', p: ['We aim to keep the services available at all times, with planned maintenance notified in advance. Support is available by email on working days.'] },
  { t: 'Liability', p: ['The services surface information to support professional judgement. Decisions about compliance positions and safeguarding actions rest with your staff, and we are not liable for decisions your organisation takes.'] },
  { t: 'Ending the agreement', p: ['You can cancel before renewal at any time from your account. On termination we will make your data available for export for 30 days, then delete it in line with our retention schedule.'] },
  { t: 'Changes and contact', p: ['We may update these terms with reasonable notice. Questions about them: hello@comordo.com.'] },
]
</script>

<template>
  <div class="min-h-screen">
    <SiteNav />
    <section class="border-b border-sand">
      <div class="container-site max-w-[880px] py-[clamp(48px,8vw,80px)] flex flex-col gap-10">
        <div class="flex flex-col gap-4">
          <div class="eyebrow">Legal</div>
          <h1 class="text-[clamp(30px,5vw,44px)] font-bold tracking-[-0.025em] m-0">Terms of Service</h1>
          <p class="font-mono text-xs text-mist m-0">Template copy · last updated October 2026 · review with your legal adviser before launch</p>
        </div>
        <div v-for="(s, i) in sections" :id="s.id" :key="s.t" class="flex flex-col gap-3 border-t border-sand pt-8 scroll-mt-24">
          <span class="font-mono text-xs text-copper">{{ String(i + 1).padStart(2, '0') }}</span>
          <h2 class="text-[clamp(20px,3vw,25px)] font-bold tracking-[-0.02em] m-0">{{ s.t }}</h2>
          <p v-for="(para, j) in s.p" :key="j" class="text-[15px] leading-relaxed text-slate m-0 text-pretty">{{ para }}</p>
        </div>
      </div>
    </section>
    <SiteFooter />
  </div>
</template>
