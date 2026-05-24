export declare class EzCollectionCard {
    collectionTitle: string;
    likes: number;
    link: string;
    images: string | string[];
    _images: string[];
    componentWillLoad(): void;
    parseImages(newValue: string | string[]): void;
    render(): any;
}
