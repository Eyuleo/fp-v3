<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            {{ __('Profile Information') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            {{ __("Update your account's profile information and email address.") }}
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="mt-6 space-y-6" enctype="multipart/form-data">
        @csrf
        @method('patch')

        <!-- Avatar Upload -->
        <div>
            <x-input-label for="avatar" :value="__('Profile Picture')" />
            <div class="mt-2 flex items-center gap-4">
                <div class="flex-shrink-0">
                    <img src="{{ $user->avatar_url }}" alt="{{ $user->full_name }}" class="h-20 w-20 rounded-full object-cover">
                </div>
                <div class="flex-1">
                    <input type="file" id="avatar" name="avatar" accept="image/jpeg,image/jpg,image/png" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                    <p class="mt-1 text-xs text-gray-500">JPG, JPEG or PNG. Max 10MB.</p>
                    <x-input-error class="mt-2" :messages="$errors->get('avatar')" />
                </div>
            </div>
        </div>

        <div>
            <x-input-label for="first_name" :value="__('First Name')" />
            <x-text-input id="first_name" name="first_name" type="text" class="mt-1 block w-full" :value="old('first_name', $user->first_name)" required autofocus autocomplete="given-name" />
            <x-input-error class="mt-2" :messages="$errors->get('first_name')" />
        </div>

        <div>
            <x-input-label for="last_name" :value="__('Last Name')" />
            <x-text-input id="last_name" name="last_name" type="text" class="mt-1 block w-full" :value="old('last_name', $user->last_name)" required autocomplete="family-name" />
            <x-input-error class="mt-2" :messages="$errors->get('last_name')" />
        </div>

        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $user->email)" required autocomplete="username" />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div>
                    <p class="text-sm mt-2 text-gray-800">
                        {{ __('Your email address is unverified.') }}

                        <button form="send-verification" class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            {{ __('Click here to re-send the verification email.') }}
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 font-medium text-sm text-green-600">
                            {{ __('A new verification link has been sent to your email address.') }}
                        </p>
                    @endif
                </div>
            @endif
        </div>

        @if($user->isStudent())
            <div>
                <x-input-label for="university" :value="__('University')" />
                <x-text-input id="university" name="university" type="text" class="mt-1 block w-full" :value="old('university', $user->university)" autocomplete="organization" />
                <x-input-error class="mt-2" :messages="$errors->get('university')" />
            </div>

            <div>
                <x-input-label for="bio" :value="__('Bio')" />
                <textarea id="bio" name="bio" rows="4" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" maxlength="1000">{{ old('bio', $user->bio) }}</textarea>
                <p class="mt-1 text-xs text-gray-500">Tell clients about your skills and experience. Max 1000 characters.</p>
                <x-input-error class="mt-2" :messages="$errors->get('bio')" />
            </div>

            <div>
                <x-input-label for="portfolio" :value="__('Portfolio Files')" />

                @if($user->portfolio_paths && count($user->portfolio_paths) > 0)
                    <div class="mt-2 mb-3 grid grid-cols-2 md:grid-cols-4 gap-2">
                        @foreach($user->portfolio_paths as $index => $portfolioPath)
                            @php
                                $extension = pathinfo($portfolioPath, PATHINFO_EXTENSION);
                                $isImage = in_array(strtolower($extension), ['jpg', 'jpeg', 'png']);
                            @endphp
                            <div class="relative border border-gray-200 rounded p-2">
                                @if($isImage)
                                    <img src="{{ asset('storage/' . $portfolioPath) }}" alt="Portfolio" class="w-full h-20 object-cover rounded">
                                @else
                                    <div class="flex flex-col items-center justify-center h-20 bg-gray-50 rounded">
                                        <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                        </svg>
                                        <span class="text-xs text-gray-600 uppercase">{{ $extension }}</span>
                                    </div>
                                @endif
                                <button type="button" onclick="deletePortfolioItem({{ $index }})" class="absolute top-1 right-1 bg-red-500 text-white rounded-full p-1 hover:bg-red-600">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </div>
                        @endforeach
                    </div>
                @endif

                <input type="file" id="portfolio" name="portfolio[]" multiple accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                <p class="mt-1 text-xs text-gray-500">Upload additional files (up to 5 total). PDF, DOC, DOCX, JPG, JPEG, or PNG. Max 10MB per file.</p>
                <x-input-error class="mt-2" :messages="$errors->get('portfolio')" />
            </div>

            <script>
                function deletePortfolioItem(index) {
                    if (confirm('Are you sure you want to delete this portfolio item?')) {
                        // Create a form to submit the deletion
                        const form = document.createElement('form');
                        form.method = 'POST';
                        form.action = '{{ route("profile.portfolio.delete") }}';

                        const csrfToken = document.createElement('input');
                        csrfToken.type = 'hidden';
                        csrfToken.name = '_token';
                        csrfToken.value = '{{ csrf_token() }}';

                        const methodField = document.createElement('input');
                        methodField.type = 'hidden';
                        methodField.name = '_method';
                        methodField.value = 'DELETE';

                        const indexField = document.createElement('input');
                        indexField.type = 'hidden';
                        indexField.name = 'index';
                        indexField.value = index;

                        form.appendChild(csrfToken);
                        form.appendChild(methodField);
                        form.appendChild(indexField);
                        document.body.appendChild(form);
                        form.submit();
                    }
                }
            </script>
        @else
            <div>
                <x-input-label for="bio" :value="__('Bio')" />
                <textarea id="bio" name="bio" rows="3" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" maxlength="1000">{{ old('bio', $user->bio) }}</textarea>
                <p class="mt-1 text-xs text-gray-500">Optional. Max 1000 characters.</p>
                <x-input-error class="mt-2" :messages="$errors->get('bio')" />
            </div>
        @endif

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Save') }}</x-primary-button>

            @if (session('status') === 'profile-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-gray-600"
                >{{ __('Saved.') }}</p>
            @endif
        </div>
    </form>
</section>
