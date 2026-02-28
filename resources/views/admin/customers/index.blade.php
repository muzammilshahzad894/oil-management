@extends('admin.layout')

@section('title', 'Customers')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="fas fa-users me-2"></i>Customers</span>
        <a href="{{ route('admin.customers.create') }}" class="btn btn-light btn-sm">
            <i class="fas fa-plus me-2"></i>Add New Customer
        </a>
    </div>
    <div class="card-body">
        <div class="mb-3">
            <input type="text" 
                   id="customerSearch" 
                   class="form-control" 
                   placeholder="Search by name, phone, or email..." 
                   value="{{ request('search') }}"
                   autocomplete="off">
        </div>
        
        <div id="tableContainer">
            @include('admin.customers.partials.table', ['customers' => $customers])
        </div>
    </div>
</div>

{{-- Modal: Add Extra Paid --}}
<div class="modal fade" id="extraPaidModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-wallet me-2"></i>Add extra paid</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="mb-2">Customer: <strong id="extraPaidCustomerName"></strong></p>
                <p class="mb-3">Current extra paid balance: <strong id="extraPaidBalance">—</strong></p>
                <form id="extraPaidForm">
                    <input type="hidden" id="extraPaidCustomerId" name="customer_id">
                    @csrf
                    <div class="mb-3">
                        <label for="extraPaidAmount" class="form-label">Amount to add <span class="text-danger">*</span></label>
                        <input type="number" step="any" min="0.01" class="form-control" id="extraPaidAmount" name="amount" required>
                    </div>
                    <div class="mb-3">
                        <label for="extraPaidNote" class="form-label">Note (optional)</label>
                        <input type="text" class="form-control" id="extraPaidNote" name="note" placeholder="e.g. Advance received">
                    </div>
                    <button type="submit" class="btn btn-primary" id="extraPaidSubmitBtn">
                        <i class="fas fa-save me-2"></i>Save
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $(document).ready(function() {
        let searchTimeout;
        const $searchInput = $('#customerSearch');
        const $tableContainer = $('#tableContainer');
        const balanceUrl = '{{ url("admin/customers") }}';
        const storeExtraUrl = '{{ url("admin/customers") }}';

        $(document).on('click', '.btn-extra-paid', function() {
            const customerId = $(this).data('customer-id');
            const customerName = $(this).data('customer-name');
            $('#extraPaidCustomerId').val(customerId);
            $('#extraPaidCustomerName').text(customerName);
            $('#extraPaidAmount').val('');
            $('#extraPaidNote').val('');
            $('#extraPaidBalance').text('…');
            var modal = new bootstrap.Modal(document.getElementById('extraPaidModal'));
            modal.show();
            $.get(balanceUrl + '/' + customerId + '/extra-paid/balance', function(data) {
                $('#extraPaidBalance').text(data.formatted);
            }).fail(function() {
                $('#extraPaidBalance').text('0');
            });
        });

        $('#extraPaidForm').on('submit', function(e) {
            e.preventDefault();
            const customerId = $('#extraPaidCustomerId').val();
            const $btn = $('#extraPaidSubmitBtn').prop('disabled', true);
            $.ajax({
                url: storeExtraUrl + '/' + customerId + '/extra-paid',
                method: 'POST',
                data: {
                    _token: $('input[name="_token"]', '#extraPaidForm').val(),
                    amount: $('#extraPaidAmount').val(),
                    note: $('#extraPaidNote').val()
                },
                success: function(data) {
                    $('#extraPaidBalance').text(data.formatted);
                    $('#extraPaidAmount').val('');
                    $('#extraPaidNote').val('');
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({ icon: 'success', title: 'Saved', text: data.message }).then(function() {
                            window.location.reload();
                        });
                    } else {
                        alert(data.message);
                        window.location.reload();
                    }
                },
                error: function(xhr) {
                    var msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : (xhr.responseJSON && xhr.responseJSON.errors) ? Object.values(xhr.responseJSON.errors).flat().join(' ') : 'Error saving.';
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({ icon: 'error', title: 'Error', text: msg });
                    } else {
                        alert(msg);
                    }
                },
                complete: function() {
                    $btn.prop('disabled', false);
                }
            });
        });
        
        $searchInput.on('input', function() {
            clearTimeout(searchTimeout);
            const search = $(this).val().trim();
            
            searchTimeout = setTimeout(function() {
                if (search.length === 0) {
                    window.location.href = '{{ route("admin.customers.index") }}';
                    return;
                }
                
                $tableContainer.html('<div class="text-center p-4"><div class="spinner-border text-primary" role="status"></div><p class="mt-2">Searching...</p></div>');
                
                $.ajax({
                    url: '{{ route("admin.customers.index") }}',
                    method: 'GET',
                    data: { search: search },
                    success: function(html) {
                        const $html = $(html);
                        const $newTable = $html.find('#tableContainer');
                        if ($newTable.length) {
                            $tableContainer.html($newTable.html());
                            // Reinitialize tooltips for new content
                            $tableContainer.find('[data-bs-toggle="tooltip"]').each(function() {
                                new bootstrap.Tooltip(this);
                            });
                        }
                    },
                    error: function() {
                        $tableContainer.html('<div class="alert alert-danger">Error loading results</div>');
                    }
                });
            }, 500);
        });
    });
    
    function confirmDelete(id, name) {
        Swal.fire({
            title: 'Are you sure?',
            html: '<p>You want to delete customer <strong>' + name + '</strong>?</p><p class="text-danger"><small>This action cannot be undone.</small></p>',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                $('#delete-form-' + id).submit();
            }
        });
    }
</script>
@endsection
