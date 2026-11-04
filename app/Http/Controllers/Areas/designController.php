<?php

namespace App\Http\Controllers\Areas;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\View;

class designController extends Controller
{
    public array $breadcrumbs = [];

    public function __construct()
    {
        $this->middleware('auth');
        $this->breadcrumbs[] = ['name' => 'Design', 'url' => route('design.index')];
    }

    public function index()
    {
        return View::make('areas.design.index')->with([
            'breadcrumbs' => $this->breadcrumbs,
            'accessList' => [
                [
                    'name' => 'Car gallery',
                    'url' => route('design.tools.car_gallery.index'),
                    'icon' => 'fa-car',
                ],
                [
                    'name' => 'Events',
                    'url' => route('design.tools.events.index'),
                    'icon' => 'fa-calendar-days',
                ],
                [
                    'name' => 'ASM homepage',
                    'url' => route('design.tools.homepage.asm.index'),
                    'icon' => 'fa-house-laptop',
                ],
                [
                    'name' => 'ASD homepage',
                    'url' => route('design.tools.homepage.asd.index'),
                    'icon' => 'fa-house-laptop',
                ],
                [
                    'name' => 'Product image review',
                    'url' => route('design.tools.product_images.index'),
                    'icon' => 'fa-images',
                ],
                [
                    'name' => trans('messages.asm_resources'),
                    'url' => route('design.tools.resources.asm.index'),
                    'icon' => 'fa-folder-open',
                ],
                [
                    'name' => trans('messages.resources'),
                    'url' => route('design.tools.resources.asd.index'),
                    'icon' => 'fa-folder-open',
                ],
            ],
        ]);
    }
}
