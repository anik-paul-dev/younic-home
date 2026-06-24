@extends('layouts.app')
@section('title', 'Announcements')
@section('header_title', 'Broadcast Announcements')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    <!-- Broadcast Form -->
    <div class="glass-card p-6">
        <h3 class="text-lg font-semibold text-white mb-4 flex items-center">
            <svg class="w-5 h-5 mr-2 text-teal-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"></path></svg>
            Send Announcement
        </h3>
        <p class="text-sm text-slate-400 mb-6">This will send a real-time notification to all users.</p>
        
        <form action="{{ route('admin.announcements.send') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="form-label">Subject</label>
                <input type="text" name="title" class="input-field" required placeholder="e.g. Server Maintenance Notice">
            </div>
            <div>
                <label class="form-label">Message</label>
                <textarea name="message" rows="5" class="input-field resize-none" required placeholder="Type your message here..."></textarea>
            </div>
            <button type="submit" class="w-full btn-primary py-3">Broadcast Now</button>
        </form>
    </div>

    <!-- Announcement History -->
    <div class="lg:col-span-2 glass-card p-6">
        <h3 class="text-lg font-semibold text-white mb-4">Past Announcements</h3>
        
        @if($announcements->isEmpty())
            <div class="text-slate-400 text-center py-8">
                No announcements have been sent yet.
            </div>
        @else
            <div class="space-y-4">
                @foreach($announcements as $announcement)
                    <div class="p-4 bg-slate-900/40 rounded-lg border border-slate-700">
                        <div class="flex justify-between items-start mb-2">
                            <h4 class="font-medium text-slate-200">{{ $announcement->title }}</h4>
                            <span class="text-xs text-slate-500">{{ $announcement->created_at->format('M d, Y · h:i A') }}</span>
                        </div>
                        <p class="text-sm text-slate-400">{{ $announcement->message }}</p>
                    </div>
                @endforeach
            </div>
            
            <div class="mt-4">
                {{ $announcements->links('pagination::tailwind') }}
            </div>
        @endif
    </div>

</div>
@endsection
