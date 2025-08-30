<!-- Left side column. contains the logo and sidebar -->
@php
    // Map raw color names to Tailwind color hex values (using -500 shade)
    $bgColorsDark = [
        'blue'    => '#00359e', // blue-900
        'orange'  => '#772917', // orange-900
        'red'     => '#7a271a', // red-900
        'green'   => '#074d31', // green-900
        'yellow'  => '#7a2e0e ', // yellow-900
        'purple'  => '#3e1c96', // purple-900
        'pink'    => '#831843', // pink-900
        'gray'    => '#111827', // gray-900
        'sky'     => '#0b4a6f', // sky-900
        'primary' => '#00359e', // fallback if session is 'primary'
    ];


    $rawTheme = session('business.theme_color') ?? 'primary';
    $bgColor = $bgColorsDark[$rawTheme];

    $isRtl = in_array(session()->get('user.language', config('app.locale')), config('constants.langs_rtl'));
    //dd($isRtl);
@endphp
<style>
    @media (min-width: 1024px) {
        .sidebar-rounded {
            {{ $isRtl ? 'border-top-left-radius: 2rem;overflow:hidden' : 'border-top-right-radius: 2rem;overflow:hidden' }}
        }
    }
</style>
<aside class="side-bar no-print tw-relative tw-fixed tw-hidden tw-h-full tw-bg-white tw-w-64 xl:tw-w-64 lg:tw-flex lg:tw-flex-col tw-shrink-0">

   <!-- <div> -->
   <div class="tw-bg-gray-100">
        <!-- sidebar: style can be found in sidebar.less -->

        {{-- <a href="{{route('home')}}" class="logo">
            <span class="logo-lg">{{ Session::get('business.name') }}</span>
        </a> --}}

        
        <!-- <a 
            href="{{route('home')}}" 
            class="tw-flex tw-items-center tw-justify-center tw-w-full tw-border-r tw-h-15 tw-shrink-0"
            style="background-color: {{ $bgColor }}; color: white;"
        >
            <p class="tw-text-lg tw-font-medium tw-text-white side-bar-heading tw-text-center">
                {{ Session::get('business.name') }} 
                <span class="tw-inline-block tw-w-3 tw-h-3 tw-bg-green-400 tw-rounded-full" title="Online"></span>
            </p>
        </a> -->
        <!-- new title -->
        <a 
            href="{{route('home')}}" 
            class="tw-flex tw-items-center tw-justify-center tw-w-full tw-border-r tw-h-15 tw-shrink-0 tw-bg-gray-100"
        >
            <p 
                class="tw-text-xl tw-font-bold tw-text-white side-bar-heading tw-text-center"
                style="color: {{ $bgColor }};">
                {{ Session::get('business.name') }} 
                <span class="tw-inline-block tw-w-3 tw-h-3 tw-bg-green-400 tw-rounded-full" title="Online"></span>
            </p>
        </a>

        <!-- Sidebar Menu -->
        <div class="tw-bg-gray-100 sidebar-rounded">  <!-- this class is new added for the radius -->
            {!! Menu::render('admin-sidebar-menu', 'adminltecustom') !!}
        </div>

        <!-- /.sidebar-menu -->
        <!-- /.sidebar -->
   </div>
</aside>
