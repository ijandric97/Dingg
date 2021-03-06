<?php

namespace App\Http\Controllers;

use App\Order;
use App\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\View\View;

class OrderUserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param User $user
     * @return View
     * @throws AuthorizationException
     */
    public function index(User $user)
    {
        $this->authorize('edit-user', $user);

        $colors = [
            '0' => 'text-danger',
            '1' => 'text-success',
            '2' => 'text-dark',
        ];

        return view('pages.user.order.index', [
            'user' => $user,
            'orders' => $user->orders()
                ->orderBy('status', 'desc')
                ->orderBy('reservation_time', 'asc')
                ->paginate(50),
            'colors' => $colors,
        ]);
    }


    /**
     * Show the form for editing the specified resource.
     *
     * @param User $user
     * @param Order $order
     * @return View
     * @throws AuthorizationException
     */
    public function edit(User $user, Order $order)
    {
        $this->authorize('edit-user', $user);

        $colors = [
            '0' => 'bg-danger',
            '1' => 'bg-success',
            '2' => 'bg-dark',
        ];

        return view('pages.user.order.edit', [
            'user' => $user,
            'order' => $order,
            'table' => $order->table,
            'colors' => $colors,
            'products' => $order->products()->get(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param User $user
     * @param Order $order
     * @return Application|RedirectResponse|Redirector
     * @throws AuthorizationException
     */
    public function update(Request $request, User $user, Order $order)
    {
        $this->authorize('edit-user', $user);

        $order->status = 0;
        $order->save();

        return redirect(route('user.order.edit', [$user, $order]))->with('success', 'Order canceled');
    }
}
