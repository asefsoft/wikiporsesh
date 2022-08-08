<?php

namespace App\View\Components;

use Illuminate\View\Component;

class AppLayout extends Component
{
    /**
     * Get the view / contents that represents the component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
//        request()->session()->flash('flash.banner', 'Yay it works!');
        return view('layouts.app');
    }
}
