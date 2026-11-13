<?php

namespace App\Http\Controllers;

use App\Models\Address;
use App\Models\ShippingZone;
use Illuminate\Http\Request;
use App\Support\Seo;
use Inertia\Inertia;

class AddressController extends Controller
{
    public function index(Request $request)
    {
                Seo::set([
            'title' => Seo::tr('My addresses', 'عناويني'),
            'noindex' => true,
        ]);
        return Inertia::render('Addresses', [
            'addresses' => $request->user()->addresses()->orderByDesc('is_default')->orderBy('id')->get(),
            'governorates' => ShippingZone::where('is_active', true)->pluck('governorates')->flatten()->unique()->values(),
        ]);
    }

    public function store(Request $request)
    {
        $user = $request->user();
        $data = $this->validated($request);

        // The first address is always the default.
        $data['is_default'] = ($data['is_default'] ?? false) || ! $user->addresses()->exists();

        if ($data['is_default']) {
            $user->addresses()->update(['is_default' => false]);
        }

        $user->addresses()->create($data);

        return back()->with('status', __('store.address_saved'));
    }

    public function update(Request $request, Address $address)
    {
        $this->own($address);
        $data = $this->validated($request);

        if (! empty($data['is_default'])) {
            $request->user()->addresses()->update(['is_default' => false]);
        }

        $address->update($data);

        return back()->with('status', __('store.address_updated'));
    }

    public function destroy(Address $address)
    {
        $this->own($address);

        $wasDefault = $address->is_default;
        $address->delete();

        // Keep one default if any address remains.
        if ($wasDefault) {
            auth()->user()->addresses()->oldest()->first()?->update(['is_default' => true]);
        }

        return back()->with('status', __('store.address_deleted'));
    }

    private function own(Address $address): void
    {
        abort_unless($address->user_id === auth()->id(), 403);
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'label' => ['nullable', 'string', 'max:50'],
            'full_name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:30'],
            'governorate' => ['required', 'string', 'max:100'],
            'city' => ['required', 'string', 'max:100'],
            'area' => ['nullable', 'string', 'max:100'],
            'street' => ['required', 'string', 'max:255'],
            'building' => ['nullable', 'string', 'max:100'],
            'floor' => ['nullable', 'string', 'max:50'],
            'landmark' => ['nullable', 'string', 'max:255'],
            'is_default' => ['boolean'],
        ]);
    }
}
