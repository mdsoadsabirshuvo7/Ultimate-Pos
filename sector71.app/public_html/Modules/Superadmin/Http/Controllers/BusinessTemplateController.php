<?php

namespace Modules\Superadmin\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;
use Modules\Superadmin\Entities\BusinessTemplate;
use Yajra\DataTables\Facades\DataTables;

class BusinessTemplateController extends Controller
{
    public function index()
    {
        if (!auth()->user()->can('superadmin')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            $templates = BusinessTemplate::select([
                'id', 'name', 'slug', 'description', 'icon', 'color',
                'is_active', 'sort_order', 'categories', 'units', 
                'tax_rates', 'products', 'created_at'
            ]);

            return DataTables::of($templates)
                ->addColumn('categories_count', function ($row) {
                    return is_array($row->categories) ? count($row->categories) : 0;
                })
                ->addColumn('units_count', function ($row) {
                    return is_array($row->units) ? count($row->units) : 0;
                })
                ->addColumn('products_count', function ($row) {
                    return is_array($row->products) ? count($row->products) : 0;
                })
                ->addColumn('tax_rates_count', function ($row) {
                    return is_array($row->tax_rates) ? count($row->tax_rates) : 0;
                })
                ->editColumn('is_active', function ($row) {
                    return $row->is_active 
                        ? '<span class="label bg-green">' . __('business.is_active') . '</span>' 
                        : '<span class="label bg-gray">' . __('lang_v1.inactive') . '</span>';
                })
                ->addColumn('preview', function ($row) {
                    $style = "background-color: {$row->color}; color: white; padding: 10px 15px; border-radius: 5px; display: inline-block;";
                    $icon = $row->icon ? "<i class='{$row->icon}'></i> " : '';
                    return "<span style='{$style}'>{$icon}{$row->name}</span>";
                })
                ->addColumn('action', function ($row) {
                    $html = '<a href="' . action([\Modules\Superadmin\Http\Controllers\BusinessTemplateController::class, 'edit'], [$row->id]) . '" 
                                class="btn btn-primary btn-xs">
                                <i class="fa fa-edit"></i> ' . __('messages.edit') . '
                            </a> ';
                    $html .= '<a href="' . action([\Modules\Superadmin\Http\Controllers\BusinessTemplateController::class, 'duplicate'], [$row->id]) . '" 
                                class="btn btn-info btn-xs">
                                <i class="fa fa-copy"></i> ' . __('lang_v1.duplicate') . '
                            </a> ';
                    $html .= '<button type="button" class="btn btn-danger btn-xs delete_template" 
                                data-href="' . action([\Modules\Superadmin\Http\Controllers\BusinessTemplateController::class, 'destroy'], [$row->id]) . '">
                                <i class="fa fa-trash"></i> ' . __('messages.delete') . '
                            </button>';
                    return $html;
                })
                ->rawColumns(['is_active', 'preview', 'action'])
                ->make(true);
        }

        return view('superadmin::business_templates.index');
    }

    public function create()
    {
        if (!auth()->user()->can('superadmin')) {
            abort(403, 'Unauthorized action.');
        }

        $icons = $this->getIconOptions();
        $colors = $this->getColorOptions();

        return view('superadmin::business_templates.create')
            ->with(compact('icons', 'colors'));
    }

    public function store(Request $request)
    {
        if (!auth()->user()->can('superadmin')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $request->validate([
                'name' => 'required|string|max:191',
                'description' => 'nullable|string',
                'icon' => 'nullable|string|max:191',
                'color' => ['nullable', 'string', 'max:20', 'regex:/^#[0-9A-Fa-f]{6}$/'],
                'sort_order' => 'nullable|integer|min:0',
            ]);

            $input = $request->only([
                'name', 'description', 'icon', 'color', 'sort_order', 'is_active'
            ]);

            $input['is_active'] = !empty($input['is_active']) ? 1 : 0;
            $input['created_by'] = auth()->id();

            $input['categories'] = $this->processJsonInput($request->input('categories_data'));
            $input['units'] = $this->processJsonInput($request->input('units_data'));
            $input['tax_rates'] = $this->processJsonInput($request->input('tax_rates_data'));
            $input['products'] = $this->processJsonInput($request->input('products_data'));
            $input['brands'] = $this->processJsonInput($request->input('brands_data'));
            $input['expense_categories'] = $this->processJsonInput($request->input('expense_categories_data'));
            $input['enabled_modules'] = $request->input('enabled_modules', []);

            BusinessTemplate::create($input);

            $output = ['success' => 1, 'msg' => __('lang_v1.success')];
        } catch (\Exception $e) {
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());
            $output = ['success' => 0, 'msg' => __('messages.something_went_wrong')];
        }

        return redirect()
            ->action([\Modules\Superadmin\Http\Controllers\BusinessTemplateController::class, 'index'])
            ->with('status', $output);
    }

    public function edit($id)
    {
        if (!auth()->user()->can('superadmin')) {
            abort(403, 'Unauthorized action.');
        }

        $template = BusinessTemplate::findOrFail($id);
        $icons = $this->getIconOptions();
        $colors = $this->getColorOptions();

        return view('superadmin::business_templates.edit')
            ->with(compact('template', 'icons', 'colors'));
    }

    public function update(Request $request, $id)
    {
        if (!auth()->user()->can('superadmin')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $template = BusinessTemplate::findOrFail($id);

            $request->validate([
                'name' => 'required|string|max:191',
                'description' => 'nullable|string',
                'icon' => 'nullable|string|max:191',
                'color' => ['nullable', 'string', 'max:20', 'regex:/^#[0-9A-Fa-f]{6}$/'],
                'sort_order' => 'nullable|integer|min:0',
            ]);

            $input = $request->only([
                'name', 'description', 'icon', 'color', 'sort_order', 'is_active'
            ]);

            $input['is_active'] = !empty($input['is_active']) ? 1 : 0;

            $input['categories'] = $this->processJsonInput($request->input('categories_data'));
            $input['units'] = $this->processJsonInput($request->input('units_data'));
            $input['tax_rates'] = $this->processJsonInput($request->input('tax_rates_data'));
            $input['products'] = $this->processJsonInput($request->input('products_data'));
            $input['brands'] = $this->processJsonInput($request->input('brands_data'));
            $input['expense_categories'] = $this->processJsonInput($request->input('expense_categories_data'));
            $input['enabled_modules'] = $request->input('enabled_modules', []);

            $template->update($input);

            $output = ['success' => 1, 'msg' => __('lang_v1.success')];
        } catch (\Exception $e) {
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());
            $output = ['success' => 0, 'msg' => __('messages.something_went_wrong')];
        }

        return redirect()
            ->action([\Modules\Superadmin\Http\Controllers\BusinessTemplateController::class, 'index'])
            ->with('status', $output);
    }

    public function destroy($id)
    {
        if (!auth()->user()->can('superadmin')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            BusinessTemplate::findOrFail($id)->delete();
            $output = ['success' => true, 'msg' => __('lang_v1.success')];
        } catch (\Exception $e) {
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());
            $output = ['success' => false, 'msg' => __('messages.something_went_wrong')];
        }

        return response()->json($output);
    }

    public function duplicate($id)
    {
        if (!auth()->user()->can('superadmin')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $template = BusinessTemplate::findOrFail($id);
            
            $newTemplate = $template->replicate();
            $newTemplate->name = $this->generateDuplicateName($template->name);
            $newTemplate->slug = $this->generateUniqueSlug($newTemplate->name);
            $newTemplate->created_by = auth()->id();
            $newTemplate->save();

            $output = ['success' => 1, 'msg' => __('lang_v1.success')];
        } catch (\Exception $e) {
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());
            $output = ['success' => 0, 'msg' => __('messages.something_went_wrong')];
        }

        return redirect()
            ->action([\Modules\Superadmin\Http\Controllers\BusinessTemplateController::class, 'index'])
            ->with('status', $output);
    }

    private function processJsonInput($input)
    {
        if (empty($input)) {
            return null;
        }

        if (is_string($input)) {
            $decoded = json_decode($input, true);
            return json_last_error() === JSON_ERROR_NONE ? $decoded : null;
        }

        return is_array($input) ? $input : null;
    }

    private function generateDuplicateName($baseName)
    {
        $name = $baseName . ' (Copy)';
        $counter = 2;

        while (BusinessTemplate::withTrashed()->where('name', $name)->exists()) {
            $name = $baseName . ' (Copy ' . $counter . ')';
            $counter++;
        }

        return $name;
    }

    private function generateUniqueSlug($name)
    {
        $baseSlug = Str::slug($name);
        if (empty($baseSlug)) {
            $baseSlug = 'business-template';
        }
        $slug = $baseSlug;
        $counter = 2;

        while (BusinessTemplate::withTrashed()->where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    private function getIconOptions()
    {
        return [
            '' => __('messages.please_select'),
            'fa fa-star' => 'Star (All In One)',
            'fa fa-medkit' => 'Medkit (Pharmacy)',
            'fa fa-wrench' => 'Wrench (Service Center)',
            'fa fa-desktop' => 'Desktop (Electronics)',
            'fa fa-shopping-cart' => 'Cart (Super Market)',
            'fa fa-cutlery' => 'Cutlery (Restaurant)',
            'fa fa-car' => 'Car (Auto Parts)',
            'fa fa-book' => 'Book (Bookstore)',
            'fa fa-tshirt' => 'T-Shirt (Clothing)',
            'fa fa-home' => 'Home (Furniture)',
            'fa fa-paint-brush' => 'Paint Brush (Hardware)',
            'fa fa-gift' => 'Gift (Gift Shop)',
            'fa fa-glass' => 'Glass (Bar/Pub)',
            'fa fa-coffee' => 'Coffee (Cafe)',
            'fa fa-leaf' => 'Leaf (Organic/Health)',
            'fa fa-paw' => 'Paw (Pet Store)',
            'fa fa-camera' => 'Camera (Photography)',
            'fa fa-music' => 'Music (Music Store)',
            'fa fa-futbol-o' => 'Sports (Sports Store)',
            'fa fa-building' => 'Building (General Business)',
        ];
    }

    private function getColorOptions()
    {
        return [
            '#3c8dbc' => 'Blue',
            '#00a65a' => 'Green',
            '#f39c12' => 'Orange',
            '#dd4b39' => 'Red',
            '#605ca8' => 'Purple',
            '#00c0ef' => 'Aqua',
            '#d2d6de' => 'Gray',
            '#001f3f' => 'Navy',
            '#39cccc' => 'Teal',
            '#3d9970' => 'Olive',
            '#01ff70' => 'Lime',
            '#ff851b' => 'Orange Dark',
            '#f012be' => 'Fuchsia',
            '#b10dc9' => 'Violet',
            '#111111' => 'Black',
        ];
    }
}
