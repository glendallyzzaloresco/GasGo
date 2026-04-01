@extends('layouts.app')

@section('title', 'Restocks Management')

@section('content')
<div class="container-fluid py-5">
    <div class="row mb-4">
        <div class="col-md-8">
            <h1 class="h3 mb-0">
                <i class="fas fa-box me-2"></i> Restock Management
            </h1>
        </div>
        <div class="col-md-4 text-end">
            <a href="{{ route('admin.restock.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i> Create Restock
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Restocks Table -->
    <div class="card shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Supplier</th>
                        <th class="text-center">Items</th>
                        <th>Status</th>
                        <th>Created By</th>
                        <th>Created Date</th>
                        <th>Received Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($restocks as $restock)
                        <tr>
                            <td>
                                <strong>#{{ $restock->id }}</strong>
                            </td>
                            <td>{{ $restock->supplier_name ?? '—' }}</td>
                            <td class="text-center">
                                <span class="badge bg-light text-dark">{{ $restock->items->count() }}</span>
                            </td>
                            <td>
                                @if($restock->status === 'DRAFT')
                                    <span class="badge bg-warning">Draft</span>
                                @else
                                    <span class="badge bg-success">Received</span>
                                @endif
                            </td>
                            <td>{{ $restock->creator->name ?? '—' }}</td>
                            <td>{{ $restock->created_at->format('M d, Y H:i') }}</td>
                            <td>
                                @if($restock->received_at)
                                    {{ $restock->received_at->format('M d, Y H:i') }}
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm" role="group">
                                    <a href="{{ route('admin.restock.show', $restock) }}" class="btn btn-outline-primary" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @if($restock->status === 'DRAFT')
                                        <a href="{{ route('admin.restock.edit', $restock) }}" class="btn btn-outline-secondary" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('admin.restock.destroy', $restock) }}" method="POST" style="display: inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger" title="Delete" 
                                                onclick="return confirm('Delete this restock?')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">
                                No restocks found. <a href="{{ route('admin.restock.create') }}">Create one</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($restocks->hasPages())
            <div class="card-footer">
                {{ $restocks->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
