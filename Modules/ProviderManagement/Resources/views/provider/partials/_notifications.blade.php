@foreach($notifications as $notification)
    <a href="#" class="dropdown-item-text align-items-center media gap-3">
        <div class="avatar title-color hover-color-c2">
            <span class="material-icons">notifications</span>
        </div>
        <div class="media-body ">
            <img src="{{$notification->cover_image_full_path}}"
                 class="avatar avatar-lg min-w-50px rounded-circle" alt="{{translate('image')}}">
            <div class="">
                <h5 class="card-title mb-0 line-limit-1">{{$notification->title}}</h5>
                <p class="card-text fs-12 mb-1 line-limit-2">{{$notification->description}}</p>
                @php
                    $to_time = strtotime($notification->created_at);
                    $from_time = strtotime(now());
                    $diff = round(abs($to_time - $from_time) / 60,2);
                    $time = translate(':count min', ['count' => $diff]);
                    if ($diff>60){
                        $diff = round($diff/60);
                        $time = translate(':count hr', ['count' => $diff]);
                        if ($diff>24){
                            $diff = round($diff/24);
                            $time = translate(':count day', ['count' => $diff]);
                             if ($diff>30){
                                $diff = round($diff/30);
                                $time = translate(':count month', ['count' => $diff]);
                                 if ($diff>12){
                                    $diff = round($diff/12);
                                    $time = translate(':count year', ['count' => $diff]);
                                }
                            }
                        }
                    }
                @endphp
                <span class="card-text fz-12 text-opacity-75">{{ translate(':time ago', ['time' => $time]) }}</span>
            </div>
        </div>
    </a>
    <div class="dropdown-divider"></div>
@endforeach
