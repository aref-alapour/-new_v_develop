import { Component, Prop, h, Watch, Element } from '@stencil/core';

@Component({
  tag: 'ez-tab',
  shadow: false,
})
export class EzTab {
  @Element() el: HTMLElement;
  @Prop() tabId: string;
  @Prop() label: string;
  @Prop({ mutable: true }) active: boolean = false;

  @Watch('active')
  activeChanged(newValue: boolean) {
    if (newValue) {
      this.el.style.display = 'block';
    } else {
      this.el.style.display = 'none';
    }
  }

  componentDidLoad() {
    this.activeChanged(this.active);
  }

  render() {
    return (
      <div class={`transition-opacity duration-300 ${this.active ? 'opacity-100' : 'opacity-0 h-0 overflow-hidden'}`}>
        <slot />
      </div>
    );
  }
}
