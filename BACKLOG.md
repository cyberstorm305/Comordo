# Backlog

## Joint Comordo marketing site: done

The corporate site is built. The same app serves either site, chosen per deploy by the `SITE` env value (`imagesafe`, the default, or `comordo`). Each site has its own routes and SEO config, and the other site's routes return 404. See the README for running it locally and `docs/production-checklist.md` in the workspace repo for the comordo.com deploy.

How to add a product to the corporate site:

1. Add a product card to the "Our products" section of `resources/js/Pages/HomePage.vue`, linking to the product's overview page (the ImageSafe card is the pattern).
2. Add the overview page as a Vue page in `resources/js/Pages/` (copy `ImageSafeOverviewPage.vue`) and its route in `routes/web.php`, rendered only when `Site::is('comordo')`.
3. Add the page to `config/seo-comordo.php` under `pages` (title, description and `sitemap` priority), which also puts it in the sitemap.
4. Link it from the nav and footer (`SiteNav.vue`, `SiteFooter.vue`) and extend the feature and SEO tests (titles, canonical, sitemap, 404 on the ImageSafe site).

Follow-ups:

- `favicon` and `icon-512` are the ImageSafe tick-box mark and are also served on comordo.com. Give Comordo its own mark.
- `CompliPage.vue` is still reachable at `/compli` but not linked from either site. Link it from the corporate site when Compli is ready to market.
