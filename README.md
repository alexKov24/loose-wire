# Example use

```php
<?php
/**
 * Template Name: Wire Test
 * 
 * Custom page template for ICJW conference registration
 */

wp_head();

use \LooseWire\WireManager as WireManager;

WireManager::print('CounterWire')


```

```php
// conter wire
<?php
use LooseWire\Wire as Wire;

class CounterWire extends Wire
{
    public $count = 0;
    public $text = '';

    public function increment()
    {
        $this->count++;
    }

    public function decrement()
    {
        $this->count--;
    }

    public function answer()
    {
        if ($this->text == 'hi there') {
            $this->text = 'obiwan kenobi';
        }
    }

    public function render(): string
    {
        $title = ($this->count == 2) ? "<h2>you got {$this->count}!</h2>" : "";
        $disabled = $this->count >= 3 ? 'disabled' : '';

        return <<<HTML
                {$title}
                <div class='counter-wire'>
                    <span>Count: {$this->count}</span>
                    <button wire:click='increment' $disabled>+</button>
                    <button wire:click='decrement' >-</button>
                </div>

                <div class='jedai-wire'>
                    <input value='{$this->text}' wire-value='text' wire:bind='text'>
                    <button wire:click='answer' >say</button>
                </div>
                
        HTML;
    }

}


```