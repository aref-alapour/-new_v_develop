export declare class EzBannerSlider {
    el: HTMLElement;
    items: string | any[];
    sliderModel: 'wide' | 'normal';
    _items: any[];
    private emblaApi;
    componentWillLoad(): void;
    componentDidLoad(): void;
    private parseItems;
    private initEmbla;
    private scrollPrev;
    render(): any;
}
