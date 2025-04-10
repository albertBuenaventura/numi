<?php

declare(strict_types=1);

namespace App\Http\Requests\Offer;

use App\Models\Catalog\Price;
use App\Models\Store\Offer;
use App\Models\Store\Slot;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class OfferSlotUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
        $offer = $this->route('offer');
        $slot = $this->route('slot');

        // Ensure the offer belongs to the user's current organization
        // and the slot belongs to the offer
        return $offer instanceof Offer
            && $slot instanceof Slot
            && $offer->organization_id === Auth::user()->currentOrganization->id
            && $slot->offer_id === $offer->id;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $offer = $this->route('offer');
        $slot = $this->route('slot');
        $organizationId = Auth::user()->currentOrganization->id;

        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'key' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                 // Key must be unique within the *offer*
                 // Use the actual table name from Slot model if different
                 // $slot->getTable()
                // Rule::unique('store_offer_slots')->where(function ($query) use ($offer) { 
                //     return $query->where('offer_id', $offer->id);
                // })->ignore($slot->id), // Ignore the current slot being updated
                'regex:/^[a-z0-9_]+$/' // Ensure key is snake_case and alphanumeric
            ],
            'sort_order' => ['sometimes', 'required', 'integer', 'min:0'],
            'is_required' => ['sometimes', 'required', 'boolean'],
            'default_price_id' => [ 
                'nullable', 
                'integer',
                Rule::exists('catalog_prices', 'id')->where(function ($query) use ($organizationId) {
                    return $query->where('organization_id', $organizationId)
                                ->where('is_active', true); 
                })
            ],
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Ensure boolean value for is_required
        if ($this->has('is_required')) {
            $this->merge([
                'is_required' => filter_var($this->input('is_required'), FILTER_VALIDATE_BOOLEAN)
            ]);
        }
        // Ensure sort_order is integer
        if ($this->has('sort_order')) {
            $this->merge([
                 'sort_order' => (int) $this->input('sort_order', 0)
            ]);
        }
        // Ensure default_price_id is null if empty string or not present
         if (!$this->input('default_price_id')) { 
             $this->merge(['default_price_id' => null]); 
         }
    }
}
