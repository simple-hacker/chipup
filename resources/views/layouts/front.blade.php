<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Poker') }}</title>

    <!-- favicon -->
    <link rel="icon" type="image/svg+xml" href="{{ asset('images/logo.svg') }}">
    <link rel="alternate icon" href="{{ asset('favicon.ico') }}">

    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.12.0-2/js/fontawesome.min.js" integrity="sha256-bP9MBDJ4xkv81w/A2cgwRonMI+eelvZwm7e8rP5JIxA=" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.13.1/js/solid.min.js" integrity="sha512-CLqslaaohrU5XmL6aleymvvBtYEHsL2VYBVM8wxeW5YNu8jpImtOP1R2XcJeyZe2Uy8lIWxeWWGdrSOXAqCv7g==" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.12.0-2/js/brands.min.js" integrity="sha256-OjapPU7ZR5ESNNSe21vgHeQCya5MZT4Y3X94x0JrP90=" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/currency-flags/2.1.2/currency-flags.min.css" integrity="sha512-7i2zQYGYkHZk2pfa3xzPcC4ZiIRs+FldBNwiNYIuJnPo/8oT6JBOhkuyacgIUe2UvJ6JEi3xH20d8AVb+xtFqg==" crossorigin="anonymous" />
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">   
</head>
<body class="bg-gray-800">
    <div id="app">
        <div class="flex flex-col xl:flex-row h-95vh w-full">
            <div class="hidden xl:block xl:w-3/5 h-1/2 bg-cover bg-right" style="background-image: url(' {{ asset('images/background.jpg') }}')"></div>
            <div class="h-full w-full xl:w-2/5 h-1/2 flex flex-col justify-center items-center">

                <div class="flex flex-col items-center bg-gray-600 rounded-t w-5/6 p-3 pb-6">
                    <img src="{{ asset('images/logo.svg') }}" width="75px" alt="Poker Logo">
                    <h1 class="text-4xl w-full text-center font-bold mb-3 text-white">{{ config('app.name', 'ChipUp') }}</h1>
                    @yield('form')
                </div>
                @guest
                    @yield('link')
                @endguest
            </div>
        </div>
        <div class="h-5vh py-6 bg-gray-600 flex flex-col justify-center items-center text-gray-100">
            <i class="fas fa-chevron-up fa-2x"></i>
        </div>
        <div class="py-6 bg-gray-500 flex flex-col justify-center items-center">
            <div class="flex justify-center">
                <img src="{{ asset('images/suits.svg') }}" alt="Suits" width="256px">
            </div>
        </div>
        <main class="flex flex-col py-8 justify-center w-full md:w-5/6 px-1 md:px-3 mx-auto text-gray-100 text-base md:text-lg">
            <div class="flex flex-col items-center self-center mb-6">
                <img src="{{ asset('images/logo.svg') }}" width="75px" alt="Poker Logo">
                <span class="mt-2 text-xl md:text-2xl text-gray-100 text-center">Manage your poker bankroll.  Anywhere.</span>
            </div>
            <div data-aos="fade-right" class="card self-start w-full md:w-2/3 shadow flex mb-5 md:mb-10 py-6 px-3 md:px-6 items-center">
                <i class="fas fa-mobile-alt fa-2x md:fa-3x mr-4 text-green-500"></i>
                <div class="w-full">
                    <h2 class="text-sm md:text-base uppercase font-extrabold tracking-wider text-white">Manage your poker bankroll</h2>
                    <p>Use anywhere on anything, not tied to a single platform such as iOS or Android.</p>
                    <p>Beautiful responsive design, looks great whether you're viewing on mobile, tablet or desktop.</p>
                </div>
            </div>
            <div data-aos="fade-left" class="card self-end w-full md:w-2/3 shadow flex mb-5 md:mb-10 py-6 px-3 md:px-6 items-center">
                <i class="fas fa-filter fa-2x md:fa-3x mr-4 text-green-500"></i>
                <div class="w-full">    
                    <h2 class="text-sm md:text-base uppercase font-extrabold tracking-wider text-white">Advanced custom filtering</h2>
                    <p>Really find out where you're excelling to maximise your profits even further.</p>
                    <p>Filter by a wide range of variables such as cash games or tournaments, stakes, locations, profit range, buy in range etc.</p>
                </div>
            </div>
            <div data-aos="fade-left" class="card self-start w-full md:w-2/3 shadow flex mb-5 md:mb-10 py-6 px-3 md:px-6 items-center">
                <i class="fas fa-chart-line fa-2x md:fa-3x mr-4 text-green-500"></i>
                <div class="w-full">
                    <h2 class="text-sm md:text-base uppercase font-extrabold tracking-wider text-white">Detailed Statistics</h2>
                    <p>Incredible dashboard with a wide range or charts and statistics to get to the nitty gritty of your play, all of which can be filtered of course.</p>
                </div>
            </div>
            <div data-aos="fade-right" class="card self-end w-full md:w-2/3 shadow flex mb-5 md:mb-10 py-6 px-3 md:px-6 items-center">
                <i class="fas fa-server fa-2x md:fa-3x mr-4 text-green-500"></i>
                <div class="w-full">  
                    <h2 class="text-sm md:text-base uppercase font-extrabold tracking-wider text-white">Backed up by our database</h2>
                    <p>Need to panic if you've changed or wiped your phone and lost your data, we've got it all for you.</p>
                </div>
            </div>
            <div data-aos="fade-left" class="card self-start w-full md:w-2/3 shadow flex mb-5 md:mb-10 py-6 px-3 md:px-6 items-center">
                <i class="fas fa-money-bill-wave fa-2x md:fa-3x mr-4 text-green-500"></i>
                <div class="w-full">
                    <h2 class="text-sm md:text-base uppercase font-extrabold tracking-wider text-white">Multiple currency support</h2>
                    <p>Playing abroad?  We support multiple currencies which are automatically converted using the latest exchange rates.</p>
                    <div class="flex mt-2">
                        <div class="currency-flag currency-flag-lg currency-flag-gbp mr-3"></div>
                        <div class="currency-flag currency-flag-lg currency-flag-usd mr-3"></div>
                        <div class="currency-flag currency-flag-lg currency-flag-eur mr-3"></div>
                        <div class="currency-flag currency-flag-lg currency-flag-cad mr-3"></div>
                        <div class="currency-flag currency-flag-lg currency-flag-aud mr-3"></div>
                        <div class="currency-flag currency-flag-lg currency-flag-pln"></div>
                    </div>
                </div>
            </div>
            <div data-aos="fade-right" class="card self-end w-full md:w-2/3 shadow flex mb-5 md:mb-10 py-6 px-3 md:px-6 items-center">
                <i class="fas fa-globe-europe fa-2x md:fa-3x mr-4 text-green-500"></i>
                <div class="w-full">  
                    <h2 class="text-sm md:text-base uppercase font-extrabold tracking-wider text-white">Multiple language support</h2>
                    <p>Coming soon!</p>
                </div>
            </div>
            <a data-aos="fade-up" href="{{ route('login') }}" class="w-full md:w-2/3 self-center mt-3 p-4 bg-gray-500 hover:bg-gray-450 rounded border-b-8 border-green-500 hover:border-green-400 shadow hover:shadow-2xl cursor-pointer text-white text-sm md:text-base font-medium uppercase text-center">
                Create a free account now
            </a>
        </main>
        <footer class="flex justify-center py-4 bg-gray-700">
            <small class="text-white">&copy; Copyright {{ date('Y') }}, <span class="text-green-500">{{ config('app.name', 'ChipUp') }}</span></small>
        </footer> 
    </div>

    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        AOS.init();
    </script>
</body>
</html>
