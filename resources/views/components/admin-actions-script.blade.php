@props([
    'opportunityId' => null,
    'routes' => []
])

<script>
// Admin Actions JavaScript Functions
function processMerchandiseDelivery(opportunityId) {
    Swal.fire({
        title: 'تأكيد العملية',
        text: 'هل أنت متأكد من وضع علامة وصول البضائع لهذه الفرصة؟',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'نعم، تأكيد',
        cancelButtonText: 'إلغاء',
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33'
    }).then((result) => {
        if (result.isConfirmed) {
            // Show loading
            Swal.fire({
                title: 'جاري المعالجة...',
                text: 'يرجى الانتظار',
                allowOutsideClick: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            // Make AJAX request
            fetch(`{{ $routes['merchandise_delivery'] ?? '/admin/investment-opportunities' }}/${opportunityId}/process-merchandise-delivery`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json',
                },
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        title: 'تم بنجاح!',
                        text: data.message,
                        icon: 'success',
                        confirmButtonText: 'حسناً'
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        title: 'خطأ!',
                        text: data.message,
                        icon: 'error',
                        confirmButtonText: 'حسناً'
                    });
                }
            })
            .catch(error => {
                Swal.fire({
                    title: 'خطأ!',
                    text: 'حدث خطأ أثناء معالجة الطلب',
                    icon: 'error',
                    confirmButtonText: 'حسناً'
                });
            });
        }
    });
}

function recordActualProfit(opportunityId) {
    Swal.fire({
        title: 'تسجيل الربح الفعلي',
        html: `
            <div class="mb-3">
                <label class="form-label">الربح الفعلي لكل سهم</label>
                <input type="number" id="actual_profit_per_share" class="form-control" step="0.01" placeholder="0.00">
            </div>
            <div class="mb-3">
                <label class="form-label">صافي الربح الفعلي لكل سهم</label>
                <input type="number" id="actual_net_profit_per_share" class="form-control" step="0.01" placeholder="0.00">
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: 'تسجيل',
        cancelButtonText: 'إلغاء',
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        preConfirm: () => {
            const actualProfit = document.getElementById('actual_profit_per_share').value;
            const actualNetProfit = document.getElementById('actual_net_profit_per_share').value;

            if (!actualProfit || !actualNetProfit) {
                Swal.showValidationMessage('يرجى ملء جميع الحقول');
                return false;
            }

            return {
                actual_profit_per_share: parseFloat(actualProfit),
                actual_net_profit_per_share: parseFloat(actualNetProfit)
            };
        }
    }).then((result) => {
        if (result.isConfirmed) {
            // Show loading
            Swal.fire({
                title: 'جاري التسجيل...',
                text: 'يرجى الانتظار',
                allowOutsideClick: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            // Make AJAX request
            fetch(`{{ $routes['record_profit'] ?? '/admin/investment-opportunities' }}/${opportunityId}/record-actual-profit`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(result.value)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        title: 'تم بنجاح!',
                        text: data.message,
                        icon: 'success',
                        confirmButtonText: 'حسناً'
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        title: 'خطأ!',
                        text: data.message,
                        icon: 'error',
                        confirmButtonText: 'حسناً'
                    });
                }
            })
            .catch(error => {
                Swal.fire({
                    title: 'خطأ!',
                    text: 'حدث خطأ أثناء معالجة الطلب',
                    icon: 'error',
                    confirmButtonText: 'حسناً'
                });
            });
        }
    });
}

function distributeReturns(opportunityId) {
    Swal.fire({
        title: 'تأكيد العملية',
        text: 'هل أنت متأكد من توزيع العوائد لهذه الفرصة؟',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'نعم، تأكيد',
        cancelButtonText: 'إلغاء',
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33'
    }).then((result) => {
        if (result.isConfirmed) {
            // Show loading
            Swal.fire({
                title: 'جاري التوزيع...',
                text: 'يرجى الانتظار',
                allowOutsideClick: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            // Make AJAX request
            fetch(`{{ $routes['distribute_returns'] ?? '/admin/investment-opportunities' }}/${opportunityId}/distribute-returns`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json',
                },
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        title: 'تم بنجاح!',
                        text: data.message,
                        icon: 'success',
                        confirmButtonText: 'حسناً'
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        title: 'خطأ!',
                        text: data.message,
                        icon: 'error',
                        confirmButtonText: 'حسناً'
                    });
                }
            })
            .catch(error => {
                Swal.fire({
                    title: 'خطأ!',
                    text: 'حدث خطأ أثناء معالجة الطلب',
                    icon: 'error',
                    confirmButtonText: 'حسناً'
                });
            });
        }
    });
}

function viewMerchandiseStatus(opportunityId) {
    // Open merchandise status modal or redirect
    window.open(`{{ $routes['merchandise_status'] ?? '/admin/investment-opportunities' }}/${opportunityId}/merchandise-status`, '_blank');
}

function viewReturnsStatus(opportunityId) {
    // Open returns status modal or redirect
    window.open(`{{ $routes['returns_status'] ?? '/admin/investment-opportunities' }}/${opportunityId}/returns-status`, '_blank');
}
</script>
