@inject('_settings', 'globalSettings')
@if (($_settings->getGlobal('replicated_site', 'show')) || (Auth::user()->hasRole(['Superadmin', 'Admin'])))
<div class="cp-announcements">
    <div class="cp-annnouncement-wrapper">
        <h4 class="panel-title"></i>{{ $_settings->getGlobal('title_announcement', 'value') }}</h4>
        <ul>
        @foreach ($recent_announcements as $recent_announcement)
            <li><a href="/announcements/{{ $recent_announcement->id }}"><h3 class="no-top no-bottom">{{ $recent_announcement->title }}</h3><span>{{ $recent_announcement->updated_at->format('M d y') }}</span></a></li>
        @endforeach
        </ul>
    </div>
</div>
@else
    <div class="cp-annnouncement-wrapper" style="width:45%">
        <h4 class="panel-title"></i>{{ $_settings->getGlobal('title_announcement', 'value') }}</h4>
        <ul>
        @foreach ($recent_announcements as $recent_announcement)
            <li><a href="/announcements/{{ $recent_announcement->id }}"><h3 class="no-top no-bottom">{{ $recent_announcement->title }}</h3><span>{{ $recent_announcement->updated_at->format('M d y') }}</span></a></li>
        @endforeach
        </ul>
    </div>
@endif
