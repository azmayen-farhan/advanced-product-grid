# Advanced Product Grid

A custom Elementor widget for WooCommerce, built to cover what an older WoodMart build is missing: a product grid with a sorting dropdown, variation swatches, a hover-to-swap gallery image, sale/sold-out badges, and an "Order Now" button that links straight to the product page.

## Requirements

- WordPress with Elementor active (free version is enough)
- WooCommerce active
- PHP 7.4+

## Install

1. In WP Admin go to **Plugins → Add New → Upload Plugin**.
2. Upload `advanced-product-grid.zip` and activate it.
3. Edit any page with Elementor, search the widget panel for **"Product Grid"** — it lives under the **Advanced Widgets** category.
4. Drag it onto the page.

## What each setting does

**Query**
- Products Per Page / Columns (responsive — separate values for desktop, tablet, mobile)
- Categories — leave empty for all categories
- Default Sorting — what shows before a visitor changes the dropdown
- On-Sale Products Only

**Sorting Bar**
- Show/hide the sorting dropdown and a "X products found" count

**Product Card**
- Toggle the sale badge, sold-out badge, variation swatches, hover image swap
- Image aspect ratio
- Button text (default "Order Now")

**Style tab** — colors, typography and spacing for the grid, badges, title/price, swatches, and button, so you can match your WoodMart accent color exactly.

## Notes on a few decisions I made

- **Sorting** reloads the page with `?apg_orderby=...` in the URL, the same approach WooCommerce's own default sorting dropdown uses, rather than AJAX filtering. Say the word if you'd rather it filter in place without a reload.
- **Sold-out products still show an "Order Now" button** linking to the product page, rather than being hidden or disabled — your screenshot's small "X" icon on the sold-out card wasn't clear enough for me to replicate exactly, so I kept it simple. Easy to change either way.
- **Variation swatches** come from the product's variation attributes (e.g. Size). Sizes that are out of stock across all variations show greyed out and struck through, and aren't clickable. Clicking an in-stock size takes you to the product page with that value pre-selected in the URL, which WooCommerce reads automatically.
- **Sale badge %** is calculated from the lowest regular price vs. lowest active price — works for both simple and variable products.
- The small heart/lightning icon on one card in your reference screenshot isn't included since it wasn't clear what it represents (featured flag? flash sale? wishlist?). Happy to add it once you tell me what it should do.
- The widget always shows out-of-stock products with a "Sold Out" badge rather than hiding them — this intentionally ignores your WooCommerce "hide out of stock items from the catalog" setting if you have it on, since hiding them would make the sold-out badge pointless. Let me know if you'd rather it respect that setting.

## A note on testing

I don't have a live WordPress + WooCommerce + Elementor + WoodMart environment to test this against directly. I've syntax-checked all the PHP and JS, and load-tested the widget class against mock WordPress/WooCommerce/Elementor objects to catch typos or bad function calls — but please try it on a staging copy first, the way you would with any new plugin.

## Changelog

**1.0.0** — Initial release.
