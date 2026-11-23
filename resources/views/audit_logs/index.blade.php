@extends('layouts.app', ['title' => 'Audit Trail & Log Aktivitas'])

@section('content')
<div class="space-y-6">

    <div>
        <h2 class="text-2xl font-black text-slate-900 tracking-tight">Audit Trail & Log Aktivitas Sistem</h2>
        <p class="text-xs text-slate-500 mt-0.5">Rekaman setiap aksi krusial: pembuatan tagihan, penerimaan kwitansi, pembatalan/void, approval dan modifikasi data keuangan.</p>
    </div>

    <!-- Audit Logs Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-slate-500 font-bold uppercase tracking-wider">
                        <th class="py-3 px-4">Waktu</th>
                        <th class="py-3 px-4">Pengguna (Aktor)</th>
                        <th class="py-3 px-4">Aksi / Event</th>
                        <th class="py-3 px-4">Model Entitas</th>
                        <th class="py-3 px-4">Deskripsi</th>
                        <th class="py-3 px-4">Perubahan Data (Old &rarr; New)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($logs as $log)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="py-3 px-4 font-mono text-slate-500">{{ $log->created_at->translatedFormat('d/m/Y H:i:s') }}</td>
                            <td class="py-3 px-4 font-bold text-slate-900">{{ $log->causer?->name ?? 'Sistem Otomatis' }}</td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-700 uppercase">
                                    {{ $log->event ?? $log->description }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-slate-500 font-mono text-[11px]">
                                {{ class_basename($log->subject_type ?? '') }} #{{ $log->subject_id }}
                            </td>
                            <td class="py-3 px-4 text-slate-800 font-medium">{{ $log->description }}</td>
                            <td class="py-3 px-4 text-[10px] text-slate-500 font-mono max-w-xs truncate">
                                @if(!empty($log->properties))
                                    {{ json_encode($log->properties, JSON_UNESCAPED_UNICODE) }}
                                @else
                                    -
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-slate-400">Belum ada riwayat aktivitas yang tercatat.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($logs->hasPages())
            <div class="p-4 border-t border-slate-200">
                {{ $logs->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
