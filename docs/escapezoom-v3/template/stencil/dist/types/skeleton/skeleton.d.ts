/**
 * EZ Skeleton - Placeholder loader for content that's being fetched.
 * Use as a wrapper or with predefined type props for common patterns.
 */
export declare class EzSkeleton {
    /** Predefined skeleton type: card, list, text, avatar */
    type: 'card' | 'list' | 'text' | 'avatar' | 'custom';
    /** Number of items to show (for list/card types) */
    count: number;
    /** Show animation pulse effect */
    animate: boolean;
    renderCard(): any;
    renderList(): any;
    renderText(): any;
    renderAvatar(): any;
    render(): any;
}
