<?php

namespace App\Http\Controllers;

use App\Actions\Price\DestroyPrice;
use App\Actions\Price\StorePrice;
use App\Actions\Price\UpdatePrice;
use App\Http\Requests\Price\StoreRequest as PriceStoreRequest;
use App\Http\Requests\Price\UpdateRequest as PriceUpdateRequest;
use App\Http\Resources\ErrorResponse;
use App\Http\Resources\PriceResource;
use App\Http\Resources\ProductResource;
use App\Models\Catalog\Price;
use App\Models\Catalog\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class PriceController extends Controller
{
    /**
     * Store a newly created resource in storage.
     * Handles both standard form submissions (redirect) and JSON API requests (modal).
     */
    public function store(PriceStoreRequest $request, Product $product, StorePrice $storePrice): RedirectResponse
    {
        // The StorePrice action likely handles the actual creation logic
        $price = $storePrice($product, $request);

        // Redirect back to the product show page
        return redirect()->route('products.show', [$product])
            ->with('success', 'Price created successfully.'); // Optional success message
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(PriceUpdateRequest $request, Product $product, Price $price, UpdatePrice $updatePrice): RedirectResponse
    {
        // Authorization handled by PriceUpdateRequest
        $price = $updatePrice($product, $price, $request);

        // Redirect back to the product show page
        return redirect()->route('products.show', [$product])
            ->with('success', 'Price updated successfully.'); // Optional success message
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product, Price $price, DestroyPrice $destroyPrice): RedirectResponse
    {
        if ($price->product_id !== $product->id) {
             abort(403); // Can't delete price from wrong product
        }
        // Optional: Policy check: $this->authorize('delete', $price);

        $destroyPrice($product, $price); // Pass product for context if needed in action/policy

        // Redirect back after deletion
        return redirect()->route('products.show', [$product])
            ->with('success', 'Price deleted successfully.');
    }
}
