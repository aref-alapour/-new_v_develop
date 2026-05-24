import { Component, Prop, h } from '@stencil/core';

@Component({
  tag: 'ez-input',
  shadow: false,
})
export class EzInput {
  @Prop() label: string;
  @Prop() type: string = 'text';
  @Prop() name: string;
  @Prop() placeholder: string;
  @Prop() value: string;
  @Prop() required: boolean = false;
  @Prop() readonly: boolean = false;

  render() {
    return (
      <div class="relative w-full text-right font-sans" dir="rtl">
        {this.label && <label class="mb-2 block text-sm font-bold text-steel">{this.label}</label>}
        <input
          type={this.type}
          name={this.name}
          placeholder={this.placeholder}
          value={this.value}
          required={this.required}
          readOnly={this.readonly}
          class="w-full bg-white border border-gray-100/80 rounded-lg max-lg:shadow-13 h-d48 px-4 py-2 text-ink-tab focus:outline-none focus:ring-1 focus:ring-primary-500 placeholder-gray-400"
        />
      </div>
    );
  }
}
