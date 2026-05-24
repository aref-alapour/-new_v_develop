import { EventEmitter } from '../stencil-public-runtime';
export declare class EzPagination {
    totalPages: number;
    currentPage: number;
    pageChange: EventEmitter<number>;
    private handlePageClick;
    render(): any;
}
