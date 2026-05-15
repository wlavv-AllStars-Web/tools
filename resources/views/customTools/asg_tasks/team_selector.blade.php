@if($isAdmin)
<div class="team-selector">
    <div class="row">
        <div class="col-lg-12">
            @php
                $icons = [
                    1 => 'fa-solid fa-chart-line',      // ACCOUNTING
                    2 => 'fa-solid fa-people-roof',              // ADMIN
                    3 => 'fa-solid fa-database',          // DATA
                    4 => 'fa-solid fa-box',             // LOGISTICS
                    5 => 'fa-solid fa-bullhorn',          // MARKETING
                    6 => 'fa-solid fa-comments-dollar',     // PURCHASE
                    7 => 'fa-solid fa-dollar-sign',        // SALES
                    8 => 'fa-solid fa-car',             // SHOP
                    9 => 'fa-solid fa-code',             // WEB
                ];
            @endphp
            <div class="team-buttons">
                @foreach($departments as $id => $label)
                    @php
                        $qs = [ 'team'  => $id, 'year'  => $year  ?? (int) now()->format('Y'), 'month' => $month ?? (int) now()->format('n') ];
                        if (!empty($week)) $qs['week'] = (int) $week;
                    @endphp

                    <a href="{{ route($tasksIndexRouteName ?? 'asg_tasks.index', $qs) }}" class="dept-pill {{ (int)$id === (int)$teamId ? 'dept-pill-active' : '' }}" title="{{ $label }}">
                        <div class="dept-icon"> <i class="{{ $icons[$id] ?? 'fa-solid fa-circle' }}"></i> </div>
                        <div class="dept-label"> {{ $label }} </div>
                    </a>
                @endforeach
            </div>
        </div>
    </div>
</div>

<style>
    .team-buttons { display: flex; justify-content: center; align-items: center; flex-wrap: wrap; gap: 18px; margin: 10px auto; }
    .dept-pill { display: flex; flex-direction: column; align-items: center; justify-content: center; width: 110px; padding: 12px 8px; text-decoration: none; border-radius: 12px; transition: all .25s ease; color: #6b7280; }
    .dept-icon i { font-size: 38px; margin-bottom: 8px; margin-top: 5px; }
    .dept-label { font-size: 12px; font-weight: 600; letter-spacing: .5px; text-align: center; }
    .dept-pill:hover { background: #f3f4f6; color: #111827; }
    .dept-pill-active { background: #eff6ff; color: #2563eb; }
    .dept-pill-active i { transform: scale(1.15); }    
</style>
@endif
