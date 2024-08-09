@extends('layout.master')
@section('content')
    <div>
        <div class="container">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">{{ __('Edit User') }}</div>
                    <div class="card-body">
                        <form method="POST" action=" {{ route('user.update', $user->id) }} ">
                        @csrf
                            <div>
                                <label for="name" class="col-md-4 col-form-label text-md-right">{{ __('Nama') }} </label>
                                <div class="col-md-6">
                                    <input type="text" class="forom-control @error('name') is-invalid @enderror" name="name"
                                    value="{{ $user->name }}" required autocomplete="name" autofocus>
                                    @error('name')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ message }} </strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            <div>
                                <label for="name" class="col-md-4 col-form-label text-md-right">{{ __('Alamat Email') }} </label>
                                <div class="col-md-6">
                                    <input type="text" class="forom-control @error('email') is-invalid @enderror" name="email"
                                    value="{{ $user->email }}" required autocomplete="email">
                                    @error('email')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ message }} </strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            <div>
                                <label for="name" class="col-md-4 col-form-label text-md-right">{{ __('Password') }} </label>
                                <div class="col-md-6">
                                    <input type="password" class="forom-control @error('password') is-invalid @enderror" name="password"
                                    value="{{ $user->password }}" required autocomplete="new-passsword">
                                    @error('password')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ message }} </strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            <div>
                                <label for="name" class="col-md-4 col-form-label text-md-right">{{ __('Confirm Password') }} </label>
                                <div class="col-md-6">
                                    <input type="password" class="forom-control @error('password') is-invalid @enderror" 
                                    name="password_confirmation" value="{{ $user->password }}" required autocomplete="new-passsword">
                                    @error('password')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ message }} </strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            <div>
                                <label for="name" class="col-md-4 col-form-label text-md-right" name="level"></label>
                                <div class="col-md-6">
                                    <select name="level" id="level" value="{{ $user->level }}" class="form-control">
                                        <option value="peminjam" <?php if($user->level == "peminjam") echo 'selected="selected"'; ?> >Peminjam</option>
                                        <option value="admin" <?php if($user->level == "admin") echo 'selected="selected"'; ?>>Admin</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col-md-6 offset-md-0">
                                    <button type="submit" class="btn btn-primary">
                                        {{ __('Simpan') }}
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection            