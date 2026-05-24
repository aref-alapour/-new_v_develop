/**
 * EZ Brand Card — entire card is one link (`shadow: false`; styles from global Tailwind/main.css).
 * SSR uses a real `<a class="ez-brand-card">` in `brand-card.php` so directories work without hydration.
 *
 * Slots (inside the main link):
 *   - `media` — logo / placeholder
 *   - `badge` — optional score (position absolute in slotted markup)
 *   - `title-row` — title + optional meta (no nested links)
 *   - `details` — e.g. address
 * Slot `actions` is **outside** the link (valid HTML if you need buttons).
 */
export declare class EzBrandCard {
    href: string;
    brandId?: number;
    brandSlug?: string;
    render(): any;
}
