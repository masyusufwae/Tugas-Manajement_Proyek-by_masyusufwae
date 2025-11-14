@extends('admin.sidebar')

@section('title', 'Monitoring Proyek')

@section('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #894949;
            --secondary: #B2704E;
            --accent: #CD9D77;
            --light: #FCC0C0;
            --dark: #5A2E2E;
            --gray: #8D7B7B;
            --border-radius: 12px;
            --box-shadow: 0 6px 15px rgba(137, 73, 73, 0.15);
            --gradient: linear-gradient(135deg, var(--primary), var(--secondary));
        }
        
        .monitoring-container {
            padding: 20px;
            background: linear-gradient(135deg, #FEF7F7 0%, #FCE8E8 100%);
            min-height: calc(100vh - 60px);
        }
        
        /* Header Styles */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 40px;
            padding: 25px 30px;
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            border-left: 6px solid var(--primary);
        }
        
        .header-content h1 {
            font-size: 32px;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 5px;
        }
        
        .header-content p {
            color: var(--gray);
            font-size: 16px;
            margin: 0;
        }
        
        .section-title {
            font-size: 22px;
            font-weight: 700;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            color: var(--primary);
        }
        
        .section-title i {
            margin-right: 12px;
            color: var(--accent);
            background: var(--light);
            padding: 10px;
            border-radius: 10px;
        }
        
        /* Projects Table */
        .projects-table {
            background: white;
            border-radius: var(--border-radius);
            padding: 30px;
            box-shadow: var(--box-shadow);
            margin-bottom: 40px;
            overflow-x: auto;
            border-left: 6px solid var(--secondary);
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 800px;
        }
        
        th, td {
            padding: 16px 20px;
            text-align: left;
            border-bottom: 1px solid #F5E6E6;
        }
        
        th {
            background-color: var(--light);
            font-weight: 600;
            color: var(--primary);
            font-size: 15px;
            position: sticky;
            top: 0;
        }
        
        tr:hover {
            background-color: #FEF7F7;
        }
        
        .status {
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
            display: inline-block;
            text-align: center;
            min-width: 100px;
        }
        
        .status.todo {
            background-color: #FFE8E8;
            color: var(--primary);
            border: 1px solid var(--light);
        }
        
        .status.in-progress {
            background-color: #FFF4E8;
            color: #B2704E;
            border: 1px solid #F0D9C8;
        }
        
        .status.done {
            background-color: #E8F5E8;
            color: #5A8E5A;
            border: 1px solid #D0E6D0;
        }
        
        .loading {
            text-align: center;
            padding: 40px;
            color: var(--gray);
        }
        
        .loading i {
            font-size: 24px;
            margin-bottom: 10px;
            color: var(--accent);
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .monitoring-container {
                padding: 15px;
            }
            
            .header {
                flex-direction: column;
                text-align: center;
                gap: 20px;
            }
            
            .projects-table {
                overflow-x: auto;
            }
        }
    </style>
@endsection

@section('content')
    <!-- Main Content -->
    <div class="monitoring-container">
        <!-- Header -->
        <div class="header">
            <div class="header-content">
                <h1><i class="fas fa-chart-line"></i> Monitoring Manajemen Proyek</h1>
                <p>Daftar semua proyek dalam sistem</p>
            </div>
        </div>
        
        <!-- Projects Table -->
        <div class="projects-table">
            <div class="section-title">
                <i class="fas fa-table"></i>
                <span>Semua Proyek</span>
            </div>
            
            <div class="loading" id="table-loading">
                <i class="fas fa-spinner fa-spin"></i>
                <div>Memuat data proyek...</div>
            </div>
            
            <table style="display: none;" id="projects-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nama Proyek</th>
                        <th>Status</th>
                        <th>Tenggat Waktu</th>
                        <th>Cards</th>
                        <th>Boards</th>
                        <th>Members</th>
                        <th>Perkiraan Jam</th>
                        <th>Jam Aktual</th>
                        <th>Progress</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody id="projects-table-body">
                    <!-- Data akan diisi oleh JavaScript -->
                </tbody>
            </table>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        // Format status untuk tampilan
        function formatStatus(status) {
            const statusMap = {
                'todo': 'To Do',
                'in-progress': 'In Progress',
                'done': 'Done'
            };
            return statusMap[status] || status;
        }
        
        // Load projects table
        async function loadProjects() {
            try {
                // Try both route names in case of routing issues
                let response;
                try {
                    response = await fetch('{{ route("admin.monitoring.projects") }}');
                } catch (e) {
                    // Fallback to direct URL
                    response = await fetch('/admin/monitoring/projects');
                }
                
                if (!response.ok) {
                    throw new Error('Failed to fetch projects');
                }
                
                const projects = await response.json();
                
                const tableBody = document.getElementById('projects-table-body');
                const loadingElement = document.getElementById('table-loading');
                const tableElement = document.getElementById('projects-table');
                
                tableBody.innerHTML = '';
                
                if (projects.length === 0) {
                    tableBody.innerHTML = '<tr><td colspan="11" style="text-align: center; padding: 40px; color: var(--gray);">Tidak ada proyek ditemukan</td></tr>';
                    loadingElement.style.display = 'none';
                    tableElement.style.display = 'table';
                    return;
                }
                
                projects.forEach(project => {
                    const row = document.createElement('tr');
                    
                    // Format date if exists
                    const dueDate = project.deadline ? 
                        new Date(project.deadline + 'T00:00:00').toLocaleDateString('id-ID', {
                            day: '2-digit',
                            month: '2-digit',
                            year: 'numeric'
                        }) : 'N/A';
                    
                    // Format status badge with overdue indicator
                    let statusClass = project.status;
                    let statusText = formatStatus(project.status);
                    if (project.is_overdue && project.status !== 'done') {
                        statusText += ' <i class="fas fa-exclamation-triangle" style="color: #E74C3C; margin-left: 5px;"></i>';
                    }
                    
                    // Cards breakdown
                    const cardsInfo = `
                        <div style="font-size: 12px;">
                            <span style="color: #27AE60;">✓ ${project.done_cards || 0}</span> |
                            <span style="color: #F39C12;">↻ ${project.in_progress_cards || 0}</span> |
                            <span style="color: #95A5A6;">○ ${project.todo_cards || 0}</span>
                            <br>
                            <small style="color: var(--gray);">Total: ${project.total_cards || 0}</small>
                        </div>
                    `;
                    
                    // Members list
                    const membersList = project.members && project.members.length > 0 
                        ? project.members.map(m => m.name).join(', ') 
                        : 'Tidak ada anggota';
                    const membersDisplay = project.total_members > 0 
                        ? `<span title="${membersList}">${project.total_members} anggota</span>`
                        : '0 anggota';
                    
                    row.innerHTML = `
                        <td>${project.id}</td>
                        <td>
                            <strong style="color: var(--primary);">${project.title || 'N/A'}</strong>
                            ${project.description ? `<br><small style="color: var(--gray); font-size: 12px;">${project.description.substring(0, 50)}${project.description.length > 50 ? '...' : ''}</small>` : ''}
                        </td>
                        <td><span class="status ${statusClass}">${statusText}</span></td>
                        <td>
                            ${dueDate}
                            ${project.is_overdue && project.status !== 'done' ? '<br><small style="color: #E74C3C; font-weight: 600;">Terlambat</small>' : ''}
                        </td>
                        <td>${cardsInfo}</td>
                        <td>
                            <span style="font-weight: 600; color: var(--primary);">${project.total_boards || 0}</span>
                        </td>
                        <td>
                            <span title="${membersList}" style="cursor: help;">${membersDisplay}</span>
                        </td>
                        <td>${project.estimated_hours ? project.estimated_hours.toFixed(2) : '0.00'}h</td>
                        <td>${project.actual_hours ? project.actual_hours.toFixed(2) : '0.00'}h</td>
                        <td>
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <div style="flex: 1; height: 10px; background: #F5E6E6; border-radius: 5px; overflow: hidden; min-width: 80px;">
                                    <div style="height: 100%; width: ${project.progress}%; background: var(--gradient); border-radius: 5px; transition: width 0.5s ease;"></div>
                                </div>
                                <span style="font-weight: 600; color: var(--primary); min-width: 45px; font-size: 13px;">${project.progress.toFixed(1)}%</span>
                            </div>
                        </td>
                        <td>
                            <a href="{{ url('/projects') }}/${project.id}" class="btn btn-sm" style="background: var(--primary); color: white; padding: 5px 12px; border-radius: 6px; text-decoration: none; font-size: 12px;">
                                <i class="fas fa-eye"></i> Detail
                            </a>
                        </td>
                    `;
                    
                    tableBody.appendChild(row);
                });
                
                // Hide loading and show table
                loadingElement.style.display = 'none';
                tableElement.style.display = 'table';
                
            } catch (error) {
                console.error('Error loading projects:', error);
                document.getElementById('table-loading').innerHTML = 
                    '<div style="color: #E74C3C; text-align: center;"><i class="fas fa-exclamation-triangle"></i> Error memuat data proyek: ' + error.message + '</div>';
            }
        }
        
        // Initialize dashboard
        document.addEventListener('DOMContentLoaded', function() {
            loadProjects();
            
            // Refresh data every 30 seconds
            setInterval(() => {
                loadProjects();
            }, 30000);
        });
    </script>
@endsection