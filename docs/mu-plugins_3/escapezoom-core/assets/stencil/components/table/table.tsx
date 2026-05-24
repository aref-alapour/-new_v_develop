import { Component, h, Prop, Host, Element } from '@stencil/core';

@Component({
  tag: 'ez-table',
  shadow: false,
})
export class EzTable {
  @Element() el: HTMLElement;
  @Prop() columns: string | any[] = [];
  @Prop() loading: boolean = false;
  @Prop() skeletonCount: number = 5;
  @Prop() caption: string;

  private getColumns(): string[] {
    if (typeof this.columns === 'string') {
      try {
        return JSON.parse(this.columns);
      } catch (e) {
        return [];
      }
    }
    return Array.isArray(this.columns) ? this.columns : [];
  }

  render() {
    const cols = this.getColumns();
    const colCount = cols.length;
    return (
      <Host class="block w-full">
        <div class="bg-white rounded-xl border border-[#E4EBF0] overflow-hidden">
          {this.caption && (
            <div class="px-6 py-4 border-b border-[#E4EBF0] bg-[#FAFDFF]">
              <h3 class="font-yekan-bold text-navyBlue">{this.caption}</h3>
            </div>
          )}
          <div class="overflow-x-auto">
            <div class="w-full table min-w-full">
              {colCount > 0 && (
                <div class="table-header-group bg-[#FAFDFF]">
                  <div class="table-row">
                    {cols.map((col: string) => (
                      <div class="table-cell px-6 py-4 text-right text-xs font-yekan-bold text-navyBlue whitespace-nowrap align-middle">
                        {col}
                      </div>
                    ))}
                  </div>
                </div>
              )}
              <div class="table-row-group bg-white">
                {this.loading ? (
                  Array.from({ length: this.skeletonCount }).map((_, rowIndex) => (
                    <div class="table-row border-b border-[#E4EBF0]" key={`skeleton-${rowIndex}`}>
                      {Array.from({ length: colCount || 5 }).map((_, colIndex) => (
                        <div class="table-cell px-6 py-4 align-middle" key={`skeleton-cell-${colIndex}`}>
                          <div class="h-4 bg-gray-100 rounded animate-pulse"></div>
                        </div>
                      ))}
                    </div>
                  ))
                ) : (
                  <slot />
                )}
              </div>
            </div>
          </div>
        </div>
      </Host>
    );
  }
}
