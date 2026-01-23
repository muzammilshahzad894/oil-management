@extends('admin.layout')

@section('title', 'Edit Sale')

@section('content')
<div class="card">
    <div class="card-header">
        <i class="fas fa-edit me-2"></i>Edit Sale
    </div>
    <div class="card-body">
        <form action="{{ route('admin.sales.update', $sale->id) }}" method="POST">
            @csrf
            @method('PUT')
            @include('admin.sales.partials.form', ['sale' => $sale, 'customers' => $customers, 'brands' => $brands])
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.getElementById('brand_id').addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const stock = selectedOption.getAttribute('data-stock');
        const stockInfo = document.getElementById('stock-info');
        
        if (stock !== null && stock !== '') {
            stockInfo.textContent = 'Available stock: ' + stock;
            if (parseInt(stock) < 10) {
                stockInfo.className = 'text-danger';
            } else {
                stockInfo.className = 'text-success';
            }
        } else {
            stockInfo.textContent = '';
        }
    });
    
    // Trigger on page load if brand is already selected
    if (document.getElementById('brand_id').value) {
        document.getElementById('brand_id').dispatchEvent(new Event('change'));
    }
</script>
@endsection
