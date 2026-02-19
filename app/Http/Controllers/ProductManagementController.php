<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ProductManagementController extends Controller
{
    public function index(): View
    {
        $products = Product::query()
            ->with('categoryRef')
            ->orderByDesc('id')
            ->get();

        $categories = Category::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('pages.products', compact('products', 'categories'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'price' => ['required', 'numeric', 'min:0'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'description' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $category = empty($validated['category_id']) ? null : Category::query()->find($validated['category_id']);

        $validated['category'] = $category?->name;
        $validated['is_active'] = $request->boolean('is_active');
        $validated['added_by'] = Auth::id();
        $validated['updated_by'] = Auth::id();

        $product = Product::create($validated);

        $this->logProductAction($product, 'created', [
            'name' => $product->name,
            'price' => $product->price,
            'category' => $product->category,
        ]);

        return redirect()
            ->route('products.index')
            ->with('success', 'Product created successfully.');
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'price' => ['required', 'numeric', 'min:0'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'description' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $before = [
            'name' => $product->name,
            'price' => $product->price,
            'category' => $product->category,
            'is_active' => $product->is_active,
        ];

        $category = empty($validated['category_id']) ? null : Category::query()->find($validated['category_id']);

        $validated['category'] = $category?->name;
        $validated['is_active'] = $request->boolean('is_active');
        $validated['updated_by'] = Auth::id();

        $product->update($validated);

        $this->logProductAction($product, 'updated', [
            'before' => $before,
            'after' => [
                'name' => $product->name,
                'price' => $product->price,
                'category' => $product->category,
                'is_active' => $product->is_active,
            ],
        ]);

        return redirect()
            ->route('products.index')
            ->with('success', 'Product updated successfully.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $details = [
            'name' => $product->name,
            'price' => $product->price,
            'category' => $product->category,
        ];

        $product->deleted_by = Auth::id();
        $product->save();
        $product->delete();

        $this->logProductAction($product, 'deleted', $details);

        return redirect()
            ->route('products.index')
            ->with('success', 'Product deleted successfully.');
    }

    private function logProductAction(Product $product, string $action, array $details = []): void
    {
        ProductLog::create([
            'product_id' => $product->id,
            'action' => $action,
            'details' => $details,
            'performed_by' => Auth::id(),
            'performed_at' => now(),
        ]);
    }
}
