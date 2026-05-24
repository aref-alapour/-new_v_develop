# Theme escapezoom-v3

## Purpose

The **escapezoom-v3** theme is the front-end for EscapeZoom. It uses Vite, Stencil (Web Components, namespace `ez-components`), and Tailwind CSS. No jQuery; no prohibited libraries (see rule 04). Core business logic lives in the escapezoom-core mu-plugin, not in the theme.

## Folder structure

```
wp-content/themes/escapezoom-v3/
├── app/                    # Theme-only logic (hooks, Ajax handlers)
│   ├── init.php
│   ├── ajax/               # Optional: Ajax init
│   └── functions/          # Optional: theme helpers
├── assets/
│   ├── js/
│   ├── css/
│   │   ├── input.css       # Tailwind source
│   │   └── main.css        # Tailwind output (npm run css:build)
│   └── vendor/             # Third-party assets
├── template/               # Stencil components + theme PHP
│   └── components/         # ez-components (see docs/blocks-and-components.md)
├── www/
│   └── build/              # Vite + Stencil output (ez-components.js)
├── style.css               # Theme header
├── functions.php           # Registration and enqueue only
├── package.json
├── vite.config.js
├── stencil.config.ts
├── tailwind.config.js
└── postcss.config.cjs
```

## Build (mandatory)

- **Package manager:** npm.
- **Commands:**
  - `npm run dev` — Vite dev server (port 5174).
  - `npm run build` — Stencil build (Web Components → www/).
  - `npm run build:vite` — Vite production build (→ www/build/).
  - `npm run css:build` — Tailwind: input.css → main.css.
- **Output:** Vite → `www/build/`; Stencil → `www/build/ez-components.js` (and loader); Tailwind → `assets/css/main.css`.

## Stack (mandatory)

- **Vite** — dev server and production build.
- **Stencil** — Web Components, namespace `ez-components`, source in `template/`.
- **Tailwind CSS** — PostCSS, Autoprefixer; input.css → main.css.
- **Front-end:** Alpine.js, Embla Carousel (autoplay/fade), Flatpickr, htmx, jalaali-js.
- **Prohibited:** jQuery on front end; SweetAlert, Tippy, Zebline, Three.js (rule 04). Use GSAP for modals/tooltips/transitions when needed. Conditional loading for heavy scripts (Leaflet, Chart.js).

## Activation

1. Run `npm install` in the theme root.
2. Run `npm run css:build` (and optionally `npm run build` for Stencil) so main.css and ez-components.js exist.
3. In WordPress Admin → Appearance → Themes, activate **EscapeZoom v3**.

Documentation for the mu-plugin: `docs/escapezoom-core.md`.

## One-time setup

1. Run `npm install` in the theme root.
2. Run `npm run css:build` to generate `assets/css/main.css`.
3. Run `npm run build` (Stencil) to generate `www/build/ez-components.js`.
4. Activate the theme in WordPress. Enqueues are conditional on built files existing.
