<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

use App\User;
use App\Restaurant;
use App\Comment as Comment;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        // 'App\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        Gate::define('is-admin', function ($user) {
            return $user->role === 'admin';
        });
        Gate::define('is-restaurant', function ($user) {
            return $user->role === 'restaurant';
        });

        // $this->authorize('is-admin') -> user is automatically given
        // you should do $this->authorize('is-admin', $restaurant);
        Gate::define('edit-restaurant', function ($user, Restaurant $restaurant) {
            return $user->role === 'admin' or $user->id == $restaurant->owner_id;
        });

        Gate::define('delete-comment', function ($user, Comment $comment) {
            return $user->role === 'admin' or $user->id == $comment->user()->get()[0]->id;
        });

        Gate::define('edit-user', function ($user, User $editUser) {
            return $user->role === 'admin' or $user->id == $editUser->id;
        });
    }
}
