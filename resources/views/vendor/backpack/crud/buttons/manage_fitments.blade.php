@if ($crud->hasAccess('list'))
@php
$fitmentCount = \App\Models\ProductVehicleFitment::where('product_id', $entry->id)->count();
@endphp
<a href="{{ backpack_url('product-vehicle-fitment?product_id=' . $entry->id) }}"
    class="btn btn-sm btn-link"
    title="Quản lý Vehicle Fitments"
    data-toggle="tooltip">
    <i class="la la-car"></i> Fitments ({{ $fitmentCount }})
</a>
@endif