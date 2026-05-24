export declare class EzSansList {
    productId: string | number;
    /** API endpoint for fetching sessions (e.g. admin-ajax.php?action=ez_sanses or custom). */
    apiEndpoint: string;
    dates: any[];
    selectedDate: string;
    sessions: any[];
    loading: boolean;
    error: string;
    componentWillLoad(): void;
    fetchData: () => Promise<void>;
    mockData(): void;
    handleDateSelect(dateObj: any): void;
    render(): any;
}
