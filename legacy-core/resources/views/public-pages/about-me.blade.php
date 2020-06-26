@extends('layouts.store')
@section('title')
    About Me
@endsection
@section('content')
    <div class="about-me-wrapper">
        <div class="my-story">
            @if(session()->has('store_owner'))
                <span>MY STORY</span>
            @else
                <span>OUR STORY</span>
            @endif
        </div>
        <div class="images-wrapper">
            <div class="cp-left-col main-img">
                <img class="main-img" src="{{ $settings['story_profile_image'] }}" alt="profile"/>
                <div class="main-mobile-view">
                    <p>{{ $settings['story_title'] }}</p>
                    <p>{{ $settings['story_intro']}}</p>
                </div>
            </div>
            <div class="cp-left-col grid">
                <div class="grid-img-wrapper">
                    <div class="mobile-grid">
                        <div class="grid-item top">
                             <img class="img-grid" src="{{ $settings['story_grid_image_1'] }}" alt="{{ $settings['story_heading_1'] }}"/>
                            <h5>{{ $settings['story_heading_1'] }}</h5>
                         </div>
                        <div class="mobile-view">
                            <p>{{ $settings['story_heading_1'] }}</p>
                            <p>{{ $settings['story_text_1'] }}</p>
                        </div>
                     </div>
                     <div class="mobile-grid">
                         <div class="grid-item top">
                             <img class="img-grid" src="{{ $settings['story_grid_image_2'] }}" alt="{{ $settings['story_heading_2'] }}"/>
                            <h5>{{ $settings['story_heading_2'] }}</h5>
                         </div>
                            <div class="mobile-view">
                                <p>{{ $settings['story_heading_2'] }}</p>
                                <p>{{ $settings['story_text_2'] }}</p>
                            </div>
                    </div>
                    <div class="mobile-grid">
                         <div class="grid-item">
                             <img class="img-grid" src="{{ $settings['story_grid_image_3'] }}" alt="{{ $settings['story_heading_3'] }}"/>
                            <h5>{{ $settings['story_heading_3'] }}</h5>
                        </div>
                        <div class="mobile-view">
                            <p>{{ $settings['story_heading_3'] }}</p>
                            <p>{{ $settings['story_text_3'] }}</p>
                        </div>
                    </div>
                    <div class="mobile-grid">
                         <div class="grid-item">
                             <img class="img-grid" src="{{ $settings['story_grid_image_4'] }}" alt="{{ $settings['story_heading_4'] }}"/>
                            <h5>{{ $settings['story_heading_4'] }}</h5>
                        </div>
                        <div class="mobile-view">
                            <p>{{ $settings['story_heading_4'] }}</p>
                            <p>{{ $settings['story_text_4'] }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="life-wrapper">
            <div class="desktop-view">
                <span>{{ $settings['story_title']}}</span>
                <p>{{ $settings['story_intro']}}</p>
            </div>
            <div class="desktop-view">
                <span>{{ $settings['story_heading_1']}}</span>
                <p>{{ $settings['story_text_1'] }}</p>
            </div>
            <div class="desktop-view">
                <span>{{ $settings['story_heading_2']}}</span>
                <p>{{ $settings['story_text_2'] }}</p>
            </div>
            <div class="desktop-view">
                <span>{{ $settings['story_heading_3']}}</span>
                <p>{{ $settings['story_text_3'] }}</p>
            </div>
            <div class="desktop-view">
                <span>{{ $settings['story_heading_4']}}</span>
                <p>{{ $settings['story_text_4'] }}</p>
            </div>
        </div>
    </div>
@endsection
