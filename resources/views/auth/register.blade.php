<x-layouts.auth title="Create account">
    <div>
        <p class="text-sm font-bold uppercase tracking-[0.18em] text-orange-600">Join the workspace</p>
        <h2 class="mt-3 text-3xl font-extrabold tracking-tight text-slate-950">Create your account</h2>
        <p class="mt-3 text-sm leading-6 text-slate-500">New accounts start as team members. An admin can adjust access later.</p>
    </div>

    <form method="POST" action="{{ route('register.store') }}" class="mt-8 space-y-5">
        @csrf
        <div>
            <label for="name" class="text-sm font-bold text-slate-700">Full name</label>
            <input id="name" name="name" value="{{ old('name') }}" required autofocus autocomplete="name"
                   placeholder="Your name"
                   class="mt-2 w-full rounded-xl border-slate-300 bg-white px-4 py-3 text-sm shadow-sm placeholder:text-slate-400 focus:border-orange-500 focus:ring-orange-500">
            @error('name') <p class="mt-2 text-xs font-semibold text-rose-600">{{ $message }}</p> @enderror
        </div>

        <div class="grid gap-5 sm:grid-cols-2">
            <div>
                <label for="email" class="text-sm font-bold text-slate-700">Email address</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" required autocomplete="email"
                       placeholder="you@company.com"
                       class="mt-2 w-full rounded-xl border-slate-300 bg-white px-4 py-3 text-sm shadow-sm placeholder:text-slate-400 focus:border-orange-500 focus:ring-orange-500">
                @error('email') <p class="mt-2 text-xs font-semibold text-rose-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="phone" class="text-sm font-bold text-slate-700">Phone <span class="font-medium text-slate-400">(optional)</span></label>
                <input id="phone" name="phone" value="{{ old('phone') }}" autocomplete="tel"
                       placeholder="+49 123 456789"
                       class="mt-2 w-full rounded-xl border-slate-300 bg-white px-4 py-3 text-sm shadow-sm placeholder:text-slate-400 focus:border-orange-500 focus:ring-orange-500">
                @error('phone') <p class="mt-2 text-xs font-semibold text-rose-600">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="grid gap-5 sm:grid-cols-2">
            <div>
                <label for="password" class="text-sm font-bold text-slate-700">Password</label>
                <input id="password" name="password" type="password" required autocomplete="new-password"
                       placeholder="8+ characters"
                       class="mt-2 w-full rounded-xl border-slate-300 bg-white px-4 py-3 text-sm shadow-sm placeholder:text-slate-400 focus:border-orange-500 focus:ring-orange-500">
                @error('password') <p class="mt-2 text-xs font-semibold text-rose-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="password_confirmation" class="text-sm font-bold text-slate-700">Confirm password</label>
                <input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password"
                       placeholder="Repeat password"
                       class="mt-2 w-full rounded-xl border-slate-300 bg-white px-4 py-3 text-sm shadow-sm placeholder:text-slate-400 focus:border-orange-500 focus:ring-orange-500">
            </div>
        </div>

        <button class="flex w-full items-center justify-center rounded-xl bg-slate-950 px-4 py-3.5 text-sm font-extrabold text-white shadow-lg shadow-slate-950/15 transition hover:bg-slate-800 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-orange-500">
            Create account
        </button>
    </form>

    <p class="mt-7 text-center text-sm text-slate-500">
        Already have an account?
        <a href="{{ route('login') }}" class="font-bold text-orange-600 hover:text-orange-700">Sign in</a>
    </p>
</x-layouts.auth>
