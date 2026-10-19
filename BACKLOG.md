# Backlog

## Joint Comordo marketing site for all products

The site currently markets ImageSafe only. `/` renders `ImageSafePage.vue` and the nav is anchor links to its sections. The multi-product pieces are kept, not deleted, so they can come back when Compli, OnlineRevison and future products are ready to market together.

What is parked:

- `resources/js/Pages/HomePage.vue`: the joint Comordo homepage (products overview, FAQs). Its route is commented out in `routes/web.php`.
- Product page links in the nav (`resources/js/Components/SiteNav.vue`), commented out above the anchor links.
- The Compli link in the footer (`resources/js/Components/SiteFooter.vue`), commented out.
- `resources/js/Pages/CompliPage.vue`: still reachable at `/compli`, but not linked from the nav or footer.

To bring the joint site back:

1. In `routes/web.php`, restore the `HomePage` route on `/` and give ImageSafe its own route on `/imagesafe` again (remove the redirect).
2. In `SiteNav.vue`, restore the commented-out product links and remove the ImageSafe anchor links, or move the anchors into an in-page nav on `ImageSafePage.vue`.
3. Restore the Compli link in `SiteFooter.vue` and point the ImageSafe link back at `/imagesafe`.
4. In `ImageSafePage.vue`, change the nav CTA back to `/imagesafe#preview`, and point the "Explore ImageSafe" link in `DemoPage.vue` back at `/imagesafe`.
5. Add OnlineRevison to the products section of `HomePage.vue` and to the nav.
