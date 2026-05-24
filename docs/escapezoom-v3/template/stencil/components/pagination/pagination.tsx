import { Component, h, Prop, Event, EventEmitter, Host } from '@stencil/core';

@Component({
  tag: 'ez-pagination',
  shadow: false,
})
export class EzPagination {
  @Prop() totalPages: number = 1;
  @Prop() currentPage: number = 1;
  @Event() pageChange: EventEmitter<number>;

  private handlePageClick(page: number) {
    if (page >= 1 && page <= this.totalPages && page !== this.currentPage) {
      this.pageChange.emit(page);
    }
  }

  render() {
    if (this.totalPages <= 1) return null;
    const pages: (number | string)[] = [];
    const delta = 2;
    for (let i = 1; i <= this.totalPages; i++) {
      if (
        i === 1 ||
        i === this.totalPages ||
        (i >= this.currentPage - delta && i <= this.currentPage + delta)
      ) {
        pages.push(i);
      } else if (pages[pages.length - 1] !== '...') {
        pages.push('...');
      }
    }
    return (
      <Host class="flex justify-center items-center gap-2 select-none">
        <button
          disabled={this.currentPage === 1}
          onClick={() => this.handlePageClick(this.currentPage - 1)}
          class="w-9 h-9 flex items-center justify-center rounded-lg border border-slate-105 bg-white hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed transition-colors text-navyBlue"
        >
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"></polyline></svg>
        </button>
        {pages.map((page) => (
          page === '...' ? (
            <span class="text-gray-400">...</span>
          ) : (
            <button
              onClick={() => this.handlePageClick(page as number)}
              class={{
                'w-9 h-9 flex items-center justify-center rounded-lg text-sm font-yekan-bold transition-all': true,
                'bg-primary-500 text-white shadow-lg shadow-primary-500/30': this.currentPage === page,
                'bg-white text-navyBlue border border-slate-105 hover:border-primary-500 hover:text-primary-500': this.currentPage !== page
              }}
            >
              {page}
            </button>
          )
        ))}
        <button
          disabled={this.currentPage === this.totalPages}
          onClick={() => this.handlePageClick(this.currentPage + 1)}
          class="w-9 h-9 flex items-center justify-center rounded-lg border border-slate-105 bg-white hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed transition-colors text-navyBlue"
        >
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"></polyline></svg>
        </button>
      </Host>
    );
  }
}
