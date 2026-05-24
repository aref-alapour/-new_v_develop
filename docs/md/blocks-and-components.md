# Blocks and Components (EscapeZoom v3)

## Stencil components (ez-components)

All components live under `wp-content/themes/escapezoom-v3/template/components/`. Build output: `www/build/ez-components.js` (and loader). Namespace: **ez-components**.

| Component | Tag | Notes |
|-----------|-----|--------|
| accordion | `ez-accordion` | Container for accordion items |
| accordion-item | `ez-accordion-item` | Single expandable item |
| autocomplete | `ez-autocomplete` | Uses autocomplete.css |
| badge | `ez-badge` | |
| banner-slider | `ez-banner-slider` | Embla-based banner/carousel |
| blog-card | `ez-blog-card` | TSX + blog-card.php (slot="image") |
| brand-card | `ez-brand-card` | |
| breadcrumb | `ez-breadcrumb` | |
| button | `ez-button` | |
| collection-card | `ez-collection-card` | |
| comments | comment-item-product, comment-item-post | render-comments.php; Ajax in app/ajax/comments.php |
| dropdown | `ez-dropdown` | Uses @Listen('optionSelect') from ez-dropdown-item |
| dropdown-item | `ez-dropdown-item` | Emits optionSelect { value, text } |
| loading | `ez-loading` | |
| modal | `ez-modal` | |
| pagination | `ez-pagination` | |
| product-card | `ez-product-card` | TSX + product-card.php; caller passes home_url |
| range-datepicker | `ez-range-datepicker` | TSX, CSS, holidays/*.json |
| sans-list | `ez-sans-list` | apiEndpoint default ''; passes session to items |
| sans-item | `ez-sans-item` | Accepts session as object or JSON string |
| select | `ez-select` | TSX + select.css |
| table | `ez-table` | |
| table-row | `ez-table-row` | |
| table-cell | `ez-table-cell` | |
| tabs | `ez-tabs` | activeTab attribute |
| tab | `ez-tab` | |
| text-input | `ez-text-input` | input.tsx |

## Gutenberg blocks (implemented)

All Stencil components have a **corresponding Gutenberg block** in the category **EscapeZoom**. Registration lives in the mu-plugin **escapezoom-core** (`src/Blocks/`); rendering uses either a **theme template part** (for blocks that call `ez_render_*`) or a **dynamic tag** output by the plugin.

### Block registration (escapezoom-core)

- **Category:** Registered via `BlockCategoryRegistrar` (slug: `escapezoom`, title: EscapeZoom).
- **Definitions:** One folder per block under `src/Blocks/block-definitions/{block-slug}/` with `block.json` (name: `escapezoom/ez-*`, category: `escapezoom`, attributes as needed).
- **Bootstrap:** `BlocksBootstrap::boot()` registers the category, enqueues theme `www/build/ez-components.js` and `assets/css/main.css` in the block editor (when active theme is escapezoom-v3), and registers all blocks from metadata with a `render_callback` from `BlockRenderResolver`.
- **Render:** Blocks that use theme PHP (`ez-product-card`, `ez-blog-card`, `ez-comments`) are rendered via `get_template_part('template-parts/blocks/' . $shortName)` in the theme. All other blocks output the Stencil custom element tag with allowed attributes.

### Theme template parts (escapezoom-v3)

- `template-parts/blocks/ez-product-card.php` — loads product-card.php, calls `ez_render_product_card($product, home_url())`; product from block attribute `productJson` (JSON) or `productId` (if `ez_get_product_for_block` exists).
- `template-parts/blocks/ez-blog-card.php` — loads blog-card.php, calls `ez_render_blog_card($post)`; post from `postId` or from block attributes.
- `template-parts/blocks/ez-comments.php` — loads render-comments.php; shows comments for `postId` or latest comments (attributes: `postId`, `postType`, `limit`).

### How to add a new block (mandatory for new components)

1. **Add the Stencil component** in the theme under `template/components/` and build (`npm run build`).
2. **Add block metadata** in the plugin: create `src/Blocks/block-definitions/ez-{component}/block.json` with `name: "escapezoom/ez-{component}"`, `category: "escapezoom"`, and any attributes.
3. **Render:**  
   - If the component has a theme PHP render helper (e.g. `ez_render_*`), add it to `BlockRenderResolver::THEME_TEMPLATE_BLOCKS` and create `template-parts/blocks/ez-{component}.php` in the theme that loads the helper and outputs the markup.  
   - Otherwise the default callback will output the Stencil tag; add allowed attributes for the tag in `BlockRenderResolver::allowedAttributesForTag()` if needed.
4. No need to touch `BlocksBootstrap::getBlockSlugs()` — it auto-discovers blocks by scanning `block-definitions/` for directories containing `block.json`.

### List of blocks (one per component)

| Block (escapezoom/ez-*) | Stencil tag | Render |
|-------------------------|-------------|--------|
| ez-accordion, ez-accordion-item | ez-accordion, ez-accordion-item | Tag |
| ez-autocomplete | ez-autocomplete | Tag |
| ez-badge | ez-badge | Tag |
| ez-banner-slider | ez-banner-slider | Tag |
| ez-blog-card | ez-blog-card | Theme template part |
| ez-brand-card | ez-brand-card | Tag |
| ez-breadcrumb | ez-breadcrumb | Tag |
| ez-button | ez-button | Tag |
| ez-collection-card | ez-collection-card | Tag |
| ez-comments | — | Theme template part (comment items) |
| ez-dropdown, ez-dropdown-item | ez-dropdown, ez-dropdown-item | Tag |
| ez-loading | ez-loading | Tag |
| ez-modal | ez-modal | Tag |
| ez-pagination | ez-pagination | Tag |
| ez-product-card | ez-product-card | Theme template part |
| ez-range-datepicker | ez-range-datepicker | Tag |
| ez-sans-list, ez-sans-item | ez-sans-list, ez-sans-item | Tag |
| ez-select | ez-select | Tag |
| ez-table, ez-table-row, ez-table-cell | ez-table, ez-table-row, ez-table-cell | Tag |
| ez-tabs, ez-tab | ez-tabs, ez-tab | Tag |
| ez-text-input | ez-text-input | Tag |

### Future section blocks (optional)

Higher-level section blocks (e.g. Hero Banner Slider, Game Finder, Product Slider by City) can be added later; each would compose the above component blocks and/or theme template parts.

## Home page (planned)

The home page will be buildable **only with Gutenberg**: a "Home Page" block pattern or template containing the above blocks in order. Template `home.php` will output only `the_content()` (block content).

## SEO

Every block must output **semantic, server-rendered HTML** (headings, links, images with alt). Stencil enhances interactivity; critical content is never only in client-side JS.
