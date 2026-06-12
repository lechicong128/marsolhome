<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="utf-8">
    <meta name="robots" content="noindex, nofollow">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Admin Login Portal">
    @if(str_contains(request()->getHost(), 'trycloudflare.com'))
        <meta http-equiv="Content-Security-Policy" content="upgrade-insecure-requests">
    @endif
    <link rel="shortcut icon" href="{{get_option('favicon')}}">
    <title>Login - Admin</title>
    <base href="{{ asset('') }}">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Tailwind CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        inter: ['Inter', 'sans-serif'],
                    },
                }
            }
        }
    </script>

    <!-- Font Awesome for eye icon -->
    <link href="admin/assets/css/icons.css" rel="stylesheet" type="text/css" />

    <style>
        @keyframes card-fade-in {
            from {
                opacity: 0;
                transform: translateY(24px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes shake {

            0%,
            100% {
                transform: translateX(0);
            }

            25% {
                transform: translateX(-5px);
            }

            75% {
                transform: translateX(5px);
            }
        }

        @keyframes float-blob {

            0%,
            100% {
                transform: translate(0, 0) scale(1);
            }

            33% {
                transform: translate(30px, -20px) scale(1.05);
            }

            66% {
                transform: translate(-20px, 15px) scale(0.95);
            }
        }

        @keyframes shimmer {
            0% { background-position: -200% 0; }
            100% { background-position: 200% 0; }
        }

        .animate-card {
            animation: card-fade-in 0.7s cubic-bezier(0.16, 1, 0.3, 1) both;
        }

        .animate-shake {
            animation: shake 0.4s ease;
        }

        .animate-float {
            animation: float-blob 8s ease-in-out infinite;
        }

        .animate-float-delayed {
            animation: float-blob 10s ease-in-out 2s infinite;
        }

        .animate-float-slow {
            animation: float-blob 12s ease-in-out 4s infinite;
        }

        /* Custom input styles */
        .login-input {
            border-color: rgba(100, 116, 139, 0.2) !important;
            color: #1e293b !important;
            background: rgba(255, 255, 255, 0.7) !important;
        }

        .login-input::placeholder {
            color: rgba(100, 116, 139, 0.45) !important;
        }

        .login-input:focus {
            border-color: rgba(37, 99, 235, 0.5) !important;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1) !important;
            background: rgba(255, 255, 255, 0.9) !important;
        }
    </style>
</head>

<body class="font-inter">
    <!-- Full page background -->
    <div class="min-h-screen relative overflow-hidden flex items-center justify-center p-4" style="background: linear-gradient(135deg, #eef2ff 0%, #e0e7ff 25%, #dbeafe 50%, #e0f2fe 75%, #f0f9ff 100%);">

        <!-- Soft pastel blobs -->
        <div class="absolute top-[-15%] right-[-5%] w-[700px] h-[700px] rounded-full blur-[120px] animate-float z-0" style="background: radial-gradient(circle, rgba(165, 180, 252, 0.4) 0%, rgba(147, 197, 253, 0.2) 50%, transparent 70%);"></div>
        <div class="absolute bottom-[-15%] left-[-10%] w-[600px] h-[600px] rounded-full blur-[110px] animate-float-delayed z-0" style="background: radial-gradient(circle, rgba(186, 230, 253, 0.4) 0%, rgba(224, 242, 254, 0.2) 50%, transparent 70%);"></div>
        <div class="absolute top-[20%] left-[15%] w-[500px] h-[500px] rounded-full blur-[100px] animate-float-slow z-0" style="background: radial-gradient(circle, rgba(147, 197, 253, 0.4) 0%, rgba(165, 243, 252, 0.2) 50%, transparent 70%);"></div>
        <div class="absolute bottom-[10%] right-[10%] w-[400px] h-[400px] rounded-full blur-[90px] animate-float z-0" style="background: radial-gradient(circle, rgba(191, 219, 254, 0.35) 0%, rgba(219, 234, 254, 0.15) 50%, transparent 70%);"></div>

        <!-- Decorative elements -->
        <div class="absolute inset-0 z-0 pointer-events-none overflow-hidden">
            <!-- Soft wave bands -->
            <div class="absolute w-[200%] h-[350px] top-[10%] left-[-50%] animate-float opacity-50" style="background: linear-gradient(180deg, transparent, rgba(165, 180, 252, 0.08), rgba(186, 230, 253, 0.05), transparent); transform: rotate(-6deg); border-radius: 50%;"></div>
            <div class="absolute w-[200%] h-[280px] top-[45%] left-[-30%] animate-float-delayed opacity-40" style="background: linear-gradient(180deg, transparent, rgba(147, 197, 253, 0.07), rgba(191, 219, 254, 0.04), transparent); transform: rotate(4deg); border-radius: 50%;"></div>
            <div class="absolute w-[200%] h-[220px] bottom-[5%] left-[-20%] animate-float-slow opacity-35" style="background: linear-gradient(180deg, transparent, rgba(147, 197, 253, 0.06), rgba(165, 243, 252, 0.03), transparent); transform: rotate(-3deg); border-radius: 50%;"></div>

            <!-- Subtle dot grid -->
            <div class="absolute inset-0 opacity-[0.04]"
                style="background-image: radial-gradient(circle, rgba(37, 99, 235, 0.6) 1px, transparent 1px); background-size: 45px 45px;">
            </div>

            <!-- Subtle diagonal lines -->
            <div class="absolute inset-0 opacity-[0.025]"
                style="background-image: repeating-linear-gradient(45deg, transparent, transparent 80px, rgba(37, 99, 235, 0.4) 80px, rgba(37, 99, 235, 0.4) 81px);">
            </div>
        </div>

        <!-- Card -->
        <div class="relative z-50 w-full max-w-[440px] animate-card">
            <div class="backdrop-blur-2xl border border-white/70 rounded-3xl shadow-[0_20px_60px_-15px_rgba(37,99,235,0.12),0_8px_25px_-5px_rgba(0,0,0,0.06)] overflow-hidden" style="background: linear-gradient(145deg, rgba(255, 255, 255, 0.88), rgba(255, 255, 255, 0.95));">

                <!-- Top accent line -->
                <div class="h-[3px] w-full" style="background: linear-gradient(to right, transparent, #60a5fa, #2563eb, #1d4ed8, #0ea5e9, transparent);"></div>

                <!-- Card body -->
                <div class="px-10 py-12">
                    <form action="admin/login" method="post">

                        <!-- Logo / Brand -->
                        <div class="flex flex-col items-center mb-10">
                            <span class="img-logo-foso" style="margin-bottom: 12px;">
                                <img src="admin/assets/images/logo_login.png" width="200"></span>
                            <h1 class="text-2xl font-semibold tracking-widest" style="color: #1e3a8a;">AI GROUP ADMIN</h1>
                            <p class="text-[11px] mt-1.5 tracking-[0.2em] uppercase" style="color: rgba(37, 99, 235, 0.7);">Trang quản trị bất động sản</p>
                        </div>

                        <?php $cookieLogin = !empty($_COOKIE['remember_login']) ? json_decode($_COOKIE['remember_login']) : NULL ?>
                        {{ csrf_field() }}

                        <!-- Error message -->
                        @if(Session::has('message'))
                        <div class="mb-5 p-4 rounded-xl bg-red-50 border border-red-200 flex items-start gap-3 animate-shake">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-red-500 flex-shrink-0 mt-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="10" />
                                <line x1="12" y1="8" x2="12" y2="12" />
                                <line x1="12" y1="16" x2="12.01" y2="16" />
                            </svg>
                            <p class="text-sm text-red-600">{{ Session::get('message') }}</p>
                        </div>
                        @endif

                        <!-- Email -->
                        <div class="mb-5 group">
                            <label class="block text-[11px] font-medium tracking-widest uppercase mb-2" style="color: #475569;">Email</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-4 flex items-center pointer-events-none">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 transition-colors duration-200" style="color: rgba(37, 99, 235, 0.5);" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <rect width="20" height="16" x="2" y="4" rx="2" />
                                        <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7" />
                                    </svg>
                                </div>
                                <input
                                    type="text"
                                    id="input_login_email"
                                    name="email"
                                    autocomplete="off"
                                    placeholder="admin@example.com"
                                    value="{{!empty($cookieLogin) ? $cookieLogin->email : ''}}"
                                    required
                                    class="login-input w-full border rounded-xl pl-11 pr-4 py-3.5 text-sm outline-none transition-all duration-200" />
                            </div>
                            @if($errors->has('email'))
                            <p class="text-red-500 text-xs mt-1.5 pl-1">{{ $errors->first('email') }}</p>
                            @endif
                        </div>

                        <!-- Password -->
                        <div class="mb-5 group">
                            <label class="block text-[11px] font-medium tracking-widest uppercase mb-2" style="color: #475569;">Mật khẩu</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-4 flex items-center pointer-events-none">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 transition-colors duration-200" style="color: rgba(37, 99, 235, 0.5);" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <rect width="18" height="11" x="3" y="11" rx="2" ry="2" />
                                        <path d="M7 11V7a5 5 0 0 1 10 0v4" />
                                    </svg>
                                </div>
                                <input
                                    type="password"
                                    name="password"
                                    autocomplete="off"
                                    placeholder="••••••••"
                                    value="{{!empty($cookieLogin) ? decrypt($cookieLogin->password) : ''}}"
                                    required
                                    class="login-input w-full border rounded-xl pl-11 pr-12 py-3.5 text-sm outline-none transition-all duration-200" />
                                <button type="button" onclick="showPassword('password'); return false;"
                                    class="absolute inset-y-0 right-4 flex items-center transition-colors duration-200" style="color: rgba(37, 99, 235, 0.5);">
                                    <svg id="icon-eye-off" xmlns="http://www.w3.org/2000/svg" class="w-[18px] h-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M9.88 9.88a3 3 0 1 0 4.24 4.24" />
                                        <path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68" />
                                        <path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61" />
                                        <line x1="2" y1="2" x2="22" y2="22" />
                                    </svg>
                                    <svg id="icon-eye-on" xmlns="http://www.w3.org/2000/svg" class="w-[18px] h-[18px] hidden" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z" />
                                        <circle cx="12" cy="12" r="3" />
                                    </svg>
                                </button>
                            </div>
                            @if($errors->has('password'))
                            <p class="text-red-500 text-xs mt-1.5 pl-1">{{ $errors->first('password') }}</p>
                            @endif
                        </div>

                        <!-- Remember me -->
                        <div class="flex items-center gap-2 mb-6">
                            <input id="checkbox-signup" type="checkbox" name="remember" value="1" {{!empty($cookieLogin) ? 'checked' : ''}}
                                class="w-4 h-4 rounded focus:ring-offset-0 cursor-pointer" style="border-color: rgba(37,99,235,0.3); accent-color: #2563eb;" />
                            <label for="checkbox-signup" class="text-sm cursor-pointer select-none" style="color: rgba(71, 85, 105, 0.8);">
                                Ghi nhớ đăng nhập
                            </label>
                        </div>

                        <!-- Submit button -->
                        <button type="submit"
                            class="relative w-full py-3.5 rounded-xl font-medium text-sm tracking-wide overflow-hidden group/btn cursor-pointer"
                            style="background: linear-gradient(135deg, #2563eb, #1d4ed8, #1e40af);">
                            <div class="absolute inset-0 opacity-0 group-hover/btn:opacity-100 transition-opacity duration-300" style="background-color: rgba(255,255,255,0.15);"></div>
                            <span class="relative text-white flex items-center justify-center gap-2 font-semibold">
                                Đăng nhập
                            </span>
                        </button>

                    </form>
                </div>

                <!-- Bottom accent line -->
                <div class="h-[3px] w-full" style="background: linear-gradient(to right, transparent, #60a5fa, #2563eb, #1d4ed8, #0ea5e9, transparent);"></div>

                <!-- Footer -->
                <div class="px-10 py-4 text-center" style="background-color: rgba(37, 99, 235, 0.03);">
                    <p class="text-[11px] tracking-[0.2em] uppercase font-normal" style="color: rgba(100, 116, 139, 0.5);">
                        Ai-Group Admin Portal
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- jQuery -->
    <script src="admin/assets/js/jquery.min.js"></script>
    <script>
        function showPassword(name) {
            var target = $('input[name="' + name + '"]');
            var iconOff = $('#icon-eye-off');
            var iconOn = $('#icon-eye-on');

            if ($(target).attr('type') === 'password' && $(target).val() !== '') {
                $(target).attr('type', 'text');
                iconOff.addClass('hidden');
                iconOn.removeClass('hidden');
            } else {
                $(target).attr('type', 'password');
                iconOn.addClass('hidden');
                iconOff.removeClass('hidden');
            }
        }
    </script>
</body>

</html>