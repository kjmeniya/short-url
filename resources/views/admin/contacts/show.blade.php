@extends('admin.layout.master')

@section('title', $title ?? 'Contact Details')
@section('description', $description ?? 'View contact message details')
@section('keywords', $keywords ?? 'contact details, message')

@section('content')
<nav class="page-breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
        <li class="breadcrumb-item"><a href="{{ route('admin.contacts.index') }}">Contacts</a></li>
        <li class="breadcrumb-item active" aria-current="page">Contact Details</li>
    </ol>
</nav>

<div class="row">
    <div class="col-md-12">
        @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Contact Message Details</h5>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.contacts.index') }}" class="btn btn-sm btn-secondary">
                        <i data-lucide="arrow-left" class="icon-sm me-1"></i>Back to List
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-6">
                        <h6 class="text-muted mb-2">Contact Information</h6>
                        <table class="table table-sm">
                            <tr>
                                <th width="150">Name:</th>
                                <td><strong>{{ $contact->name }}</strong></td>
                            </tr>
                            <tr>
                                <th>Email:</th>
                                <td><a href="mailto:{{ $contact->email }}">{{ $contact->email }}</a></td>
                            </tr>
                            <tr>
                                <th>Status:</th>
                                <td>{!! $contact->status_badge !!}</td>
                            </tr>
                            <tr>
                                <th>Spam Status:</th>
                                <td>{!! $contact->spam_badge !!}</td>
                            </tr>
                            <tr>
                                <th>Received:</th>
                                <td>{{ $contact->formatted_date }}</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-muted mb-2">Reply Information</h6>
                        <table class="table table-sm">
                            <tr>
                                <th width="150">Replied At:</th>
                                <td>{{ $contact->formatted_replied_date }}</td>
                            </tr>
                            <tr>
                                <th>Replied By:</th>
                                <td>{{ $contact->repliedBy?->name ?? 'N/A' }}</td>
                            </tr>
                        </table>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-12">
                        <h6 class="text-muted mb-2">Subject</h6>
                        <p class="fw-bold">{{ $contact->subject }}</p>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-12">
                        <h6 class="text-muted mb-2">Message</h6>
                        <div class="p-3 bg-light rounded">
                            <p class="mb-0" style="white-space: pre-wrap;">{{ $contact->message }}</p>
                        </div>
                    </div>
                </div>

                @if($contact->reply_message)
                <div class="row mb-4">
                    <div class="col-12">
                        <h6 class="text-muted mb-2">Reply Message</h6>
                        <div class="p-3 bg-success bg-opacity-10 rounded border border-success">
                            <p class="mb-0" style="white-space: pre-wrap;">{{ $contact->reply_message }}</p>
                        </div>
                    </div>
                </div>
                @endif

                @if(!$contact->isReplied())
                <div class="row">
                    <div class="col-12">
                        <h6 class="text-muted mb-2">Send Reply</h6>
                        <form action="{{ route('admin.contacts.reply', $contact) }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <textarea name="reply_message" class="form-control @error('reply_message') is-invalid @enderror" rows="5" placeholder="Enter your reply message..." required>{{ old('reply_message') }}</textarea>
                                @error('reply_message')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <button type="submit" class="btn btn-success">
                                <i data-lucide="send" class="icon-sm me-1"></i>Send Reply & Mark as Replied
                            </button>
                        </form>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('custom-scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    });
</script>
@endpush