export declare class EzProductCard {
    productId?: string | number;
    status?: string;
    href?: string;
    isSlide?: boolean;
    /** عنوان محصول (از سرور؛ در HTML به‌صورت product-title پاس داده می‌شود تا با titleٔ رزروشده تداخل نداشته باشد). */
    productTitle?: string;
    price?: string;
    imageUrl?: string;
    address?: string;
    render(): any;
}
