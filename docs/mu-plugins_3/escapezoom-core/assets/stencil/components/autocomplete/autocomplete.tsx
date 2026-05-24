import { Component, Prop, State, Element, h, Event, EventEmitter } from '@stencil/core';

@Component({
  tag: 'ez-autocomplete',
  styleUrl: 'autocomplete.css',
  shadow: false,
})
export class EzAutocomplete {
  @Element() el: HTMLElement;

  @Prop() placeholder: string = 'جستجو...';
  @Prop() debounce: number = 300;
  @Prop() loading: boolean = false;
  @Prop() results: string | any[] = [];
  @Prop() value: string = '';

  @State() searchQuery: string = '';
  @State() _results: any[] = [];
  @State() isOpen: boolean = false;

  @Event() search: EventEmitter<string>;
  @Event() selection: EventEmitter<any>;

  private timer: any;
  private inputRef: HTMLInputElement;

  componentWillLoad() {
    this.searchQuery = this.value;
    this.parseResults(this.results);
  }

  componentDidLoad() {
    document.addEventListener('click', this.handleOutsideClick);
  }

  disconnectedCallback() {
    document.removeEventListener('click', this.handleOutsideClick);
  }

  handleOutsideClick = (e: MouseEvent) => {
    if (!this.el.contains(e.target as Node)) {
      this.isOpen = false;
    }
  }

  parseResults(val: string | any[]) {
    if (typeof val === 'string') {
      try {
        this._results = JSON.parse(val);
      } catch (e) {
        this._results = [];
      }
    } else {
      this._results = val || [];
    }
  }

  handleInput = (e: InputEvent) => {
    const val = (e.target as HTMLInputElement).value;
    this.searchQuery = val;
    this.isOpen = val.length > 0;
    if (this.timer) clearTimeout(this.timer);
    this.timer = setTimeout(() => {
      this.search.emit(val);
    }, this.debounce);
  };

  handleSelect(item: any) {
    this.selection.emit(item);
    this.isOpen = false;
  }

  handleClear = () => {
    this.searchQuery = '';
    this.isOpen = false;
    this.search.emit('');
    if (this.inputRef) this.inputRef.focus();
  }

  render() {
    return (
      <div class="relative w-full">
        <div class="relative">
          <input
            ref={el => this.inputRef = el as HTMLInputElement}
            type="text"
            class="w-full h-12 px-4 rounded-lg text-right border border-[#E4EBF0] placeholder:text-gray-400 focus:outline-none focus:ring-1 focus:ring-primary-500 bg-white"
            placeholder={this.placeholder}
            value={this.searchQuery}
            onInput={this.handleInput}
          />
          <div class="absolute left-3 top-1/2 transform -translate-y-1/2 flex items-center">
            {this.loading ? (
              <div class="w-5 h-5 border-2 border-gray-200 border-t-primary-500 rounded-full animate-spin"></div>
            ) : this.searchQuery ? (
              <button type="button" onClick={this.handleClear} class="text-gray-400 hover:text-gray-600">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
              </button>
            ) : (
              <button type="button" class="text-gray-400">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
              </button>
            )}
          </div>
        </div>
        {this.isOpen && (
          <div class="absolute w-full mt-2 bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden z-[60]">
            {this.loading ? (
              <div class="p-4 text-center text-sm text-gray-400">در حال جستجو...</div>
            ) : this._results.length > 0 ? (
              <ul class="max-h-[300px] overflow-y-auto">
                {this._results.map(item => (
                  <li>
                    <a href={item.link || '#'} class="flex items-center gap-3 px-4 py-3 hover:bg-gray-50 transition-colors border-b border-gray-50 last:border-0" onClick={(e) => { e.preventDefault(); this.handleSelect(item); }}>
                      {item.image && (
                        <img src={item.image} class="w-10 h-10 rounded-lg object-cover" alt="" />
                      )}
                      <div class="flex flex-col">
                        <span class="text-sm font-bold text-gray-800">{item.title}</span>
                        {item.subtitle && <span class="text-xs text-gray-500">{item.subtitle}</span>}
                      </div>
                    </a>
                  </li>
                ))}
              </ul>
            ) : (
              <div class="p-4 text-center text-sm text-gray-400">
                {this.searchQuery.length < 2 ? 'لطفا بیشتر تایپ کنید...' : 'موردی یافت نشد'}
              </div>
            )}
          </div>
        )}
      </div>
    );
  }
}
