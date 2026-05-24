export declare class EzLoading {
    type: 'spinner' | 'dots' | 'circle';
    size: 'sm' | 'md' | 'lg' | 'xl';
    message?: string;
    color: string;
    private getSizeClass;
    renderSpinner(): any;
    renderDots(): any;
    renderCircle(): any;
    render(): any;
}
