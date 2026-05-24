import { Component, Prop, State, h, Watch } from '@stencil/core';

@Component({
  tag: 'ez-breadcrumb',
  shadow: false,
})
export class EzBreadcrumb {
  @Prop() items: string | any[] = [];

  @State() _items: any[] = [];

  componentWillLoad() {
    this.parseItems(this.items);
  }

  @Watch('items')
  parseItems(newValue: string | any[]) {
    if (typeof newValue === 'string') {
      try {
        this._items = JSON.parse(newValue);
      } catch (e) {
        this._items = [];
      }
    } else {
      this._items = Array.isArray(newValue) ? newValue : [];
    }
  }

  render() {
    if (this._items.length === 0) return null;
    return (
      <nav class="flex" aria-label="Breadcrumb">
        <ol class="inline-flex items-center">
          {this._items.map((item: any, index: number) => (
            <li class="group">
              <div class="flex items-center">
                {index > 0 && (
                  <div class="mx-5 h-2 w-px bg-slate-110"></div>
                )}
                {item.url ? (
                  <a class="text-2xs font-medium text-slate-310 hover:text-primary-600 transition-colors" href={item.url}>
                    {item.label}
                  </a>
                ) : (
                  <span class="text-2xs font-medium text-slate-310 cursor-text opacity-75">
                    {item.label}
                  </span>
                )}
              </div>
            </li>
          ))}
        </ol>
      </nav>
    );
  }
}
