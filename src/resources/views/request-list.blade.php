@extends('layouts.app')

@section('title', '申請一覧')

@push('css')
    <link rel="stylesheet" href="{{ asset('css/request-list.css') }}">
@endpush

@section('content')

    <main class="wrapper">

        <section class="tab-section">
            <h1>申請一覧</h1>
            <input id="pending" type="radio" name="tab-item" {{ request('tab') !== 'approved' ? 'checked' : '' }}>
            <label class="tab-item" for="pending" onclick="changeTab('pending')">承認待ち</label>

            <input id="approved" type="radio" name="tab-item" {{ request('tab') === 'approved' ? 'checked' : '' }}>
            <label class="tab-item" for="approved" onclick="changeTab('approved')">承認済み</label>
        </section>

        <section class="request-list-section">
            <!-- 承認待ちタブ -->
            <div class="tab-content" id="pending-content"
                style="{{ request('tab') !== 'approved' ? 'display:block;' : 'display:none;' }}">
                <table class="request-table">
                    <thead>
                        <tr>
                            <th>状態</th>
                            <th>名前</th>
                            <th>対象日時</th>
                            <th>申請理由</th>
                            <th>申請日時</th>
                            <th>詳細</th>
                        </tr>
                    </thead>
                    @foreach ($pendingCorrections as $pendingCorrection)
                        <tbody>
                            <tr>
                                <td>{{ $pendingCorrection['correction_status_id'] }}</td>
                                <td>{{ $pendingCorrection['name'] }}</td>
                                <td>{{ $pendingCorrection['old_date'] }}</td>
                                <td>{{ $pendingCorrection['reason'] }}</td>
                                <td>{{ $pendingCorrection['request_date'] }}</td>
                                <td>
                                    @if (auth()->user()->role_id === \App\Models\User::ROLE_ADMIN)
                                        <a href="{{ route('correction.show', ['id' => $pendingCorrection['correction_id']]) }}" class="details-link">詳細</a>
                                    @elseif(auth()->user()->role_id === \App\Models\User::ROLE_GENERAL)
                                        <a href="{{ route('attendance.detail', ['attendance_id' => $pendingCorrection['attendance_id']]) }}" class="details-link">詳細</a>
                                    @endif
                                </td>
                            </tr>
                        </tbody>
                    @endforeach
                </table>
            </div>
            <!-- 承認済みタブ -->
            <div class="tab-content" id="approved-content"
                style="{{ request('tab') === 'approved' ? 'display:block;' : 'display:none;' }}">
                <table class="request-table">
                    <thead>
                        <tr>
                            <th>状態</th>
                            <th>名前</th>
                            <th>対象日時</th>
                            <th>申請理由</th>
                            <th>申請日時</th>
                            <th>詳細</th>
                        </tr>
                    </thead>
                    @foreach ($approvedCorrections as $approvedCorrection)
                        <tbody>
                            <tr>
                                <td>{{ $approvedCorrection['correction_status_id'] }}</td>
                                <td>{{ $approvedCorrection['name'] }}</td>
                                <td>{{ $approvedCorrection['old_date'] }}</td>
                                <td>{{ $approvedCorrection['reason'] }}</td>
                                <td>{{ $approvedCorrection['request_date'] }}</td>
                                <td>
                                    @if (auth()->user()->role_id === \App\Models\User::ROLE_ADMIN)
                                        <a href="{{ route('correction.show', ['id' => $approvedCorrection['correction_id']]) }}" class="details-link">詳細</a>
                                    @elseif(auth()->user()->role_id === \App\Models\User::ROLE_GENERAL)
                                        <a href="{{ route('attendance.detail', ['attendance_id' => $approvedCorrection['attendance_id']]) }}" class="details-link">詳細</a>
                                    @endif
                                </td>
                            </tr>
                        </tbody>
                    @endforeach
                </table>
            </div>
        </section>
    </main>

    <script>
        function changeTab(tabName) {
            const url = new URL(window.location.href);

            if (tabName === 'pending') {
                url.searchParams.delete('tab');
            } else {
                url.searchParams.set('tab', 'approved');
            }

            window.location.href = url.href;

            document.getElementById('pending-content').style.display = (tabName === 'pending') ? 'block' : 'none';
            document.getElementById('approved-content').style.display = (tabName === 'approved') ? 'block' : 'none';
        }
    </script>
@endsection
