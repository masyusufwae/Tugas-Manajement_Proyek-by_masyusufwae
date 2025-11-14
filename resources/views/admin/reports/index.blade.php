@extends('admin.sidebar')

@section('title', 'Report Generation')

@section('content')
    
        
            
                
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2><i class="bi bi-file-earmark-text"></i> Report Generation</h2>
                    </div>

                    <!-- General Report -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-primary text-white">
                            <h5 class="card-title mb-0">
                                <i class="bi bi-file-earmark-text"></i> Generate Laporan Umum
                            </h5>
                        </div>
                        <div class="card-body">
                            <form id="generalReportForm" action="{{ route('admin.reports.general') }}" method="POST">
                                @csrf
                                <div class="row justify-content-center">
                                    <div class="col-md-10">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="start_date" class="form-label">Tanggal Mulai (Opsional)</label>
                                                    <input type="date" class="form-control" id="start_date" name="start_date">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="end_date" class="form-label">Tanggal Selesai (Opsional)</label>
                                                    <input type="date" class="form-control" id="end_date" name="end_date">
                                                </div>
                                            </div>
                                        </div>

                                        <div class="mb-4">
                                            <label class="form-label fw-bold">Format Laporan</label>
                                            <div class="d-flex justify-content-center gap-4">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="format" id="pdf_general" value="pdf" checked>
                                                    <label class="form-check-label d-flex align-items-center gap-2" for="pdf_general">
                                                        <i class="bi bi-file-earmark-pdf text-danger fs-5"></i> PDF
                                                    </label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="format" id="excel_general" value="excel">
                                                    <label class="form-check-label d-flex align-items-center gap-2" for="excel_general">
                                                        <i class="bi bi-file-earmark-excel text-success fs-5"></i> Excel (CSV)
                                                    </label>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="d-grid gap-2 d-md-flex justify-content-center mt-4">
                                            <button type="submit" class="btn btn-primary btn-lg px-5">
                                                <i class="bi bi-download me-2"></i> Generate Laporan Umum
                                            </button>
                                            <button type="button" class="btn btn-secondary btn-lg px-5" onclick="printReport()">
                                                <i class="bi bi-printer me-2"></i> Print
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Recent Reports -->
                    <div class="card shadow-sm">
                        <div class="card-header bg-primary text-white">
                            <h5 class="card-title mb-0">
                                <i class="bi bi-clock-history"></i> Recent Reports
                            </h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th class="ps-4">Report Type</th>
                                            <th>Generated At</th>
                                            <th>Status</th>
                                            <th class="pe-4">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td class="ps-4">
                                                <span class="badge bg-primary">Project Report</span>
                                                <small class="text-muted d-block mt-1">Website Redesign Project</small>
                                            </td>
                                            <td>{{ now()->subHours(2)->format('Y-m-d H:i:s') }}</td>
                                            <td><span class="badge bg-success">Completed</span></td>
                                            <td class="pe-4">
                                                <button class="btn btn-sm btn-outline-primary">
                                                    <i class="bi bi-download me-1"></i> Download
                                                </button>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="ps-4">
                                                <span class="badge bg-success">Team Report</span>
                                                <small class="text-muted d-block mt-1">All Teams</small>
                                            </td>
                                            <td>{{ now()->subDays(1)->format('Y-m-d H:i:s') }}</td>
                                            <td><span class="badge bg-success">Completed</span></td>
                                            <td class="pe-4">
                                                <button class="btn btn-sm btn-outline-primary">
                                                    <i class="bi bi-download me-1"></i> Download
                                                </button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
         
         
    

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Set max date for end date inputs
        document.addEventListener('DOMContentLoaded', function() {
            const today = new Date().toISOString().split('T')[0];
            
            document.getElementById('end_date').setAttribute('max', today);
            
            // Set end date max based on start date
            document.getElementById('start_date').addEventListener('change', function() {
                document.getElementById('end_date').setAttribute('min', this.value);
            });
        });

        function printReport() {
            const form = document.getElementById('generalReportForm');
            const formData = new FormData(form);
            const params = new URLSearchParams();

            formData.set('format', 'pdf');
            formData.set('print', '1');

            formData.forEach((value, key) => {
                if (key === '_token' || !value) {
                    return;
                }

                params.append(key, value);
            });

            window.open(`{{ route('admin.reports.general') }}?${params.toString()}`, '_blank');
        }
    </script>
@endsection