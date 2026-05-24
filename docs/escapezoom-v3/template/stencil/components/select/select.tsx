import { Component, Prop, State, Element, Listen, h, Watch, Event, EventEmitter } from '@stencil/core';

@Component({
  tag: 'ez-select',
  styleUrl: 'select.css',
  shadow: false,
})
export class EzSelect {
  @Element() el: HTMLElement;

  @Prop() name: string;
  @Prop() placeholder: string = 'انتخاب کنید';
  @Prop() options: string | any[] = [];
  @Prop() value: string | string[];
  @Prop() mode: 'single' | 'multiple' = 'single';
  @Prop() searchable: boolean = false;
  @Prop() label: string;

  @State() isOpen: boolean = false;
  @State() searchQuery: string = '';
  @State() _options: Array<{ label: string; value: string; selected?: boolean }> = [];
  @State() internalValue: string | string[] = '';
  @State() displayValue: string = '';

  @Event() selectionChange: EventEmitter<any>;

  componentWillLoad() {
    this.parseOptions(this.options);
    this.syncInternalValue(this.value);
  }

  @Watch('options')
  parseOptions(newValue: string | any[]) {
    if (typeof newValue === 'string') {
      try {
        this._options = JSON.parse(newValue);
      } catch (e) {
        this._options = [];
      }
    } else {
      this._options = Array.isArray(newValue) ? [...newValue] : [];
    }
    this.updateDisplayValue();
  }

  @Watch('value')
  syncInternalValue(newValue: string | string[] | undefined) {
    if (this.mode === 'multiple') {
      this.internalValue = Array.isArray(newValue) ? newValue : (newValue ? [newValue as string] : []);
    } else {
      this.internalValue = (newValue as string) ?? '';
    }
    this.updateDisplayValue();
  }

  updateDisplayValue() {
    if (this.mode === 'multiple') {
      const vals = this.internalValue as string[];
      if (!vals || vals.length === 0) {
        this.displayValue = '';
        return;
      }
      const labels = this._options.filter(opt => vals.includes(opt.value)).map(opt => opt.label);
      this.displayValue = labels.join(', ');
    } else {
      const val = this.internalValue as string;
      const found = this._options.find(opt => opt.value == val);
      this.displayValue = found ? found.label : '';
    }
  }

  toggleDropdown = (e: MouseEvent) => {
    e.stopPropagation();
    if (!this.isOpen) this.searchQuery = '';
    this.isOpen = !this.isOpen;
  };

  closeDropdown = () => {
    this.isOpen = false;
  };

  @Listen('click', { target: 'window' })
  handleWindowClick() {
    if (this.isOpen) this.closeDropdown();
  }

  handleOptionClick(e: MouseEvent, option: any) {
    e.stopPropagation();
    if (this.mode === 'multiple') {
      const currentVals = [...(this.internalValue as string[])];
      const index = currentVals.indexOf(option.value);
      if (index > -1) {
        currentVals.splice(index, 1);
      } else {
        currentVals.push(option.value);
      }
      this.internalValue = currentVals;
    } else {
      this.internalValue = option.value;
      this.closeDropdown();
    }
    this.updateDisplayValue();
    this.selectionChange.emit(this.internalValue);
  }

  handleSearchInput = (e: InputEvent) => {
    this.searchQuery = (e.target as HTMLInputElement).value.toLowerCase();
  };

  getFilteredOptions() {
    if (!this.searchQuery) return this._options;
    return this._options.filter(opt => opt.label.toLowerCase().includes(this.searchQuery));
  }

  render() {
    const filteredOptions = this.getFilteredOptions();
    return (
      <div class="relative w-full max-w-xs font-sans text-right" dir="rtl">
        {this.label && <label class="mb-2 block text-sm font-bold text-steel">{this.label}</label>}
        {this.mode === 'single' ? (
          <input type="hidden" name={this.name} value={this.internalValue as string} />
        ) : (
          (this.internalValue as string[]).map((val: string) => (
            <input type="hidden" name={`${this.name}[]`} value={val} />
          ))
        )}
        <button
          type="button"
          class="w-full bg-white border border-gray-100/80 rounded-lg max-lg:shadow-13 h-d48 px-4 py-2 text-right flex items-center justify-between focus:outline-none focus:ring-1 focus:ring-primary-500"
          onClick={this.toggleDropdown}
        >
          <span class="text-ink-tab font-extrabold truncate">
            {this.displayValue || <span class="text-gray-400 font-normal">{this.placeholder}</span>}
          </span>
          <svg class={`w-4 h-4 text-gray-400 m-0 transition-transform ${this.isOpen ? 'rotate-180' : ''}`} fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
          </svg>
        </button>
        {this.isOpen && (
          <div class="absolute w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg z-50 overflow-hidden">
            {this.searchable && (
              <div class="p-2 border-b border-gray-100">
                <input
                  type="text"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:border-primary-500"
                  placeholder="جستجو..."
                  onInput={this.handleSearchInput}
                  onClick={(e) => e.stopPropagation()}
                />
              </div>
            )}
            <div class="max-h-60 overflow-y-auto custom-scrollbar">
              {filteredOptions.length > 0 ? (
                filteredOptions.map(opt => {
                  const isSelected = this.mode === 'multiple'
                    ? (this.internalValue as string[]).includes(opt.value)
                    : this.internalValue == opt.value;
                  return (
                    <div
                      class={`block w-full px-4 py-2 cursor-pointer transition flex items-center justify-between
                        ${isSelected ? 'bg-primary-50 text-primary-600' : 'hover:bg-gray-100 hover:text-primary-500 text-gray-700'}`}
                      onClick={(e) => this.handleOptionClick(e, opt)}
                    >
                      <span>{opt.label}</span>
                      {this.mode === 'multiple' && (
                        <input type="checkbox" checked={isSelected} class="bg-primary-600 border-gray-300 rounded focus:ring-primary-500" readOnly />
                      )}
                      {this.mode === 'single' && isSelected && (
                        <svg class="w-4 h-4 text-primary-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                      )}
                    </div>
                  );
                })
              ) : (
                <div class="px-4 py-3 text-sm text-gray-400 text-center">موردی یافت نشد</div>
              )}
            </div>
          </div>
        )}
      </div>
    );
  }
}
