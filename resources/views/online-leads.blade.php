<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Website Enquiries &middot; {{ config('app.name') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen">

<div class="max-w-6xl mx-auto p-4 sm:p-8">

    <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Website Enquiries</h1>
            <p class="text-sm text-gray-500">Quote requests and contact messages sent from the public storefront.</p>
        </div>
        <a href="{{ url('/dashboard') }}" class="text-sm text-emerald-700 hover:underline">&larr; Back to dashboard</a>
    </div>

    @if (session('success'))
        <div class="mb-4 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 text-sm">
            {{ session('success') }}
        </div>
    @endif

    {{-- Filters --}}
    <div class="flex flex-wrap gap-2 mb-4 text-sm">
        @php
            $filters = [
                ['label' => 'All', 'params' => [], 'count' => $counts['all']],
                ['label' => 'Quotes', 'params' => ['type' => 'quote'], 'count' => $counts['quote']],
                ['label' => 'Contacts', 'params' => ['type' => 'contact'], 'count' => $counts['contact']],
                ['label' => 'New', 'params' => ['status' => 'new'], 'count' => $counts['new']],
            ];
        @endphp
        @foreach ($filters as $filter)
            @php
                $active = request()->query() == $filter['params'];
            @endphp
            <a href="{{ route('online-leads.index', $filter['params']) }}"
               class="px-3 py-1.5 rounded-full border {{ $active ? 'bg-emerald-600 text-white border-emerald-600' : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50' }}">
                {{ $filter['label'] }} <span class="opacity-70">({{ $filter['count'] }})</span>
            </a>
        @endforeach
    </div>

    @if ($leads->isEmpty())
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-10 text-center text-gray-500">
            No enquiries yet. They will appear here as soon as someone submits the quote form or the contact form on the website.
        </div>
    @else
        <div class="space-y-4">
            @foreach ($leads as $lead)
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="flex flex-wrap items-center justify-between gap-3 px-5 py-3 border-b border-gray-100 bg-gray-50">
                        <div class="flex items-center gap-3">
                            <span class="text-xs font-semibold uppercase tracking-wide px-2 py-1 rounded
                                {{ $lead->type === 'quote' ? 'bg-amber-100 text-amber-800' : 'bg-sky-100 text-sky-800' }}">
                                {{ $lead->type }}
                            </span>
                            <span class="text-xs px-2 py-1 rounded
                                {{ $lead->status === 'new' ? 'bg-emerald-100 text-emerald-800' : 'bg-gray-200 text-gray-700' }}">
                                {{ $lead->status }}
                            </span>
                            <span class="text-sm text-gray-500">#{{ $lead->id }} &middot; {{ $lead->created_at?->format('M j, Y H:i') }}</span>
                        </div>

                        <form method="POST" action="{{ route('online-leads.update', $lead) }}" class="flex items-center gap-2">
                            @csrf
                            @method('PATCH')
                            <select name="status" class="text-sm border-gray-300 rounded-md border px-2 py-1">
                                @foreach (['new', 'contacted', 'archived'] as $option)
                                    <option value="{{ $option }}" @selected($lead->status === $option)>{{ ucfirst($option) }}</option>
                                @endforeach
                            </select>
                            <button type="submit" class="text-sm px-3 py-1.5 rounded-md bg-emerald-600 text-white hover:bg-emerald-700">
                                Save
                            </button>
                        </form>
                    </div>

                    <div class="px-5 py-4 grid sm:grid-cols-2 gap-4">
                        <div class="space-y-1 text-sm">
                            <p><span class="text-gray-500">Name:</span> {{ $lead->name ?: '—' }}</p>
                            <p><span class="text-gray-500">Email:</span>
                                @if ($lead->email)
                                    <a href="mailto:{{ $lead->email }}" class="text-emerald-700 hover:underline">{{ $lead->email }}</a>
                                @else
                                    —
                                @endif
                            </p>
                            <p><span class="text-gray-500">Phone:</span> {{ $lead->phone ?: '—' }}</p>
                            <p><span class="text-gray-500">Subject:</span> {{ $lead->subject ?: '—' }}</p>
                            <p class="text-xs text-gray-400 pt-1">Source: {{ $lead->source }} @if ($lead->external_ref) &middot; ref {{ $lead->external_ref }} @endif</p>
                        </div>

                        <div class="text-sm">
                            <p class="text-gray-500 mb-1">Message</p>
                            <p class="text-gray-800 whitespace-pre-line">{{ $lead->message ?: '—' }}</p>

                            @if (! empty($lead->payload['items']))
                                <p class="text-gray-500 mt-3 mb-1">Requested items</p>
                                <ul class="list-disc list-inside text-gray-800">
                                    @foreach ($lead->payload['items'] as $item)
                                        <li>{{ $item['name'] ?? 'item' }} &times; {{ $item['quantity'] ?? '?' }}</li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-6">
            {{ $leads->links() }}
        </div>
    @endif
</div>

</body>
</html>
